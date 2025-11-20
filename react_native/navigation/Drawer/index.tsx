import React, { useEffect } from 'react'
import { Linking } from 'react-native'

import { createDrawerNavigator, DrawerContentComponentProps } from '@react-navigation/drawer'
import { AppLifecycle } from 'react-native-applifecycle'

import colors from 'constants/colors'
import { useAppSelector } from 'hooks'
import ArchiveStack from 'navigation/Drawer/ArchiveStack'
import CameraStack from 'navigation/Drawer/CameraStack'
import HomeStack from 'navigation/Drawer/HomeStack'
import ProfileStack from 'navigation/Drawer/ProfileStack'
import SettingsStack from 'navigation/Drawer/SettingsStack'
import SubscriptionsStack from 'navigation/Drawer/SubscriptionsStack'
import Tabs from 'navigation/Drawer/Tabs'
import { AppStates } from 'types'

import { navigationRef } from '../navigationRef'
import { drawerStyles } from '../styles'
import { DrawerContent } from './components/DrawerContent'

const DrawerNavigator = createDrawerNavigator()

const INITIAL_ROUTE_NAME: string = 'DrawerCamera'
const INITIAL_SCREEN_NAME: string = 'Camera'
const IS_RESET_TO_INITIAL_ROUTE_NAME: boolean = true
const EXCLUDED_SCREENS = [INITIAL_SCREEN_NAME, 'Subscriptions']

const DrawerHomeComponent = () => <Tabs component={HomeStack} />
const DrawerProfileComponent = () => <Tabs component={ProfileStack} />

const Drawer: React.FC = () => {
  const user = useAppSelector(state => state.user.user)

  useEffect(() => {
    let isDeepLinking: boolean = false

    const linkingListener = Linking.addEventListener('url', () => {
      isDeepLinking = true
    })

    const appLifecycleListener = AppLifecycle.addEventListener('change', state => {
      if (IS_RESET_TO_INITIAL_ROUTE_NAME && !isDeepLinking && state === AppStates.Active) {
        const currentScreenName = navigationRef.getCurrentRoute()?.name
        const isShouldReset = currentScreenName && !EXCLUDED_SCREENS.includes(currentScreenName)

        if (isShouldReset && navigationRef.isReady()) {
          navigationRef.current?.navigate('DrawerCamera')
        }
      }

      if (IS_RESET_TO_INITIAL_ROUTE_NAME && isDeepLinking && state === AppStates.Background) {
        isDeepLinking = false
      }
    })

    return () => {
      linkingListener.remove()
      appLifecycleListener.remove()
    }
  }, [])

  return (
    <DrawerNavigator.Navigator
      initialRouteName={INITIAL_ROUTE_NAME}
      drawerContent={(props: DrawerContentComponentProps) => <DrawerContent {...props} />}
      screenOptions={{
        headerShown: false,
        drawerActiveTintColor: colors.White,
        drawerInactiveTintColor: colors.Silver,
        drawerActiveBackgroundColor: colors.Transparent,
        drawerItemStyle: drawerStyles.commonStyles.drawerItemStyle,
        drawerLabelStyle: drawerStyles.commonStyles.drawerLabelStyle,
      }}
    >
      <DrawerNavigator.Screen
        name="DrawerHome"
        options={{ drawerLabel: 'HOME' }}
        component={DrawerHomeComponent}
      />
      <DrawerNavigator.Screen
        name="DrawerCamera"
        component={CameraStack}
        options={{ drawerLabel: 'CAMERA' }}
      />
      <DrawerNavigator.Screen
        name="DrawerProfile"
        component={DrawerProfileComponent}
        options={{ drawerLabel: 'PROFILE' }}
      />
    </DrawerNavigator.Navigator>
  )
}

export default Drawer
