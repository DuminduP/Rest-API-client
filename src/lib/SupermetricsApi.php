<?php

namespace supermetrics\lib;

use \supermetrics\lib\SupermetricsClient;

/**
 * Supermetrics API handler
 */
class SupermetricsApi extends SupermetricsClient
{

    public function __construct(string $email, string $name, $clientId)
    {
       parent::__construct($email, $name, $clientId);
    }

    /**
     * Fetch posts form the API
     * @param int pageNumber
     */
    public function fetchPost(int $pageNumber): object
    {
        return $this->callAPI('assignment/posts', ['page' => $pageNumber]);
    }
}
