<?php

declare(strict_types=1);

namespace NotificationChannels\Twilio\Tests\Integration;

use NotificationChannels\Twilio\TwilioProvider;
use PHPUnit\Framework\TestCase;

abstract class BaseIntegrationTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [TwilioProvider::class];
    }
}
