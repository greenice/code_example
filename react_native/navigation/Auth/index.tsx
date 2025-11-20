import React from 'react'
import { ImageBackground } from 'react-native'

import { createStackNavigator } from '@react-navigation/stack'

import { pngImages } from 'assets/images'
import { colors } from 'constants/index'
import { stackStyles } from 'navigation/styles'
import {
  AddingSubUsers,
  AuthSubscriptions,
  ForgotPassword,
  License,
  Onboarding,
  PrivacyPolicy,
  SignIn,
  SignUp,
} from 'screens'
import { AuthStackParamList } from 'types/navigation'

import { LeftHeaderButton } from '../components/LeftHeaderButton'

const AuthNavigator = createStackNavigator<AuthStackParamList>()

const Auth: React.FC = () => (
  <AuthNavigator.Navigator
    screenOptions={{
      headerShown: false,
      headerTitleAlign: 'center',
      headerLeft: LeftHeaderButton,
      headerTintColor: colors.White,
      headerTitleStyle: stackStyles.commonStyles.headerTitleStyle,
      headerLeftContainerStyle: stackStyles.commonStyles.headerLeftContainerStyle,
      headerRightContainerStyle: stackStyles.commonStyles.headerRightContainerStyle,
      headerBackground: () => (
        <ImageBackground
          source={pngImages.headerBackground}
          style={stackStyles.commonStyles.headerBackground}
        />
      ),
    }}
  >
    <AuthNavigator.Screen
      name="Onboarding"
      component={Onboarding}
      options={{ title: 'Onboarding' }}
    />
    <AuthNavigator.Screen name="SignIn" options={{ title: 'Sign In' }} component={SignIn} />
    <AuthNavigator.Screen name="SignUp" options={{ title: 'Sign Up' }} component={SignUp} />
    <AuthNavigator.Screen
      name="AuthSubscriptions"
      component={AuthSubscriptions}
      options={{ title: 'Subscriptions' }}
    />
    <AuthNavigator.Screen
      name="AddingSubUsers"
      component={AddingSubUsers}
      options={{ title: 'Adding Users' }}
    />
    <AuthNavigator.Screen
      name="ForgotPassword"
      component={ForgotPassword}
      options={{ title: 'Forgot Password' }}
    />
    <AuthNavigator.Screen
      name="License"
      component={License}
      options={{ title: 'End User License Agreement', headerShown: true }}
    />
    <AuthNavigator.Screen
      name="PrivacyPolicy"
      component={PrivacyPolicy}
      options={{ title: 'Privacy Policy', headerShown: true }}
    />
  </AuthNavigator.Navigator>
)

export default Auth
