<?php

namespace NotificationChannels\Twilio;

class TwilioMmsMessage extends TwilioSmsMessage
{
    /**
     * @var string|null
     */
    public $mediaUrl;

    /**
     * Set the message media url.
     *
     * @return $this
     */
    public function mediaUrl(string $url): self
    {
        $this->mediaUrl = $url;

        return $this;
    }
}
