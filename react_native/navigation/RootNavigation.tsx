import React from 'react'
import { StatusBar } from 'react-native'

import { DefaultTheme, NavigationContainer } from '@react-navigation/native'

import { BackButtonService } from 'components'
import { colors } from 'constants/index'
import { useAppSelector } from 'hooks'
import Auth from 'navigation/Auth'
import Drawer from 'navigation/Drawer'

import { navigationRef } from './navigationRef'

const linking = {
  prefixes: ['myapp://', 'com.app.test://'],
  config: {
    screens: {
      Onboarding: 'onboarding',
      SignIn: 'signIn',
      SignUp: 'signUp',
      DrawerHome: {
        screens: {
          HomeTab: {
            screens: {
              Home: 'home',
            },
          },
        },
      },
      DrawerCamera: {
        screens: {
          Camera: 'camera',
        },
      },
      DrawerProfile: {
        screens: {
          HomeTab: {
            screens: {
              Profile: 'profile',
            },
          },
        },
      },
      DrawerArchive: {
        screens: {
          Archive: 'archive',
        },
      },
      DrawerSubscriptions: {
        screens: {
          Subscriptions: 'subscriptions',
        },
      },
      DrawerSettings: {
        screens: {
          Settings: 'settings',
        },
      },
    },
  },
}

const RootNavigation: React.FC = () => {
  const accessToken = useAppSelector(state => state.auth.accessToken)

  return (
    <NavigationContainer ref={navigationRef} linking={linking} theme={DefaultTheme}>
      <StatusBar animated backgroundColor={colors.Black} barStyle="light-content" />

      <BackButtonService />
      {accessToken ? <Drawer /> : <Auth />}
    </NavigationContainer>
  )
}

export default RootNavigation
