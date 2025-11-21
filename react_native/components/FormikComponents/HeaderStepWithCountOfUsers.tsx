import React from 'react'
import { StyleSheet, Text, View, ViewStyle } from 'react-native'

import { colors, textStyles } from 'constants/index'
import { valueCheck } from 'utils'

type WithoutCountProps = {
  isWithoutCount: true
  title: string
  subTitle?: string
  totalNumber?: number | string
  currentNumber?: number | string
  containerStyle?: ViewStyle
}

type WithCountProps = {
  isWithoutCount?: false
  title: string
  subTitle: string
  totalNumber: number | string
  currentNumber: number | string
  containerStyle?: ViewStyle
}

type StepHeaderProps = WithCountProps | WithoutCountProps

const HeaderStepWithCountOfUsers: React.FC<StepHeaderProps> = ({
  title,
  subTitle,
  totalNumber,
  currentNumber,
  isWithoutCount,
  containerStyle,
}) => (
  <View style={containerStyle}>
    <Text style={styles.title}>{title}</Text>

    {!isWithoutCount && (
      <View style={styles.stepsContainer}>
        <Text style={styles.subTitle}>
          {subTitle} {valueCheck(currentNumber)} / {valueCheck(totalNumber)}
        </Text>
      </View>
    )}
  </View>
)

export default HeaderStepWithCountOfUsers

const styles = StyleSheet.create({
  title: { color: colors.DarkCharcoal, textAlign: 'center', ...textStyles.H7 },
  stepsContainer: { marginTop: 10 },
  subTitle: { color: colors.DarkCharcoal, ...textStyles.C8 },
})
