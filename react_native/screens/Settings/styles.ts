import { StyleSheet } from 'react-native'

import colors from 'constants/colors'

export default StyleSheet.create({
  contentContainerStyle: { paddingHorizontal: 20, paddingBottom: 20 },
  switch: { alignSelf: 'center' },
  modalWindow: { backgroundColor: colors.PaleGray, padding: 0, borderRadius: 2 },
  modalWindowContainer: { width: '100%' },
})
