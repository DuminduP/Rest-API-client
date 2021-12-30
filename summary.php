<?php
include_once 'bootstrap.php';

use supermetrics\services\SupermetricsService;

$email = 'SampathPerera@hotmail.com';
$name = 'Sampath';
$numPages = 10;

$input = readline('Enter your email: (Default: SampathPerera@hotmail.com) ');
$email = empty($input) ? $email : $input;
$input = readline('Enter your name: (Default: Sampath) ');
$name = empty($input) ? $name : $input;
$input = (int) readline('Number of pages fetch form the API: (Default: 10) ');
$numPages = empty($input) ? $numPages : $input;

$supermetricsService = new SupermetricsService($email, $name, CLIENT_ID, ENABLE_LOG, LOG_FILE_PATH);

$summary = $supermetricsService->getWeeklyMonthlySummary($numPages);

echo json_encode($summary);
