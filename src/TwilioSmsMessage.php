<?php

namespace NotificationChannels\Twilio;

class TwilioSmsMessage extends TwilioMessage
{
    /**
     * @var null|string
     */
    public $alphaNumSender;

    /**
     * @var null|string
     */
    public $messagingServiceSid;

    /**
     * @var null|string
     */
    public $applicationSid;

    /**
     * @var null|bool
     */
    public $forceDelivery;

    /**
     * @var null|float
     */
    public $maxPrice;

    /**
     * @var null|bool
     */
    public $provideFeedback;

    /**
     * @var null|int
     */
    public $validityPeriod;

    /**
     * Get the from address of this message.
     */
    public function getFrom(): ?string
    {
        if ($this->from) {
            return $this->from;
        }

        if ($this->alphaNumSender !== null && $this->alphaNumSender !== '') {
            return $this->alphaNumSender;
        }

        return null;
    }

    /**
     * Set the messaging service SID.
     *
     * @return $this
     */
    public function messagingServiceSid(string $messagingServiceSid): self
    {
        $this->messagingServiceSid = $messagingServiceSid;

        return $this;
    }

    /**
     * Get the messaging service SID of this message.
     */
    public function getMessagingServiceSid(): ?string
    {
        return $this->messagingServiceSid;
    }

    /**
     * Set the alphanumeric sender.
     *
     * @return $this
     */
    public function sender(string $sender): self
    {
        $this->alphaNumSender = $sender;

        return $this;
    }

    /**
     * Set application SID for the message status callback.
     *
     * @return $this
     */
    public function applicationSid(string $applicationSid): self
    {
        $this->applicationSid = $applicationSid;

        return $this;
    }

    /**
     * Set force delivery (Deliver message without validation).
     *
     * @return $this
     */
    public function forceDelivery(bool $forceDelivery): self
    {
        $this->forceDelivery = $forceDelivery;

        return $this;
    }

    /**
     * Set the max price (in USD dollars).
     *
     * @return $this
     */
    public function maxPrice(float $maxPrice): self
    {
        $this->maxPrice = $maxPrice;

        return $this;
    }

    /**
     * Set the provide feedback option.
     *
     * @return $this
     */
    public function provideFeedback(bool $provideFeedback): self
    {
        $this->provideFeedback = $provideFeedback;

        return $this;
    }

    /**
     * Set the validity period (in seconds).
     *
     *
     * @return $this
     */
    public function validityPeriod(int $validityPeriodSeconds): self
    {
        $this->validityPeriod = $validityPeriodSeconds;

        return $this;
    }
}
