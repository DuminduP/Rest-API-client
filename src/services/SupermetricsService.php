<?php
namespace supermetrics\services;

use supermetrics\lib\Aggregator;
use supermetrics\lib\SupermetricsApi;

/**
 * Call Supermetrics API, get data and do aggregations
 */
class SupermetricsService
{

    private $supermetricsApi;
    private $aggregator;

    public function __construct(string $email, string $name, string $clientId)
    {
        $this->supermetricsApi = new SupermetricsApi($email, $name, $clientId);
        $this->aggregator = new Aggregator();
        if (ENABLE_LOG) {
            $this->supermetricsApi->enableLog(LOG_FILE_PATH);
        }
    }

    /**
     * Send API request to the Supermetrics API and process the report
     * @param int $numPages Number of pages needs to process
     * @return Array summary data
     */
    public function getWeeklyMonthlySummary(int $numPages): array
    {
        for ($i = 1; $i <= $numPages; $i++) {
            $response = $this->supermetricsApi->fetchPost($i);

            if (!empty($response->data->posts)) {
                foreach ($response->data->posts as $post) {
                    $this->aggregator->setData($post);
                }
            }
        }

        return $this->aggregator->getSummaryStats();
    }
}
