import { ReactElement } from 'react'
import { ButtonProps, PressableProps } from 'react-native'

import { RouteProp } from '@react-navigation/native'
import { StackNavigationProp } from '@react-navigation/stack'

import { SettingsStackParamsList } from 'types/navigation'

export type DetailsScreenProps = {
  route: RouteProp<SettingsStackParamsList, 'Settings'>
  navigation: StackNavigationProp<SettingsStackParamsList>
}

export interface SettingButton extends PressableProps {
  title: string
  isVisible: boolean
  children?: ReactElement
}

export type SettingsData = {
  title: string
  data: SettingButton[]
}

export type ModalWindowData = {
  isVisible: boolean
  children: ReactElement
}

export type FooterModalWindowData = ModalWindowData & { buttons: ButtonProps[] }
