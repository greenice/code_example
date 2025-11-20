import React, { useRef, useState } from 'react'
import { Keyboard, StyleSheet, Text, TextInput, View } from 'react-native'

import { Formik, FormikConfig, FormikProps } from 'formik'
import Dropdown from 'react-native-input-select'
import { DropdownSelectHandle } from 'react-native-input-select/src/types/index.types'
import * as Yup from 'yup'

import { colors, constants, regexes, textStyles } from 'constants/index'
import { CommonSelectOptions, FormikErrors, UserAddFields } from 'types'
import { formatPhoneNumber, isLastItem, runAfterModalDismissed } from 'utils'

import { textInfoOfUserAddForm } from './constants'
import Input from '../Input'

interface UserAddFormProps extends FormikConfig<UserAddFields> {
  selectOptions: CommonSelectOptions[]
}

const UserAddForm = React.forwardRef<FormikProps<UserAddFields>, UserAddFormProps>(
  ({ selectOptions, ...props }, ref) => {
    const inputRefs = useRef<TextInput[]>([])
    const dropDownRef = useRef<DropdownSelectHandle | null>(null)
    const [isVisiblePassword, setVisiblePassword] = useState(false)
    const [isVisibleConfirmPassword, setVisibleConfirmPassword] = useState(false)
    const validationSchema = Yup.object().shape({
      name: Yup.string().required(FormikErrors.Required),
      lastName: Yup.string().required(FormikErrors.Required),
      phone: Yup.string()
        .matches(regexes.phoneNumberFormat, FormikErrors.PhoneNumberFormat)
        .required(FormikErrors.Required),
      email: Yup.string().email().required(FormikErrors.Required),
      password: Yup.string()
        .required(FormikErrors.Required)
        .min(constants.minimumPasswordCharacters, FormikErrors.MinimumCharacters)
        .matches(regexes.specialCharacter, FormikErrors.SpecialCharacter)
        .matches(regexes.passwordFormat, FormikErrors.PasswordFormat),
      confirmPassword: Yup.string()
        .required(FormikErrors.Required)
        .oneOf([Yup.ref('password'), ''], FormikErrors.PasswordsMustMatch),
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
              placeholder="NAME"
              value={values.name}
              autoComplete="name"
              returnKeyType="next"
              blurOnSubmit={false}
              autoCapitalize="words"
              textContentType="name"
              errorMessage={errors.name}
              onChangeText={handleChange('name')}
              onSubmitEditing={() => focusNextInput(0)}
              ref={el => {
                inputRefs.current[0] = el!
              }}
            />

            <Input
              isRequired
              placeholder="LAST NAME"
              value={values.lastName}
              autoComplete="name"
              returnKeyType="next"
              blurOnSubmit={false}
              autoCapitalize="words"
              textContentType="name"
              errorMessage={errors.lastName}
              onChangeText={handleChange('lastName')}
              onSubmitEditing={() => focusNextInput(1)}
              ref={el => {
                inputRefs.current[1] = el!
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
              onSubmitEditing={() => focusNextInput(2)}
              ref={el => {
                inputRefs.current[2] = el!
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
              onSubmitEditing={() => focusNextInput(3)}
              ref={el => {
                inputRefs.current[3] = el!
              }}
            />

            <Input
              isRequired
              returnKeyType="next"
              blurOnSubmit={false}
              autoCapitalize="none"
              placeholder="PASSWORD"
              autoComplete="password"
              value={values.password}
              textContentType="password"
              errorMessage={errors.password}
              secureTextEntry={!isVisiblePassword}
              onChangeText={handleChange('password')}
              onSubmitEditing={() => focusNextInput(4)}
              ref={el => {
                inputRefs.current[4] = el!
              }}
            >
              <Input.RightButton
                isVisible={isVisiblePassword}
                onPress={() => setVisiblePassword(!isVisiblePassword)}
              />
            </Input>

            <Input
              isRequired
              returnKeyType="next"
              autoCapitalize="none"
              autoComplete="password"
              textContentType="password"
              placeholder="CONFIRM PASSWORD"
              value={values.confirmPassword}
              errorMessage={errors.confirmPassword}
              secureTextEntry={!isVisibleConfirmPassword}
              onChangeText={handleChange('confirmPassword')}
              onSubmitEditing={() => focusNextInput(5)}
              ref={el => {
                inputRefs.current[5] = el!
              }}
            >
              <Input.RightButton
                isVisible={isVisibleConfirmPassword}
                onPress={() => setVisibleConfirmPassword(!isVisibleConfirmPassword)}
              />
            </Input>

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
  },
)

export default UserAddForm

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
