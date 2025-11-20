import { createSlice } from '@reduxjs/toolkit'

import {
  overwritePhotoResolution,
  setPhotoResolution,
  resetPhotoResolution,
  setAutoCoordsMode,
  setAvailableResolutions,
} from 'modules/settings/actions'
import { SettingsState, UnitTypes } from 'types'

const initialState: SettingsState = {
  isLoading: false,
  coordinates: null,
  isWarningUser: true,
  defaultPhotoWidth: 0,
  defaultPhotoHeight: 0,
  photoResolution: null,
  isKeyboardOpen: false,
  isAutoCoordsMode: true,
  usedStorageVolume: null,
  lastConnectionApp: null,
  isSaveToPhoneMediaLibrary: true,
  isMediaLibraryRequestPermission: true,
  radiusData: { radius: 300, unit: UnitTypes.Feet },
  availableResolutions: [
    { resolution: '1280x720', width: 1280, height: 720, area: 921600, isSelected: false },
    { resolution: '1920x1080', width: 1920, height: 1080, area: 2073600, isSelected: true },
    { resolution: '3840x2160', width: 3840, height: 2160, area: 8294400, isSelected: false },
  ],
}

const settingsReducer = createSlice({
  name: 'settings',
  initialState,
  reducers: {},
  extraReducers: builder => {
    builder.addCase(setPhotoResolution, (state, action) => ({
      ...state,
      photoResolution: action.payload,
      defaultPhotoWidth: action.payload.width,
      defaultPhotoHeight: action.payload.height,
    }))
    builder.addCase(overwritePhotoResolution, (state, action) => ({
      ...state,
      photoResolution: action.payload,
    }))
    builder.addCase(resetPhotoResolution, state => ({
      ...state,
      photoResolution: null,
    }))
    builder.addCase(setAvailableResolutions, (state, action) => ({
      ...state,
      availableResolutions: action.payload,
    }))
    builder.addCase(setAutoCoordsMode, (state, action) => ({
      ...state,
      isAutoCoordsMode: action.payload,
    }))
  },
})

const reducer = {
  settings: settingsReducer.reducer,
}

export default reducer
