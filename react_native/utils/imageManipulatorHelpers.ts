import { Image } from 'react-native'

import { ImageManipulator, SaveFormat } from 'expo-image-manipulator'

const imageRotation = async ({
  uri,
  compress = 1,
  rotationValue,
}: {
  uri: string
  compress?: number
  rotationValue: number
}): Promise<string> => {
  try {
    const context = ImageManipulator.manipulate(uri)

    context.rotate(rotationValue)

    const image = await context.renderAsync()
    const rotatedImage = await image.saveAsync({
      compress,
      format: SaveFormat.JPEG,
    })

    return rotatedImage.uri
  } catch {
    return ''
  }
}

const imageResizer = async ({
  uri,
  originalWidth,
  originalHeight,
  targetWidth = 320,
  compress = 0.5,
}: {
  uri: string
  originalWidth?: number
  originalHeight?: number
  targetWidth?: number
  compress?: number
}) => {
  try {
    let width = originalWidth
    let height = originalHeight

    if (!width || !height) {
      const dimensions = await new Promise<{
        width: number
        height: number
      }>(resolve => {
        Image.getSize(uri, (w, h) => resolve({ width: w, height: h }))
      })

      width = dimensions.width
      height = dimensions.height
    }

    const ratio = width / height
    const targetHeight = Math.round(targetWidth / ratio)
    const context = ImageManipulator.manipulate(uri)

    context.resize({ width: targetWidth, height: targetHeight })

    const image = await context.renderAsync()
    const resizedImage = await image.saveAsync({
      compress,
      format: SaveFormat.JPEG,
    })

    return resizedImage.uri
  } catch {
    return ''
  }
}

const simpleResizeImage = async ({
  uri,
  targetWidth,
  targetHeight,
  compress = 1,
}: {
  uri: string
  compress?: number
  targetWidth?: number
  targetHeight?: number
}) => {
  const context = ImageManipulator.manipulate(uri)

  if (targetWidth && targetHeight) {
    context.resize({ width: targetWidth, height: targetHeight })
  }

  const image = await context.renderAsync()
  const resizedImage = await image.saveAsync({
    compress,
    format: SaveFormat.JPEG,
  })

  return resizedImage
}

export { simpleResizeImage, imageRotation, imageResizer }
