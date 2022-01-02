<?php

namespace supermetrics\lib;

use DateTime;
use Exception;
use InvalidArgumentException;
use supermetrics\exception\InvalidTokenException;

/**
 * Connect to Supermetrics API and transfer data
 */
class SupermetricsClient
{

    /**
     * Supermetrics API base URL
     */
    private $apiBaseUrl = 'https://api.supermetrics.com/';
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
    private $token;
    /**
     * Curl handlers
     */
    private $ch;
    /**
     * Constructor.
     *
     * @param string $email  
     * @param string $name
     * @param string $clientId
     * @param string $apiBaseUrl
     *
     * @throws InvalidArgumentException
     * @throws \Exception
     */

    public function __construct(string $email, string $name,string $clientId, string $apiBaseUrl = null)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException($email . ' is not a valid email address');
        }
        $this->email = $email;
        $this->name = $name;
        $this->clientId = $clientId;
        if($apiBaseUrl !== null) {
            $this->apiBaseUrl = $apiBaseUrl;
        }
    }

    public function enableLog($log_file_path = null)
    {
        $this->isLog = true;
        if ($log_file_path !== null) {
            $this->logFile = $log_file_path;
        }
    }

    /**
     * This function will handle single API request
     * If postData is not null, consider this as a POST request. otherwise, its a GET request
     */
    private function sendRequest(string $requestPath, array $params = [], array $postData = []): object
    {
        $ch = curl_init();
        if (!empty($params)) {
            $requestPath .= '?' . http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . $requestPath);
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->log($this->apiBaseUrl . $requestPath);
        if (!empty($postData)) {
            $this->log('Post Data : ' . json_encode($postData));
        }
        $result = curl_exec($ch);
        $this->log('Response : ' . $result);

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
     * This function will handle setup CURL for multipal API calls simultaneously
     * If postData is not null, consider this as a post request. otherwise, its a get request
     */
    private function setMultiRequest(string $requestPath, array $params = [], array $postData = []): void
    {
        $ch = curl_init();
        if (!empty($params)) {
            $requestPath .= '?' . http_build_query($params);
        }
        curl_setopt($ch, CURLOPT_URL, $this->apiBaseUrl . $requestPath);
        if (!empty($postData)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->log($this->apiBaseUrl. $requestPath);
        if (!empty($postData)) {
            $this->log('Post Data : ' . json_encode($postData));
        }
        $this->ch[] = $ch;
    }

    protected function executeMultiRequest(): array
    {
        $retVal = [];
        $mh = curl_multi_init();

        foreach ($this->ch as $ch) {
            curl_multi_add_handle($mh, $ch);
        }
        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
        foreach ($this->ch as $ch) {
            $result = curl_multi_getcontent($ch); // get the content
            curl_multi_remove_handle($mh, $ch);

            $this->log('Response : ' . $result);

            if (empty($result)) {
                throw new Exception('No response from the API');
            }
            $response = json_decode($result);
            if (!empty($response->error) && $response->error->message == 'Invalid SL Token') {
                throw new InvalidTokenException('Expired or Invalid SL Token');
            }
            $retVal[] = $response;
        }
        return $retVal;
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
     * Single API call
     */
    protected function callApi(string $requestPath, array $params = [], array $postData = []): object
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
     * Setup curl options for multipal API calls
     * If postData is not null, consider this as a POST request. otherwise, its a GET request
     */
    protected function setupApiCall(string $requestPath, array $params = [], array $postData = []): void
    {
        if ($this->token === null) {
            $this->refreshToken();
        }
        $params['sl_token'] = $this->token;

        $this->setMultiRequest($requestPath, $params, $postData);
    }

    /**
     * Write logfile
     */
    private function log(string $data): void
    {
        if ($this->isLog) {
            $now = DateTime::createFromFormat('U.u', microtime(true));
            file_put_contents($this->logFile, $now->format("Y-m-d H:i:s.v ") . $data . "\n", FILE_APPEND);
        }
    }

}
