import { createAction, createAsyncThunk } from '@reduxjs/toolkit'
import { Dayjs } from 'dayjs'

import endpoints from 'constants/endpoints'
import { Coordinates, RadiusData, Resolution, UsedStorageVolume } from 'types'
import { api } from 'utils'

import * as types from './types'

export const setPhotoResolution = createAction<{ width: number; height: number }>(
  types.SET_PHOTO_RESOLUTION,
)
export const resetPhotoResolution = createAction(types.RESET_PHOTO_RESOLUTION)
export const setAvailableResolutions = createAction<Resolution[]>(types.SET_AVAILABLE_RESOLUTION)
export const setAutoCoordsMode = createAction<boolean>(types.SET_AUTO_COORDS_MODE)
