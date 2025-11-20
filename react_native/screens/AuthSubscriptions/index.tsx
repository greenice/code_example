import React from 'react'
import { ImageBackground, ScrollView } from 'react-native'

import { useNavigation } from '@react-navigation/native'
import { StackNavigationProp } from '@react-navigation/stack'
import { ProductAndroid, ProductIOS } from 'react-native-iap'
import { SafeAreaView } from 'react-native-safe-area-context'

import { pngImages } from 'assets/images'
import {
  AndroidSubscriptionsFlatList,
  WaitingModal,
  IosSubscriptionsFlatList,
  SubscriptionsContentWrapper,
} from 'components'
import { constants } from 'constants/index'
import { useAppDispatch, useAppSelector, usePurchase } from 'hooks'
import { notifySubscription, setWaiting } from 'modules/subscriptions/action'
import { AlertMessages, AlertTitles, AuthStackParamList, DataOfSelectedSubscription } from 'types'
import { isInternetConnection, showAlert } from 'utils'

import styles from './styles'

const AuthSubscriptions: React.FC = () => {
  const { isWaiting, subscriptions } = useAppSelector(state => state.subscriptions)
  const { handlerPurchase } = usePurchase()
  const dispatch = useAppDispatch()
  const navigation = useNavigation<StackNavigationProp<AuthStackParamList>>()

  const onPurchaseSubscription = async (sku: DataOfSelectedSubscription) => {
    try {
      dispatch(setWaiting(true))

      await handlerPurchase({
        sku,
        onSuccess: async purchase => {
          await dispatch(notifySubscription({ subscription: purchase, selectedSubscription: sku }))

          dispatch(setWaiting(false))
          navigation.navigate('AddingSubUsers')
        },
        onError: async () => {
          dispatch(setWaiting(false))
        },
      })
    } catch {
      dispatch(setWaiting(false))
    }
  }

  return (
    <>
      <ImageBackground source={pngImages.background} style={styles.container}>
        <SafeAreaView style={styles.container}>
          <ScrollView contentContainerStyle={styles.scrollView}>
            <SubscriptionsContentWrapper>
              {constants.isIos && subscriptions && (
                <IosSubscriptionsFlatList
                  data={subscriptions as ProductIOS[]}
                  onPressUnsubscribe={() => {}}
                  onPressCardButton={async sku => {
                    const isOnline = await isInternetConnection()

                    if (isOnline) {
                      await onPurchaseSubscription({ productId: sku.id })
                    } else {
                      showAlert({
                        title: AlertTitles.Attention,
                        message: AlertMessages.SubscriptionDoesNotWorkOffline,
                      })
                    }
                  }}
                />
              )}

              {constants.isAndroid && subscriptions && (
                <AndroidSubscriptionsFlatList
                  data={(subscriptions as ProductAndroid[])[0].subscriptionOfferDetailsAndroid}
                  onPressUnsubscribe={() => {}}
                  onPressCardButton={async sku => {
                    const isOnline = await isInternetConnection()

                    if (isOnline) {
                      await onPurchaseSubscription({
                        productId: subscriptions[0].id,
                        androidData: {
                          basePlanId: sku.basePlanId,
                          offerToken: sku.offerToken,
                        },
                      })
                    } else {
                      showAlert({
                        title: AlertTitles.Attention,
                        message: AlertMessages.SubscriptionDoesNotWorkOffline,
                      })
                    }
                  }}
                />
              )}
            </SubscriptionsContentWrapper>
          </ScrollView>
        </SafeAreaView>
      </ImageBackground>

      <WaitingModal isVisible={isWaiting} />
    </>
  )
}

export default AuthSubscriptions
