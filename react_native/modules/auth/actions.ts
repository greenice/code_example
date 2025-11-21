import AsyncStorage from '@react-native-async-storage/async-storage'
import { createAction, createAsyncThunk } from '@reduxjs/toolkit'

import endpoints from 'constants/endpoints'
import { AuthorizationData, ForgotPasswordResponse, ToastTitles, ToastTypes } from 'types'
import { api, contentTypes, setAuthorizationToken, showToastMessage } from 'utils'

import * as types from './types'
import { getUser } from '../user/actions'

export const setToken = createAction<string>(types.SET_TOKEN)
export const clearAuthState = createAction(types.CLEAR_AUTH_STATE)

export const logIn = createAsyncThunk<AuthorizationData, FormData>(
  'auth/logIn',
  async (data, { dispatch }) => {
    const response = (
      await api.post<AuthorizationData>(endpoints.logIn, data, {
        headers: contentTypes.form,
      })
    ).data

    showToastMessage({
      type: ToastTypes.Success,
      title: ToastTitles.ActionSuccessful,
      description: response.success,
    })

    if (response.token) {
      setAuthorizationToken(response.token)

      await AsyncStorage.setItem('accessToken', response.token)
      await dispatch(getUser({ isWithSetupCrashlytics: true }))
    }

    return response
  },
)

export const signUp = createAsyncThunk<undefined, FormData>(
  'auth/signUp',
  async (data, { dispatch }) => {
    console.log(333, endpoints.signUp, data)
    const result = (
      await api.post<AuthorizationData>(endpoints.signUp, data, {
        headers: contentTypes.form,
      })
    ).data

    console.log(444, result, 'result')

    showToastMessage({
      type: ToastTypes.Success,
      title: ToastTitles.Congratulations,
      description: result.success,
    })

    if (result.token) {
      setAuthorizationToken(result.token)

      await dispatch(getUser({ isWithSetupCrashlytics: true }))
    }

    return undefined
  },
)

export const forgotPassword = createAsyncThunk<undefined, FormData>(
  'auth/forgotPassword',
  async data => {
    const result = (
      await api.post<ForgotPasswordResponse>(endpoints.forgot, data, { headers: contentTypes.form })
    ).data

    showToastMessage({
      type: ToastTypes.Success,
      title: ToastTitles.ActionSuccessful,
      description: result.success,
    })

    return undefined
  },
)
