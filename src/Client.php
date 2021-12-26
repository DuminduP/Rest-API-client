<?php

namespace supermetrics;

use Exception;

class Client
{

    /**
     * Register token url
     * @var string
     */
    private const TOKAN_URL = 'https://api.supermetrics.com/assignment/register';
    /**
     * Client ID
     * @var string
     */
    private const CLIENT_ID = 'ju16a6m81mhid5ue1z3v2g0uh';
    /**
     * enable/disable debug mode
     * @var bool
     */
    private const DEBUGING = false;
    /**
     * Your email address
     * @var string
     */
    private $email;
    /**
     * Your name
     * @var string
     */
    private $name;
    /**
     * Token registered with the API
     * @var string
     */
    private $token;

    public function __construct(string $email, string $name)
    {
        $this->email = $email;
        $this->name = $name;
        try {
            $this->token = $this->registerToken();
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Get token string to be used in the subsequent querys.
     */
    private function registerToken(): string
    {
        $data['client_id'] = self::CLIENT_ID;
        $data['email'] = $this->email;
        $data['name'] = $this->name;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::TOKAN_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if (self::DEBUGING) {
            echo 'Response form the API ' . self::TOKAN_URL . "\n";
            var_dump($result);
        }

        if (empty($result)) {
            throw new Exception('No response from the API');
        }
        $response = json_decode($result);

        if (empty($response->data->sl_token)) {
            throw new Exception('No token received from the API');
        }
        return $response->data->sl_token;
    }
}
