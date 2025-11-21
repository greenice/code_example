import { ReactElement } from 'react'

import { RouteProp } from '@react-navigation/native'
import { StackNavigationProp } from '@react-navigation/stack'

import { HomeStackParamsList } from 'types/navigation'

export type DetailsScreenProps = {
  route: RouteProp<HomeStackParamsList, 'Home'>
  navigation: StackNavigationProp<HomeStackParamsList>
}

export type ModalWindowData = {
  isVisible: boolean
  children: ReactElement
}
