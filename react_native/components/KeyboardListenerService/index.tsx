import { useEffect } from 'react'
import { Keyboard } from 'react-native'

import { useAppDispatch } from 'hooks'
import { setKeyboardState } from 'modules/settings/actions'

const KeyboardListenerService = () => {
  const dispatch = useAppDispatch()

  useEffect(() => {
    const keyboardDidShowListener = Keyboard.addListener('keyboardDidShow', () => {
      dispatch(setKeyboardState(true))
    })
    const keyboardDidHideListener = Keyboard.addListener('keyboardDidHide', () => {
      dispatch(setKeyboardState(false))
    })

    return () => {
      keyboardDidShowListener.remove()
      keyboardDidHideListener.remove()
    }
  }, [])

  return null
}

export default KeyboardListenerService
