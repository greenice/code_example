import React, { useCallback, useState } from 'react'
import { Linking, SectionList, Switch } from 'react-native'

import { useFocusEffect } from '@react-navigation/native'

import { CoordsModeInfo, Modal } from 'components'
import { supports } from 'constants/index'
import { useAppDispatch, useAppSelector } from 'hooks'
import {
  getUsedStorageVolume,
  setAvailableResolutions,
  setRadiusData,
  setSaveToPhoneMediaLibrary,
} from 'modules/settings/actions'
import { AlertMessages, AlertTitles, UserRoles } from 'types'
import { isMediaLibraryPermissionGranted, showAlert } from 'utils'

import {
  Footer,
  InviteFriend,
  RadiusResizer,
  RenderButton,
  SectionHeader,
  SelectResolution,
  Separator,
  SyncButton,
  UsedStorageVolume,
} from './components'
import styles from './styles'
import { DetailsScreenProps, ModalWindowData, SettingsData } from './types'

const Settings: React.FC<DetailsScreenProps> = ({ navigation }) => {
  const dispatch = useAppDispatch()
  const user = useAppSelector(state => state.user.user)
  const { isSaveToPhoneMediaLibrary } = useAppSelector(state => state.settings)
  const lastConnectionApp = useAppSelector(state => state.settings.lastConnectionApp)
  const [modalWindowData, setModalWindowData] = useState<ModalWindowData | null>(null)
  const [isValue, setIsValue] = useState<boolean>(true)

  useFocusEffect(
    useCallback(() => {
      dispatch(getUsedStorageVolume())

      const checkPermission = async () => {
        const isPermissionGranted = await isMediaLibraryPermissionGranted()
        const isGranted = isPermissionGranted && isSaveToPhoneMediaLibrary

        setIsValue(isGranted)
      }

      checkPermission().then()
    }, []),
  )

  const onToggleSwitch = async (value: boolean) => {
    if (value) {
      setIsValue(value)

      const isPermissionGranted = await isMediaLibraryPermissionGranted()

      if (isPermissionGranted) {
        dispatch(setSaveToPhoneMediaLibrary(value))
      } else {
        showAlert({
          title: AlertTitles.PermissionDenied,
          message: AlertMessages.MediaMessage,
          buttons: [
            {
              text: 'Cancel',
              style: 'cancel',
              onPress: () => setIsValue(false),
            },
            { text: 'Go to settings', onPress: Linking.openSettings },
          ],
        })
      }
    } else {
      setIsValue(value)
      dispatch(setSaveToPhoneMediaLibrary(value))
    }
  }

  const sections: SettingsData[] = [
    {
      title: 'Preferences',
      data: [
        {
          title: 'Select Image Size',
          disabled: false,
          isVisible: true,
          onPress: () =>
            setModalWindowData({
              isVisible: true,
              children: (
                <SelectResolution
                  onClose={() => setModalWindowData(null)}
                  onConfirm={resolutions => {
                    dispatch(setAvailableResolutions(resolutions))
                    setModalWindowData(null)
                  }}
                />
              ),
            }),
        },
        {
          title: 'Set Radius',
          disabled: false,
          isVisible: true,
          onPress: () =>
            setModalWindowData({
              isVisible: true,
              children: (
                <RadiusResizer
                  onClose={() => setModalWindowData(null)}
                  onConfirm={(radius, unit) => {
                    dispatch(setRadiusData({ radius, unit }))
                    setModalWindowData(null)
                  }}
                />
              ),
            }),
        },
        {
          title: 'Account Storage',
          disabled: false,
          isVisible: true,
          onPress: () =>
            setModalWindowData({
              isVisible: true,
              children: (
                <UsedStorageVolume
                  onClose={() => setModalWindowData(null)}
                  onConfirm={() => setModalWindowData(null)}
                />
              ),
            }),
        },
      ],
    },
  ]

  const data = sections.map(item => ({ ...item, data: item.data.filter(i => i.isVisible) }))

  return (
    <>
      <SectionList
        sections={data}
        stickySectionHeadersEnabled={false}
        showsVerticalScrollIndicator={false}
        ListFooterComponent={() => <Footer />}
        renderSectionFooter={() => <Separator />}
        contentContainerStyle={styles.contentContainerStyle}
        ItemSeparatorComponent={() => <Separator />}
        keyExtractor={({ title }, index) => `${title}${index}`}
        renderItem={({ item }) => <RenderButton {...item} />}
        renderSectionHeader={({ section: { title } }) => <SectionHeader title={title} />}
      />

      <Modal
        style={styles.modalWindow}
        visible={!!modalWindowData?.isVisible}
        containerStyle={styles.modalWindowContainer}
        onClose={() => setModalWindowData(null)}
      >
        {modalWindowData?.children}
      </Modal>
    </>
  )
}

export default Settings
