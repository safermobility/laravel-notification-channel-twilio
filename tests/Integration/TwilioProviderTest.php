<?php

declare(strict_types=1);

namespace NotificationChannels\Twilio\Tests\Integration;

use NotificationChannels\Twilio\Exceptions\InvalidConfigException;
use NotificationChannels\Twilio\TwilioChannel;

class TwilioProviderTest extends BaseIntegrationTest
{
    public function testThatApplicationCannotCreateChannelWithoutConfig()
    {
        $this->expectException(InvalidConfigException::class);

        $this->app->get(TwilioChannel::class);
    }

    public function testThatApplicationCreatesChannelWithConfig()
    {
        $this->app['config']->set('twilio-notification-channel.account_sid', 'AC1234');
        $this->app['config']->set('twilio-notification-channel.auth_token', 'SECRET');
        $this->app['config']->set('twilio-notification-channel.sid', 'SK1234');

        $this->assertInstanceOf(TwilioChannel::class, $this->app->get(TwilioChannel::class));
    }
}
