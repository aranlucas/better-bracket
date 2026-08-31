<?php

declare(strict_types=1);

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class HealthTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testLivenessEndpoint(): void
    {
        $result = $this->get('/health/live');

        $result->assertStatus(200);
        $result->assertJSONExact(['status' => 'ok']);
    }

    public function testReadinessEndpoint(): void
    {
        $result = $this->get('/health/ready');

        $result->assertStatus(200);
        $result->assertJSONExact(['status' => 'ready']);
    }
}
