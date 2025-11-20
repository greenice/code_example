import { createSlice } from '@reduxjs/toolkit'

import { clearAuthState, forgotPassword, logIn, setToken, signUp } from 'modules/auth/actions'
import { AuthState } from 'types'

const initialState: AuthState = {
  accessToken: '',
  isLoading: false,
}

const authReducer = createSlice({
  name: 'auth',
  initialState,
  reducers: {},
  extraReducers: builder => {
    builder.addCase(setToken, (state, action) => ({
      ...state,
      accessToken: action.payload,
    }))
    builder.addCase(clearAuthState, () => ({
      ...initialState,
    }))
    builder.addCase(forgotPassword.fulfilled, state => ({
      ...state,
      isLoading: false,
    }))
  },
})

const reducer = {
  auth: authReducer.reducer,
}

export default reducer
