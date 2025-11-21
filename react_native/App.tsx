import React from 'react'

import { ActionSheetProvider } from '@expo/react-native-action-sheet'
import { initialWindowMetrics, SafeAreaProvider } from 'react-native-safe-area-context'
import Toast from 'react-native-toast-message'
import { Provider } from 'react-redux'

import {
  ForceUpdateService,
  InitGate,
  KeyboardListenerService,
  LastConnectionAppService,
  PurchaseProvider,
  QueueLoadingService,
  SubscriptionService,
} from 'components'
import { toastConfig } from 'constants/index'
import RootNavigation from 'navigation/RootNavigation'

import { persistor, store } from './store'

const App = () => (
  <SafeAreaProvider initialMetrics={initialWindowMetrics}>
    <Provider store={store}>
      <InitGate persistor={persistor}>
        <PurchaseProvider>
          <ActionSheetProvider>
            <RootNavigation />
          </ActionSheetProvider>

          <Toast config={toastConfig} />
          <SubscriptionService />
          <ForceUpdateService />
          <QueueLoadingService />
          <KeyboardListenerService />
          <LastConnectionAppService />
        </PurchaseProvider>
      </InitGate>
    </Provider>
  </SafeAreaProvider>
)

export default App
