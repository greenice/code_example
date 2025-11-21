import { combineReducers } from '@reduxjs/toolkit'

import authReducer from 'modules/auth'
import settingsReducer from 'modules/settings'

const reducers = combineReducers({
  ...authReducer,
  ...settingsReducer,
})

export default reducers
