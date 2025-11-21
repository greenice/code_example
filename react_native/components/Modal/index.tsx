import React from 'react'
import {
  ActivityIndicator,
  Button,
  ButtonProps,
  Modal,
  Pressable,
  StyleSheet,
  Text,
  View,
  ViewStyle,
} from 'react-native'

import { SvgXml } from 'react-native-svg'

import { svgImages } from 'assets/images'
import { colors, constants, textStyles } from 'constants/index'

import Button from '../Button'

type ContentWrapperProps = {
  style?: ViewStyle
  onClose: () => void
  buttonTitle?: string
  onConfirm?: () => void
  children: React.ReactNode
  withoutConfirmButton?: boolean
  isDisabledConfirmButton?: boolean
}

type ConfirmActionProps = {
  text: string
  onClose: () => void
  onConfirm: () => void
}

type ModalProps = {
  visible: boolean
  style?: ViewStyle
  isLoading?: boolean
  onClose: () => void
  buttons?: ButtonProps[]
  containerStyle?: ViewStyle
  children?: React.ReactNode
  isContentWrapper?: boolean
}

const ModalContentWrapper: React.FC<ContentWrapperProps> = ({
  style,
  onClose,
  children,
  onConfirm,
  buttonTitle,
  isDisabledConfirmButton,
  withoutConfirmButton = false,
}) => (
  <>
    <View style={[styles.containerContent, style]}>
      {children}

      <Pressable hitSlop={constants.hitSlop} style={styles.closeButton} onPress={onClose}>
        <SvgXml xml={svgImages.blackClose} />
      </Pressable>
    </View>

    {!withoutConfirmButton && typeof onConfirm === 'function' && (
      <Button
        onPress={onConfirm}
        textStyle={textStyles.C3}
        style={styles.confirmButton}
        title={buttonTitle || 'Confirm'}
        disabled={isDisabledConfirmButton}
      />
    )}
  </>
)

const ConfirmAction: React.FC<ConfirmActionProps> = ({ onClose, onConfirm, text }) => (
  <ModalContentWrapper buttonTitle="Yes" onClose={onClose} onConfirm={onConfirm}>
    <View style={styles.confirmActionContainer}>
      <Text style={styles.text}>{text}</Text>
    </View>
  </ModalContentWrapper>
)

const Component: React.FC<ModalProps> = ({
  style,
  visible = false,
  onClose,
  buttons,
  children,
  isLoading = false,
  containerStyle,
  isContentWrapper,
}) => (
  <Modal animationType="fade" transparent visible={visible} onRequestClose={onClose}>
    <View style={styles.container}>
      <Pressable style={styles.pressable} onPress={onClose} disabled={isLoading} />

      {isLoading ? (
        <ActivityIndicator color={colors.White} />
      ) : (
        <View style={[styles.innerContainer, containerStyle]}>
          <View style={[styles.modalContent, isContentWrapper && styles.contentWrapper, style]}>
            {children}

            {buttons && (
              <View style={styles.buttonContainer}>
                {buttons.map(item => (
                  <Button
                    key={item.title}
                    color={item.color}
                    title={item.title}
                    onPress={item.onPress}
                    disabled={item.disabled}
                  />
                ))}
              </View>
            )}
          </View>
        </View>
      )}
    </View>
  </Modal>
)

const Modal = Object.assign(Component, { ModalContentWrapper, ConfirmAction })

export { Modal, ModalContentWrapper, ConfirmAction }

const styles = StyleSheet.create({
  container: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: colors.SemiTransparentBlack,
  },
  pressable: {
    position: 'absolute',
    width: constants.appWidth,
    height: constants.appHeight,
  },
  innerContainer: { paddingHorizontal: 20 },
  modalContent: {
    padding: 20,
    borderRadius: 10,
    alignItems: 'center',
    backgroundColor: colors.White,
  },
  contentWrapper: { backgroundColor: colors.PaleGray, padding: 0, borderRadius: 2 },
  buttonContainer: {
    width: '100%',
    borderTopWidth: 1,
    flexDirection: 'row',
    borderTopColor: colors.Silver,
    justifyContent: 'space-between',
  },
  /** ContentWrapper */
  containerContent: { padding: 20, width: '100%' },
  closeButton: { position: 'absolute', alignSelf: 'flex-end', top: 10, right: 10 },
  confirmButton: { height: 40, borderRadius: 0 },
  /** ConfirmAction */
  confirmActionContainer: { marginTop: 3 },
  text: { textAlign: 'center', ...textStyles.C3 },
})
