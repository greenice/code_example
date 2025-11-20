import React, { useRef } from 'react'
import { Keyboard, StyleSheet, Text, TextInput, View } from 'react-native'

import { Formik, FormikConfig, FormikProps } from 'formik'
import Dropdown from 'react-native-input-select'
import { DropdownSelectHandle } from 'react-native-input-select/src/types/index.types'
import * as Yup from 'yup'

import { colors, regexes, textStyles } from 'constants/index'
import { CommonSelectOptions, FormikErrors, ShortenedUserAddFields } from 'types'
import { formatPhoneNumber, isLastItem, runAfterModalDismissed } from 'utils'

import { textInfoOfUserAddForm } from './constants'
import Input from '../Input'

interface UserAddFormProps extends FormikConfig<ShortenedUserAddFields> {
  selectOptions: CommonSelectOptions[]
}

const ShortenedUserAddForm = React.forwardRef<
  FormikProps<ShortenedUserAddFields>,
  UserAddFormProps
>(({ selectOptions, ...props }, ref) => {
  const inputRefs = useRef<TextInput[]>([])
  const dropDownRef = useRef<DropdownSelectHandle | null>(null)
  const validationSchema = Yup.object().shape({
    fullName: Yup.string().required(FormikErrors.Required),
    phone: Yup.string()
      .matches(regexes.phoneNumberFormat, FormikErrors.PhoneNumberFormat)
      .required(FormikErrors.Required),
    email: Yup.string().email().required(FormikErrors.Required),
    role: Yup.string().required(FormikErrors.Required),
  })

  const focusNextInput = (index: number) => {
    if (inputRefs.current[index + 1]) {
      inputRefs.current[index + 1].focus()
    } else {
      Keyboard.dismiss()

      runAfterModalDismissed(() => dropDownRef.current?.open())
    }
  }

  return (
    <Formik innerRef={ref} validationSchema={validationSchema} {...props}>
      {({ handleChange, errors, values, setFieldValue }) => (
        <>
          <Input
            isRequired
            placeholder="FULL NAME"
            value={values.fullName}
            autoComplete="name"
            returnKeyType="next"
            blurOnSubmit={false}
            autoCapitalize="words"
            textContentType="name"
            errorMessage={errors.fullName}
            onChangeText={handleChange('fullName')}
            onSubmitEditing={() => focusNextInput(0)}
            ref={el => {
              inputRefs.current[0] = el!
            }}
          />

          <Input
            isRequired
            placeholder="PHONE NUMBER"
            value={values.phone}
            returnKeyType="next"
            blurOnSubmit={false}
            autoComplete="cc-number"
            errorMessage={errors.phone}
            onChangeText={text => setFieldValue('phone', formatPhoneNumber(text))}
            onSubmitEditing={() => focusNextInput(1)}
            ref={el => {
              inputRefs.current[1] = el!
            }}
          />

          <Input
            isRequired
            placeholder="EMAIL"
            value={values.email}
            autoComplete="email"
            returnKeyType="next"
            blurOnSubmit={false}
            autoCapitalize="none"
            errorMessage={errors.email}
            keyboardType="email-address"
            textContentType="emailAddress"
            onChangeText={handleChange('email')}
            onSubmitEditing={() => focusNextInput(2)}
            ref={el => {
              inputRefs.current[2] = el!
            }}
          />

          <Dropdown
            ref={dropDownRef}
            error={errors.role}
            primaryColor="green"
            options={selectOptions}
            placeholder="SELECT ROLE*"
            selectedValue={values.role}
            onValueChange={value => setFieldValue('role', value)}
            dropdownIconStyle={styles.dropdownIcon}
            dropdownErrorStyle={styles.dropdownBorder}
            placeholderStyle={styles.dropdownPlaceholder}
            selectedItemStyle={{ ...textStyles.TextInput }}
            dropdownErrorTextStyle={styles.dropdownErrorText}
            dropdownContainerStyle={styles.dropdownContainer}
            dropdownStyle={{ ...styles.dropdown, ...styles.dropdownBorder }}
          />

          {textInfoOfUserAddForm.map((item, index, array) => (
            <View
              key={`${item.title}_${item.text}`}
              style={!isLastItem(index, array) && styles.marginBottom}
            >
              <Text style={styles.title}>{item.title}:</Text>

              <Text style={styles.text}>{item.text}</Text>
            </View>
          ))}
        </>
      )}
    </Formik>
  )
})

export default ShortenedUserAddForm

const styles = StyleSheet.create({
  dropdownContainer: { marginTop: 43 },
  dropdown: {
    paddingTop: 0,
    minHeight: 36,
    alignItems: 'center',
    backgroundColor: colors.Transparent,
  },
  dropdownBorder: {
    borderWidth: 0,
    borderBottomWidth: 1,
    borderBottomLeftRadius: 0,
    borderBottomRightRadius: 0,
    borderColor: colors.Silver,
  },
  dropdownIcon: { top: 8, right: 10 },
  dropdownPlaceholder: { color: colors.Silver, ...textStyles.Placeholder },
  dropdownErrorText: { color: colors.Red, fontSize: 14, marginTop: 5 },
  marginBottom: { marginBottom: 16 },
  title: { ...textStyles.C2 },
  text: { flex: 1 },
})
