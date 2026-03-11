<?php

namespace App\Libraries;

class PolsriApiService
{
    private string $apiUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->apiUrl = (string) env('POLSRI_API_URL');
        $this->apiKey = (string) env('POLSRI_API_KEY');
    }

    public function fetchEmployees(): array
    {
        if ($this->apiUrl === '' || $this->apiKey === '') {
            return [];
        }

        try {
            $client = service('curlrequest');
            $response = $client->request('GET', $this->apiUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]);

            if ($response->getStatusCode() !== 200) {
                log_message('error', 'POLSRI API error: ' . $response->getStatusCode());
                return [];
            }

            return json_decode($response->getBody(), true) ?? [];
        } catch (\Throwable $e) {
            log_message('error', 'POLSRI API exception: ' . $e->getMessage());
            return [];
        }
    }
}
