import { Alert, Linking, Platform } from 'react-native'

import { checkVersion } from 'react-native-check-version'
import { getBuildNumber, getVersion } from 'react-native-device-info'

import { constants, endpoints } from 'constants/index'
import { MinimumMobileAppVersion } from 'types'
import { api } from 'utils'

enum UpdateTypes {
  Major = 'major',
  Minor = 'minor',
  Patch = 'patch',
}

type Messages = {
  [key in UpdateTypes]: string
}

const updateMessages: Messages = {
  [UpdateTypes.Major]: 'Critical update available',
  [UpdateTypes.Minor]: 'New update available',
  [UpdateTypes.Patch]: 'New update available',
}

type AlertButton = {
  text: string
  onPress?: () => void
  style?: 'cancel' | 'default' | 'destructive'
}

const Buttons: AlertButton[] = [
  {
    text: 'Cancel',
    style: 'cancel',
  },
  {
    text: 'Updated Now',
    onPress: () => Linking.openURL(constants.isIos ? endpoints.appStore : endpoints.androidMarket),
  },
]

const versionComparison = (current: string | null | undefined, actual: string) => {
  if (!current || !actual) {
    return false
  }

  const format = (arr: string) => arr.split('.').map(item => Number(item))

  const currentFormatted = format(current)
  const actualFormatted = format(actual)
  for (let i = 0; i < currentFormatted.length; i++) {
    if (actualFormatted[i] === currentFormatted[i]) {
      continue
    }

    return actualFormatted[i] > currentFormatted[i]
  }

  return false
}

const useCheckUpdateApp = () => {
  // const navigation = useNavigation<StackNavigationProp<ForceUpdateParamsList>>()

  return async () => {
    try {
      if (constants.IS_BUILD_ONLY_FOR_TESTFLIGHT) {
        const currentBuild = Platform.select({
          ios: getBuildNumber(),
          android: getBuildNumber(),
        })

        const version = (
          await api.get<MinimumMobileAppVersion>(endpoints.getMinimumMobileAppVersion)
        ).data

        const build = constants.isIos ? version.build_ios : version.build_android
        const isOldBuild = versionComparison(currentBuild, String(build))

        if (isOldBuild) {
          Alert.alert('Update Window', 'Please install a new build from TestFlight!')
        }

        return isOldBuild
      }

      const currentVersion = Platform.select({
        ios: getVersion(),
        android: getVersion(),
      })

      const actualVersion = await checkVersion()

      if (actualVersion.needsUpdate) {
        const version = (
          await api.get<MinimumMobileAppVersion>(endpoints.getMinimumMobileAppVersion)
        ).data

        const isForceUpdate = versionComparison(currentVersion, String(version.app_version))

        if (!isForceUpdate) {
          Alert.alert('Update Window', updateMessages[actualVersion.updateType!], Buttons)
        }

        return isForceUpdate
      }

      return false
    } catch {
      return false
    }
  }
}

export default useCheckUpdateApp
