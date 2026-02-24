<?php

namespace App\Services\External\Sms;

use App\Models\Company;
use App\Models\MessageSettings;
use Illuminate\Support\Facades\Http;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Exception;

class TwilioService implements SmsContract
{
    protected Client $client;
    protected MessageSettings $integration;

    /**
     * @throws Exception
     */
    public function __construct(protected Company $company)
    {
        $company->load('messageSettings');

        if ($company->messageSettings && $company->messageSettings->account_sid && $company->messageSettings->auth_token) {
            $this->integration = $company->messageSettings;
            $this->client = new Client($this->integration->account_sid, $this->integration->auth_token);
        } else {
            throw new Exception("Check service settings");
        }
    }

    /**
     * Configure Twilio integration: provision a phone number if needed and check A2P status.
     *
     * @return array{status: bool, message?: string, code?: int}
     */
    public function configure(): array
    {
        try {
            if (empty($this->integration->sms_phone_number) || !$this->checkNumber()) {
                $newNumberData = $this->buyNumber();
                if ($newNumberData['status'] === false) {
                    return [
                        'status' => false,
                        'message' => $newNumberData['message'],
                    ];
                }

                $this->integration->sms_phone_number = $newNumberData['number'];
                $this->integration->sms_phone_number_sid = $newNumberData['sid'];
                $this->integration->save();
                $this->setIncomingSMSWebHook($newNumberData['sid']);
            }

            $this->integration->a2p_enable = $this->isA2PEnable();
            $this->integration->save();

            return ['status' => true];
        } catch (Exception $e) {
            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send an SMS message via Twilio.
     *
     * @return array{status: bool, data?: array, message?: string, code?: int}
     */
    public function sendSMS(string $to, string $message, string $from): array
    {
        try {
            $data = [
                'from' => $from,
                'body' => $message,
            ];

            if (!app()->isLocal()) {
                $data['statusCallback'] = route('twilio.delivery.callback');
            }

            $response = $this->client->messages->create($to, $data);

            return [
                'status' => true,
                'data' => $response->toArray(),
            ];
        } catch (TwilioException $e) {
            return [
                'status' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if the stored phone number still exists in the Twilio account.
     */
    private function checkNumber(): bool
    {
        return count($this->client->incomingPhoneNumbers->read(['phoneNumber' => $this->integration->sms_phone_number])) > 0;
    }

    /**
     * Purchase a local US phone number from Twilio, filtered by state/city if configured.
     *
     * @return array{status: bool, sid?: string, number?: string, message?: string}
     */
    private function buyNumber(): array
    {
        $params = ['smsEnabled' => true];

        if ($this->integration->state) {
            $params['inRegion'] = $this->integration->state;
        }

        if ($this->integration->city) {
            $params['inLocality'] = $this->integration->city;
        }

        $numbers = $this->client->availablePhoneNumbers('US')->local->read($params);

        if (empty($numbers)) {
            return [
                'status' => false,
                'message' => 'No phone numbers are available in your location. Try to clear city field and search only by state',
            ];
        }

        $purchasedNumber = $this->client->incomingPhoneNumbers->create([
            'phoneNumber' => $numbers[0]->phoneNumber,
        ]);

        return [
            'status' => true,
            'sid' => $purchasedNumber->sid,
            'number' => $purchasedNumber->phoneNumber,
        ];
    }

    /**
     * Fetch the current Twilio account balance.
     *
     * @throws TwilioException
     */
    public function getBalance(): string
    {
        $balance = $this->client->api->v2010->balance->fetch();

        return number_format($balance->balance, 2) . ' ' . $balance->currency;
    }

    /**
     * Set the incoming SMS webhook URL for a purchased phone number.
     *
     * @throws TwilioException
     */
    private function setIncomingSMSWebHook(string $phoneNumberSid): void
    {
        $this->client->incomingPhoneNumbers($phoneNumberSid)
            ->update([
                'smsUrl' => config('services.twilio.webhook_sms'),
                'smsMethod' => 'POST',
            ]);
    }

    /**
     * Check if the company's phone number is registered under a verified A2P 10DLC campaign.
     */
    public function isA2PEnable(): bool
    {
        try {
            $services = $this->fetchMessagingServices();

            foreach ($services as $service) {
                if (!$this->hasVerifiedA2PCampaign($service['sid'])) {
                    continue;
                }

                if ($this->serviceHasCompanyNumber($service['sid'])) {
                    return true;
                }
            }

            return false;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Fetch all messaging services from the Twilio account.
     *
     * @return array<int, array<string, mixed>>
     */
    private function fetchMessagingServices(): array
    {
        $response = Http::retry(3)->withBasicAuth(
            $this->integration->account_sid,
            $this->integration->auth_token
        )->get(config('services.twilio.messaging_api_url') . '/Services');

        if (!$response->successful()) {
            return [];
        }

        return $response->json('services') ?? [];
    }

    /**
     * Check if a messaging service has at least one verified A2P campaign.
     */
    private function hasVerifiedA2PCampaign(string $serviceSid): bool
    {
        $response = Http::retry(3)->withBasicAuth(
            $this->integration->account_sid,
            $this->integration->auth_token
        )->get(config('services.twilio.messaging_api_url') . "/Services/$serviceSid/Compliance/Usa2p");

        if (!$response->successful()) {
            return false;
        }

        $complianceItems = $response->json('compliance') ?? [];

        foreach ($complianceItems as $compliance) {
            if (!empty($compliance['campaign_status']) && strtolower($compliance['campaign_status']) === 'verified') {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a messaging service contains the company's phone number.
     */
    private function serviceHasCompanyNumber(string $serviceSid): bool
    {
        $response = Http::retry(3)->withBasicAuth(
            $this->integration->account_sid,
            $this->integration->auth_token
        )->get(config('services.twilio.messaging_api_url') . "/Services/$serviceSid/PhoneNumbers");

        if (!$response->successful()) {
            return false;
        }

        $phoneNumbers = $response->json('phone_numbers') ?? [];

        foreach ($phoneNumbers as $number) {
            if ($number['account_sid'] === $this->integration->account_sid
                && $number['sid'] === $this->integration->sms_phone_number_sid) {
                return true;
            }
        }

        return false;
    }
}
