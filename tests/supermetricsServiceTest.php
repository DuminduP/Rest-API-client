<?php

use PHPUnit\Framework\TestCase;
use \supermetrics\services\SupermetricsService;

class SupermetricsServiceTest extends TestCase
{
    public function testCanBeCreatedFromValidInput(): void
    {
        $this->assertInstanceOf(
            SupermetricsService::class,
            new SupermetricsService('test@example.com', 'Test Name', 123)
        );
    }

    public function testCannotBeCreatedFromInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SupermetricsService('userexample.com', 'Test Name', 123);
    }

    public function testInvalidFetchPostRequest(): void
    {
        $sma = new SupermetricsService('test@example.com', 'Test Name', 'xxx');
        $this->expectException(Exception::class);
        $data = $sma->getWeeklyMonthlySummary(1);
    }

    public function testValidGetWeeklyMonthlySummary(): void
    {
        $sma = new SupermetricsService('test@example.com', 'Test Name', 'ju16a6m81mhid5ue1z3v2g0uh');
        $rsp = $sma->getWeeklyMonthlySummary(2);
        $this->assertIsArray($rsp);
        $this->assertCount(2, $rsp);
        $this->assertArrayHasKey('monthly_stats', $rsp[0]);
        $this->assertArrayHasKey('weekly_stats', $rsp[1]);
        $this->assertIsArray($rsp[0]['monthly_stats']);
        $this->assertIsArray($rsp[1]['weekly_stats']);
        $this->assertArrayHasKey('month', $rsp[0]['monthly_stats'][0]);
        $this->assertArrayHasKey('average_character_length_of_post', $rsp[0]['monthly_stats'][0]);
        $this->assertArrayHasKey('longest_post', $rsp[0]['monthly_stats'][0]);
        $this->assertArrayHasKey('average_number_of_posts_per_user', $rsp[0]['monthly_stats'][0]);
        $this->assertArrayHasKey('week', $rsp[1]['weekly_stats'][0]);
        $this->assertArrayHasKey('total_posts', $rsp[1]['weekly_stats'][0]);
        $cmonth = date('Y-m');
        $cweek = date('Y-W');
        $this->assertEquals($cmonth, $rsp[0]['monthly_stats'][0]['month']);
        $this->assertEquals($cweek, $rsp[1]['weekly_stats'][0]['week']);
        $this->assertIsFloat($rsp[0]['monthly_stats'][0]['average_character_length_of_post']);
        $this->assertIsFloat($rsp[0]['monthly_stats'][0]['average_number_of_posts_per_user']);
        $this->assertIsInt($rsp[1]['weekly_stats'][0]['total_posts']);
    }
}
