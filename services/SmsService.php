<?php

declare(strict_types=1);

namespace app\services;

use app\interfaces\NotificationServiceInterface;
use Exception;
use Yii;

/**
 * Service for sending SMS via smspilot.ru
 */
final class SmsService implements NotificationServiceInterface
{
    private const API_URL = 'https://smspilot.ru/api.php';
    private const API_KEY = 'XXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZXXXXXXXXXXXXYYYYYYYYYYYYZZZZZZZZ';

    private string $apiKey;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? (Yii::$app->params['smspilot']['apiKey'] ?? self::API_KEY);
    }

    /**
     * Send SMS message
     *
     * @param string $recipient Phone number
     * @param string $message Message text
     * @return bool
     */
    public function send(string $recipient, string $message): bool
    {
        $params = [
            'send' => $message,
            'to' => $this->normalizePhone($recipient),
            'apikey' => $this->apiKey,
            'format' => 'json',
        ];

        try {
            $response = $this->makeRequest($params);

            if ($response && isset($response['send'])) {
                foreach ($response['send'] as $result) {
                    if ($result['status'] === 0) {
                        Yii::info("SMS sent successfully to {$recipient}", __METHOD__);
                        return true;
                    } else {
                        Yii::error(
                            "SMS sending failed: " . ($result['error']['description'] ?? 'Unknown error'),
                            __METHOD__
                        );
                    }
                }
            }

            return false;
        } catch (Exception $e) {
            Yii::error("SMS service error: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * Normalize phone number format
     *
     * @param string $phone
     * @return string
     */
    private function normalizePhone(string $phone): string
    {
        // Remove all non-numeric characters except +
        $phone = preg_replace('/[^\d\+]/', '', $phone);

        // If phone starts with 8, replace with +7
        if (str_starts_with($phone, '8')) {
            $phone = '+7' . substr($phone, 1);
        }

        // If phone doesn't start with +, add +7
        if (!str_starts_with($phone, '+')) {
            $phone = '+7' . $phone;
        }

        return $phone;
    }

    /**
     * Make HTTP request to API
     *
     * @param array $params
     * @return array|null
     */
    private function makeRequest(array $params): ?array
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
}
