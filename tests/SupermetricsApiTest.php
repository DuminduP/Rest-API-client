<?php

use PHPUnit\Framework\TestCase;
use \supermetrics\lib\SupermetricsApi;

class SupermetricsApiTest extends TestCase
{
    public function testCanBeCreatedFromValidInput(): void
    {
        $this->assertInstanceOf(
            SupermetricsApi::class,
            new SupermetricsApi('test@example.com', 'Test Name', 123)
        );
    }

    public function testCannotBeCreatedFromInvalidInput(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SupermetricsApi('userexample.com', 'Test Name', 123);
    }

    public function testInvalidFetchPostRequest(): void
    {
        $sma = new SupermetricsApi('test@example.com', 'Test Name', 'xxx');
        $this->expectException(Exception::class);
        $data = $sma->fetchPost(1);
    }

    public function testValidFetchPostRequest(): void
    {
        $sma = new SupermetricsApi('test@example.com', 'Test Name', 'ju16a6m81mhid5ue1z3v2g0uh');
        $rsp = $sma->fetchPost(1);
        $this->assertIsObject($rsp);
        $this->assertObjectHasAttribute('data', $rsp);
        $this->assertIsArray($rsp->data->posts);
        $this->assertCount(100, $rsp->data->posts);
    }
}
