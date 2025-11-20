import { DEV, PROD } from '@env'
import AsyncStorage from '@react-native-async-storage/async-storage'
import axios, { AxiosError } from 'axios'

import { constants, endpoints } from 'constants/index'
import {
  ErrorCodes,
  InterceptorTypes,
  ResStatuses,
  ToastMessages,
  ToastTitles,
  ToastTypes,
} from 'types/enums'

import check401 from './check401'
import { crashlyticsHandlesApiError } from './firebaseHelpers'
import isNetworkError from './isNetworkError'
import isTimeoutError from './isTimeoutError'
import logAPI from './logApi'
import { isInternetConnectionWithRetry } from './netinfoHelpers'
import showToastMessage from './showToastMessage'
import { store } from '../store'

const baseURL = constants.isDev ? DEV : PROD

const restClient = axios.create({
  baseURL,
  retryCount: 0,
  isRetryRequest: true,
  retryDelay: constants.retryDelay,
  timeout: constants.responseTimeout,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
  paramsSerializer: {
    indexes: null,
  },
})

const contentTypes = {
  form: { 'Content-Type': 'multipart/form-data' },
}

type RetryConfigData = {
  RetryUrls: string[]
}

const RetryConfig = {
  RetryUrls: [
    endpoints.logIn,
    endpoints.signUp,
    endpoints.forgot,
    endpoints.getUser,
    endpoints.updateUser,
    endpoints.addSubUser,
  ],
} as RetryConfigData

type ErrorMessageConfig = {
  urlPattern: string
  getMessage: (error: AxiosError) => string
}

const ErrorConfig = {
  NoToastCodes: [ErrorCodes.Canceled],
  NoToastURLs: [
    endpoints.uploadImageV4,
    endpoints.uploadVideoV4,
    endpoints.getUserSubscriptionStatus,
  ],
  CustomMessages: [
    {
      urlPattern: endpoints.getLocationByCoordinates,
      getMessage: err => {
        if (isTimeoutError(err)) return ToastMessages.TimeoutError
        if (isNetworkError(err)) return ToastMessages.SomethingWentWrong

        return ToastMessages.TryingToLoadPhoto
      },
    },
  ] as ErrorMessageConfig[],
}

let clearAllStoresFunc: () => void

const isAxiosError = (error: AxiosError | Error | unknown) => axios.isAxiosError(error)

const isShouldRetryRequest = (config: any): boolean => {
  const isRetryAvailable = config.retryCount < constants.maxRetryRequestAttempts
  const isRetryUrl = RetryConfig.RetryUrls.some(url => config.url?.includes(url))

  return config.isRetryRequest && isRetryAvailable && isRetryUrl
}

type ErrorResponseData =
  | string
  | number
  | { [key: string]: ErrorResponseData }
  | ErrorResponseData[]

const recursion = (item: ErrorResponseData): string => {
  if (typeof item === 'string' || typeof item === 'number') {
    return item.toString()
  }

  if (Array.isArray(item)) {
    return item.map(i => recursion(i)).join('; ')
  }

  if (typeof item === 'object' && item !== null) {
    return Object.values(item)
      .map(value => recursion(value))
      .join('; ')
  }

  return ''
}

const parseResponseErrors = (error: ErrorResponseData): string => {
  try {
    if (typeof error === 'string') return error
    if (typeof error === 'number') return error.toString()

    const result = Array.isArray(error)
      ? error.map(item => recursion(item))
      : Object.values(error).map(item => recursion(item))

    const formattedResult = result.join().replace(/; /g, '\n')

    return formattedResult || ToastMessages.UnknownError
  } catch {
    return ToastMessages.UnknownError
  }
}

const getErrorMessage = (error: AxiosError | Error | unknown) => {
  if (!error || !axios.isAxiosError(error)) return ToastMessages.UnknownError

  const customConfig = ErrorConfig.CustomMessages.find(config =>
    error.config?.url?.includes(config.urlPattern),
  )

  if (customConfig) return customConfig.getMessage(error)
  if (isTimeoutError(error)) return ToastMessages.TimeoutError
  if (isNetworkError(error)) return ToastMessages.SomethingWentWrong
  if (error.response?.data) return parseResponseErrors(error.response?.data as ErrorResponseData)

  return error.message || ToastMessages.UnknownError
}

const setAuthorizationToken = (accessToken: string) => {
  restClient.defaults.headers.Authorization = `${accessToken}`
}

const getAuthorizationFromHeaders = () => restClient.defaults.headers.Authorization

const setClearAllStoresFunc = (clearStoresFunc: () => void) => {
  clearAllStoresFunc = clearStoresFunc
}

restClient.interceptors.request.use(
  async config => {
    const controller = new AbortController()
    const isOnline = await isInternetConnectionWithRetry()
    const _config = { ...config, signal: controller.signal }

    if (constants.isDev) {
      _config.metadata = { startTime: new Date() }
    }

    if (!_config.headers.Authorization) {
      const accessToken = store.getState().auth.accessToken

      if (accessToken) {
        _config.headers.Authorization = `${accessToken}`
        setAuthorizationToken(accessToken)

        await AsyncStorage.setItem('accessToken', accessToken)
      }
    }

    if (!isOnline) {
      controller.abort()
    }

    return _config
  },
  error => Promise.reject(error),
)

restClient.interceptors.response.use(
  res => {
    if (constants.isDev) {
      logAPI(InterceptorTypes.Response, ResStatuses.Fulfilled, res)
    }

    if (res.data?.error) {
      showToastMessage({
        type: ToastTypes.Error,
        title: ToastTitles.Attention,
        description: res.data?.error,
      })

      throw new Error(res.data?.error)
    }

    return res
  },
  async err => {
    if (constants.isDev) {
      logAPI(InterceptorTypes.Response, ResStatuses.Rejected, err)
    }

    if (err.config && isNetworkError(err) && isShouldRetryRequest(err.config)) {
      await new Promise(resolve => setTimeout(resolve, err.config?.retryDelay || 1000))

      return restClient.request({ ...err.config, retryCount: (err.config.retryCount || 0) + 1 })
    }

    const isExcludedCode = ErrorConfig.NoToastCodes.includes(err.code)
    const isExcludedUrl = ErrorConfig.NoToastURLs.some(url => err.config?.url?.includes(url))
    const message = getErrorMessage(err)

    if (!constants.isDev) {
      crashlyticsHandlesApiError(err, message)
    }

    if (!isExcludedUrl && !isExcludedCode) {
      showToastMessage({
        type: ToastTypes.Error,
        title: ToastTitles.Attention,
        description: message,
      })
    }

    if (isAxiosError(err) && err.response) {
      check401(err.response.status, clearAllStoresFunc)
    }

    return Promise.reject(err)
  },
)

export {
  restClient,
  contentTypes,
  isAxiosError,
  parseResponseErrors,
  setAuthorizationToken,
  setClearAllStoresFunc,
  getAuthorizationFromHeaders,
}
