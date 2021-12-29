<?php

use PHPUnit\Framework\TestCase;
use \supermetrics\lib\SupermetricsClient;

class SupermetricsClientTest extends TestCase
{
    public function testCanBeCreatedFromValidInput(): void
    {
        $this->assertInstanceOf(
            SupermetricsClient::class,
            new SupermetricsClient('test@example.com', 'Test Name', 123)
        );
    }

    public function testCannotBeCreatedFromInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SupermetricsClient('userexample.com', 'Test Name', 123);
    }

}
