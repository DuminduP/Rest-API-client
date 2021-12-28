<?php

namespace supermetrics\lib;

use DateTime;

/**
 * This class will do all data processing and aggrigation functions
 */
class Aggregator
{

    /**
     * Keep monthly data
     * @var array
     * */
    private $monthly;
    /**
     * Keep weekly data
     * @var array
     * */
    private $weekly;

    public function __construct()
    {
        $this->monthly = [];
        $this->weekly = [];
    }

    /**
     * Populate monthly breakdown array
     */
    private function setMonthlyData(string $month, object $data): void
    {
        if (!isset($this->monthly[$month])) {
            $this->monthly[$month]['total_chars'] = 0;
            $this->monthly[$month]['post_count'] = 0;
            $this->monthly[$month]['longest_post'] = $data;
        }
        $msg_len = strlen($data->message);
        $this->monthly[$month]['total_chars'] += $msg_len;
        $this->monthly[$month]['post_count'] += 1;
        $this->monthly[$month]['user_ids'][$data->from_id] = $data->from_id;
        if ($msg_len > strlen($this->monthly[$month]['longest_post']->message)) {
            $this->monthly[$month]['longest_post'] = $data;
        }
    }

    /**
     * Populate weekly breakdown array
     */
    private function setWeeklyData(string $week): void
    {
        if (!isset($this->weekly[$week])) {
            $this->weekly[$week]['total_posts'] = 0;
        }
        $this->weekly[$week]['total_posts'] += 1;
    }

    /**
     * Populate weekly stats
     */
    private function getWeeklyStats(): array
    {
        $out = [];
        foreach ($this->weekly as $week => $val) {
            $out['weelly_stats'][] = [
                'week' => $week,
                'total_posts' => $val['total_posts'],
            ];
        }
        return $out;
    }

    /**
     * Populate monthly stats
     */
    private function getMonthlyStats(): array
    {
        $out = [];
        foreach ($this->monthly as $month => $val) {
            $out['monthly_stats'][] = [
                'month' => $month,
                'average_character_length_of_post' => $val['total_chars'] / $val['post_count'],
                'longest_post' => $val['longest_post'],
                'average_number_of_posts_per_user' => $val['post_count'] / count($val['user_ids']),
            ];
        }
        return $out;
    }

    /**
     * Set filtered data to weelky and monthly arrays
     * @var object $data
     */
    public function setData(object $data): void
    {
        $postDate = new DateTime($data->created_time);
        $month = $postDate->format('Y-m');
        $week = $postDate->format('Y-W');
        $this->setMonthlyData($month, $data);
        $this->setWeeklyData($week);
    }

    /**
     * Calculate the weekly and monthly summary
     */
    public function getSummaryStats(): array
    {
        return [$this->getMonthlyStats(), $this->getWeeklyStats()];
    }

}
