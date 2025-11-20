import { useCallback } from 'react'

import { SubscriptionStatuses } from 'types'
import { crashlyticsLog, fetchUserSubscriptionStatus } from 'utils'

import useAppSelector from './useAppSelector'

const useSubscriptionVerification = () => {
  const isSubscriptionActive = useAppSelector(state => state.subscriptions.isSubscriptionActive)

  return useCallback(async () => {
    try {
      crashlyticsLog('Subscription verification')
      const result = (await fetchUserSubscriptionStatus()).data
      crashlyticsLog(`Subscription verification status result: ${result.status}`)

      return result.status === SubscriptionStatuses.Active
    } catch {
      crashlyticsLog(
        `Subscription verification error. Status from local state: ${String(isSubscriptionActive)}`,
      )

      return isSubscriptionActive
    }
  }, [isSubscriptionActive])
}

export default useSubscriptionVerification
