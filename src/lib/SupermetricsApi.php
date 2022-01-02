<?php

namespace supermetrics\lib;

use \supermetrics\lib\SupermetricsClient;

/**
 * Supermetrics API handler
 * Create new methods for new API endpoints
 */
class SupermetricsApi extends SupermetricsClient
{

    public function __construct(string $email, string $name,string $clientId, string $apiBaseUrl = null)
    {
       parent::__construct($email, $name, $clientId, $apiBaseUrl);
    }

    /**
     * Fetch one page form the API
     * @param int pageNumber
     */
    public function fetchPost(int $pageNumber): object
    {
        return $this->callApi('assignment/posts', ['page' => $pageNumber]);
    }

    /**
     * Fetch number of pages from the API
     * @param int numberOfPages
     */
    public function fetchPosts(int $numberOfPages): array
    {
        for ($i = 1; $i <= $numberOfPages; $i++) {
             $this->setupApiCall('assignment/posts', ['page' => $i]);
        }
        return $this->executeMultiRequest();
    }
}
