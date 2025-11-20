// @ts-nocheck
const migration = {
  1: state => {
    const updatedState = { ...state }

    updatedState.settings.isWarningUser = true
    updatedState.settings.radiusData.radius = 300

    return updatedState
  },
  2: state => {
    const updatedState = { ...state }

    updatedState.settings.coordinates = null
    updatedState.settings.isMediaLibraryRequestPermission = true

    return updatedState
  },
  3: state => {
    const updatedState = { ...state }

    updatedState.settings.availableResolutions = [
      { resolution: '1280x720', width: 1280, height: 720, area: 921600, isSelected: false },
      { resolution: '1920x1080', width: 1920, height: 1080, area: 2073600, isSelected: true },
      { resolution: '3840x2160', width: 3840, height: 2160, area: 8294400, isSelected: false },
    ]

    return updatedState
  },
}

export default migration
