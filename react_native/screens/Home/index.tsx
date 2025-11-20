import React, { useCallback, useMemo, useState } from 'react'
import { FlatList, RefreshControl, View } from 'react-native'

import { useActionSheet } from '@expo/react-native-action-sheet'
import { useFocusEffect } from '@react-navigation/native'
import * as Clipboard from 'expo-clipboard'

import { ConfirmAction, Modal, SearchInput } from 'components'
import colors from 'constants/colors'
import { constants } from 'constants/index'
import { useAppDispatch, useAppSelector, useInternetConnection } from 'hooks'
import {
  deleteLocation,
  getListOfLocations,
  moveLocationsToArchive,
  renameLocation,
  shareLocation,
} from 'modules/locations/actions'
import {
  ActionSheetMessages,
  ActionSheetTitles,
  Location,
  ToastMessages,
  ToastTitles,
  ToastTypes,
} from 'types'
import {
  filterArrayByString,
  filterQueuedFilesByUserId,
  getDisabledIndices,
  showToastMessage,
} from 'utils'

import { ModalSteps } from './components/ModalSteps'
import { RenameLocationWrapper } from './components/RenameLocationWrapper'
import { RenderCard } from './components/RenderCard'
import styles from './styles'
import { DetailsScreenProps, ModalWindowData } from './types'
import { parsedHomeData } from './utils/homeHelpers'

const Home: React.FC<DetailsScreenProps> = ({ navigation }) => {
  const user = useAppSelector(state => state.user.user)
  const { userId, listOfLocations, isLoading } = useAppSelector(state => state.locations)
  const queuedMediaFiles = useAppSelector(state => state.queued.queuedFiles)
  const dispatch = useAppDispatch()
  const isOnline = useInternetConnection()
  const { showActionSheetWithOptions } = useActionSheet()
  const parsedData = parsedHomeData(
    listOfLocations ?? [],
    filterQueuedFilesByUserId(queuedMediaFiles, user),
  )
  const [isRefresh, setIsRefresh] = useState<boolean>(false)
  const [inputValue, setInputValue] = useState<string>('')
  const [modalWindowData, setModalWindowData] = useState<ModalWindowData | null>(null)

  useFocusEffect(
    useCallback(() => {
      dispatch(getListOfLocations())
    }, []),
  )

  const data = useMemo(
    () => filterArrayByString(inputValue, parsedData, ['location_name']) as Location[],
    [parsedData, inputValue],
  )

  const onPressCard = useCallback(
    (location: Location) => {
      if (location.location_id === constants.queue) {
        navigation.navigate('QueueAlbum')
      } else {
        navigation.navigate('LocationAlbum', {
          userId,
          count: Number(location.count),
          locationId: location.location_id,
          titleScreen: location.location_name,
        })
      }
    },
    [userId, isOnline],
  )

  const handleShowMenu = useCallback(
    (location: Location) => {
      const options = ['Share', 'Rename', 'Archive', 'Add new media files', 'Delete', 'Cancel']
      const cancelButtonIndex = options.length - 1
      const destructiveButtonIndex = options.indexOf('Delete')
      const disabledButtonIndices = getDisabledIndices(
        options,
        [options[0], options[1], options[2], options[4]],
        isOnline,
      )
      const message = `${ActionSheetMessages.SelectActionForProject.replace(
        'locationName',
        location.location_name,
      )}\nRadius: ${location.radius}`

      showActionSheetWithOptions(
        {
          options,
          message,
          cancelButtonIndex,
          disabledButtonIndices,
          destructiveButtonIndex,
          title: ActionSheetTitles.Options,
        },
        selectedIndex => {
          switch (selectedIndex) {
            case 0:
              setModalWindowData({
                isVisible: true,
                children: (
                  <ModalSteps
                    onClose={() => setModalWindowData(null)}
                    onCopyLink={() =>
                      Clipboard.setStringAsync(location.public_url).then(() => {
                        showToastMessage({
                          type: ToastTypes.Success,
                          title: ToastTitles.ActionSuccessful,
                          description: ToastMessages.TextCopied.replace('Text', 'Link'),
                        })
                        setModalWindowData(null)
                      })
                    }
                    onSendEmail={email =>
                      dispatch(shareLocation({ locationId: location.location_id, email })).finally(
                        () => setModalWindowData(null),
                      )
                    }
                  />
                ),
              })
              break
            case 1:
              setModalWindowData({
                isVisible: true,
                children: (
                  <RenameLocationWrapper
                    locationName={location.location_name}
                    onClose={() => setModalWindowData(null)}
                    onConfirm={value =>
                      dispatch(
                        renameLocation({ locationId: location.location_id, newName: value }),
                      ).finally(() => setModalWindowData(null))
                    }
                  />
                ),
              })
              break
            case 2:
              setModalWindowData({
                isVisible: true,
                children: (
                  <ConfirmAction
                    text="Are you sure you want to archive this project?"
                    onClose={() => setModalWindowData(null)}
                    onConfirm={() =>
                      dispatch(moveLocationsToArchive([location.location_id])).finally(() =>
                        setModalWindowData(null),
                      )
                    }
                  />
                ),
              })
              break
            case 3:
              navigation.getParent()?.navigate('DrawerCamera', {
                screen: 'Camera',
                params: {
                  locationId: location.location_id,
                  latitude: Number(location.latitude),
                  longitude: Number(location.longitude),
                },
              })
              break
            case 4:
              setModalWindowData({
                isVisible: true,
                children: (
                  <ConfirmAction
                    text="Are you sure you want to delete this project?"
                    onClose={() => setModalWindowData(null)}
                    onConfirm={() =>
                      dispatch(
                        deleteLocation({ locationId: location.location_id, isWithToast: true }),
                      ).finally(() => setModalWindowData(null))
                    }
                  />
                ),
              })
              break
            default:
              break
          }
        },
      )
    },
    [isOnline],
  )

  return (
    <>
      <SearchInput
        value={inputValue}
        onChangeText={setInputValue}
        containerStyle={styles.searchInput}
      >
        <SearchInput.RightButton onPress={() => setInputValue('')} />
      </SearchInput>

      <FlatList
        data={data}
        numColumns={2}
        columnWrapperStyle={styles.columnWrapperStyle}
        contentContainerStyle={styles.contentContainerStyle}
        ItemSeparatorComponent={() => <View style={styles.separator} />}
        keyExtractor={item => item.location_id.toString()}
        renderItem={({ item }) => (
          <RenderCard
            imageUri={item.thumbnail}
            title={item.location_name}
            videoCount={item.video_count}
            photoCount={item.photo_count}
            isDisabled={item.location_id !== constants.queue && !isOnline}
            isWithMenu={![constants.queue, user?.defaultLocationId].includes(item.location_id)}
            onPress={() => onPressCard(item)}
            onPressMenu={() => handleShowMenu(item)}
          />
        )}
        refreshControl={
          <RefreshControl
            refreshing={isRefresh}
            colors={[colors.Purple]}
            tintColor={colors.Purple}
            onRefresh={async () => {
              setIsRefresh(true)

              await dispatch(getListOfLocations())

              setIsRefresh(false)
            }}
          />
        }
      />

      <Modal
        isContentWrapper
        isLoading={isLoading}
        containerStyle={styles.modalWindow}
        visible={!!modalWindowData?.isVisible}
        onClose={() => setModalWindowData(null)}
      >
        {modalWindowData?.children}
      </Modal>
    </>
  )
}

export default Home
