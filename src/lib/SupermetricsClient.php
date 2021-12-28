<?php

namespace supermetrics\lib;

use Exception;
use supermetrics\exception\InvalidTokenException;

/**
 * Connect to Supermetrics API and transfer data
 */
class SupermetricsClient
{

    /**
     * Supermetrics API base URL
     */
    private const API_BASE_URL = 'https://api.supermetrics.com/';
    /**
     * enable/disable log API requests/responses
     */
    private $isLog = false;
    /**
     * log file path
     */
    private $logFile = './log/SupermetricsClient.log';
    /**
     * email address
     */
    private $email;
    /**
     * name
     */
    private $name;
    /**
     * Client ID provided by Supermetrics
     */
    private $clientId;
    /**
     * Token registered with the API
     */
    private $token = 1;

    public function __construct(string $email, string $name, $clientId)
    {
        $this->email = $email;
        $this->name = $name;
        $this->clientId = $clientId;
    }

    public function enableLog($log_file_path = null)
    {
        $this->isLog = true;
        if ($log_file_path !== null) {
            $this->logFile = $log_file_path;
        }
    }

    /**
     * This function will handle all API calls
     * If postData is not null, consider this as a post request. otherwise, its a get request
     */
    private function sendRequest(string $requestPath, array $params = [], array $postData = []): object
    {
        $ch = curl_init();
        if (!empty($params)) {
            $requestPath .= '?' . http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL, self::API_BASE_URL . $requestPath);
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->isLog) {
            $this->log(self::API_BASE_URL . $requestPath);
            if (!empty($postData)) {
                $this->log('Post Data : ' . json_encode($postData));
            }
        }
        $result = curl_exec($ch);
        if ($this->isLog) {
            $this->log('Response : ' . $result);
        }

        if (empty($result)) {
            throw new Exception('No response from the API');
        }
        $response = json_decode($result);
        if (!empty($response->error) && $response->error->message == 'Invalid SL Token') {
            throw new InvalidTokenException('Expired or Invalid SL Token');
        }
        return $response;
    }

    /**
     * Get new token request
     */
    private function refreshToken(): void
    {
        $postData['client_id'] = $this->clientId;
        $postData['email'] = $this->email;
        $postData['name'] = $this->name;

        $tokenData = $this->sendRequest('assignment/register', [], $postData);
        if (empty($tokenData->data->sl_token)) {
            throw new Exception('No Token Received form the API');
        }
        $this->token = $tokenData->data->sl_token;
    }

    /**
     * Setup curl options and call the API
     * If postData is not null, consider this as a post request. otherwise, its a get request
     */
    protected function callAPI(string $requestPath, array $params = [], array $postData = []): object
    {
        if ($this->token === null) {
            $this->refreshToken();
        }
        $params['sl_token'] = $this->token;

        try {
            $retVal = $this->sendRequest($requestPath, $params, $postData);
        } catch (InvalidTokenException $e) {
            //Refresh the token and retry
            $this->refreshToken();
            $params['sl_token'] = $this->token;
            $retVal = $this->sendRequest($requestPath, $params, $postData);
        }

        return $retVal;
    }

    /**
     * Write logfile
     */
    private function log(string $data): void
    {
        file_put_contents($this->logFile, date("Y-m-d H:i:s - ") . $data . "\n", FILE_APPEND);
    }

}
