<?php
include_once 'bootstrap.php';

use supermetrics\services\SupermetricsService;

$email = 'sampathperera@hotmail.com';
$name = 'Sampath';
$numPages = 10;

$supermetricsService = new SupermetricsService($email, $name, CLIENT_ID);

$summary = $supermetricsService->getWeeklyMonthlySummary($numPages);

echo json_encode($summary);