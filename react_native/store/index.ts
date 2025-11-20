import AsyncStorage from '@react-native-async-storage/async-storage'
import { configureStore } from '@reduxjs/toolkit'
import { Action } from 'redux'
import { createMigrate, persistReducer, persistStore } from 'redux-persist'
import type { Persistor } from 'redux-persist/es/types'
import { ThunkDispatch } from 'redux-thunk'

import reducers from 'modules'

import migration from './migration'

const persistConfig = {
  key: 'root',
  version: 3,
  storage: AsyncStorage,
  migrate: createMigrate(migration, { debug: __DEV__ }),
  whitelist: ['auth', 'subscriptions', 'user', 'queued', 'locations', 'settings'],
}

const persistedReducers = persistReducer(persistConfig, reducers)

const store = configureStore({
  reducer: persistedReducers,
  middleware: getDefaultMiddleware =>
    getDefaultMiddleware({
      serializableCheck: false,
      immutableCheck: false,
    }),
})

const persistor: Persistor = persistStore(store, null, () => {
  store.getState()
})

export type RootState = ReturnType<typeof store.getState>
export type TypedDispatch<T> = ThunkDispatch<T, any, Action>

export { store, persistor }
