<?php

namespace app\services;

use Yii;

/**
 * Service for sending SMS via smspilot.ru
 */
class SmsService
{
    const API_URL = 'https://smspilot.ru/api.php';
    const API_KEY = 'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ';

    /**
     * Send SMS message
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return bool
     */
    public function send($phone, $message)
    {
        // Use emulator key for testing
        $apiKey = Yii::$app->params['smspilot']['apiKey'] ?? self::API_KEY;

        $params = [
            'send' => $message,
            'to' => $this->normalizePhone($phone),
            'apikey' => $apiKey,
            'format' => 'json',
        ];

        try {
            $response = $this->makeRequest($params);

            if ($response && isset($response['send'])) {
                foreach ($response['send'] as $result) {
                    if ($result['status'] === 0) {
                        Yii::info("SMS sent successfully to {$phone}", __METHOD__);
                        return true;
                    } else {
                        Yii::error("SMS sending failed: " . ($result['error']['description'] ?? 'Unknown error'), __METHOD__);
                    }
                }
            }

            return false;

        } catch (\Exception $e) {
            Yii::error("SMS service error: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Make HTTP request to API
     *
     * @param array $params
     * @return array|null
     */
    protected function makeRequest(array $params)
    {
        $url = self::API_URL . '?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            return json_decode($response, true);
        }

        return null;
    }

    /**
     * Normalize phone number format
     *
     * @param string $phone
     * @return string
     */
    protected function normalizePhone($phone)
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d\+]/', '', $phone);

        // If phone starts with 8, replace with +7
        if (strpos($phone, '8') === 0) {
            $phone = '+7' . substr($phone, 1);
        }

        // If phone doesn't start with +, add +7
        if (strpos($phone, '+') !== 0) {
            $phone = '+7' . $phone;
        }

        return $phone;
    }

    /**
     * Check SMS balance
     *
     * @return float|null
     */
    public function getBalance()
    {
        $apiKey = Yii::$app->params['smspilot']['apiKey'] ?? self::API_KEY;

        $params = [
            'balance' => '',
            'apikey' => $apiKey,
            'format' => 'json',
        ];

        try {
            $response = $this->makeRequest($params);

            if ($response && isset($response['balance'])) {
                return (float) $response['balance'];
            }

            return null;

        } catch (\Exception $e) {
            Yii::error("Failed to check balance: " . $e->getMessage(), __METHOD__);
            return null;
        }
    }
}
