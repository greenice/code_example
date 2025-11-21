import { constants } from 'constants/index'
import { FileData, Location, MediaTypes } from 'types'

export const parsedHomeData = (listOfLocations: Location[], queuedMediaFiles: FileData[]) => {
  if (!queuedMediaFiles.length) {
    return listOfLocations
  }

  const videoCount = queuedMediaFiles.filter(item => item.type === MediaTypes.Video).length
  const photoCount = queuedMediaFiles.filter(item => item.type === MediaTypes.Photo).length
  const lastMediaFiles = queuedMediaFiles[queuedMediaFiles.length - 1]

  return [
    {
      location_id: constants.queue,
      thumbnail: lastMediaFiles.thumbnail,
      location_name: 'Files to be uploaded',
      video_count: videoCount.toString(),
      photo_count: photoCount.toString(),
    },
    ...listOfLocations,
  ]
}
