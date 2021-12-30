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

    public function __construct(string $email, string $name, string $clientId, $enableLog = false, $log_file_path = null)
    {
        $this->supermetricsApi = new SupermetricsApi($email, $name, $clientId);
        $this->aggregator = new Aggregator();
        if ($enableLog) {
            $this->supermetricsApi->enableLog($log_file_path);
        }
    }

    /**
     * Send API request to the Supermetrics API and process the report
     * @param int $numPages Number of pages needs to process
     * @return Array summary data
     */
    public function getWeeklyMonthlySummary(int $numPages): array
    {
        $results = $this->supermetricsApi->fetchPosts($numPages);
        foreach ($results as $res) {
            if (!empty($res->data->posts)) {
                foreach ($res->data->posts as $post) {
                    $this->aggregator->setData($post);
                }
            }
        }

        return $this->aggregator->getSummaryStats();
    }
}
