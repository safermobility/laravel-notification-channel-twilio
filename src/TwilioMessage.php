<?php

namespace NotificationChannels\Twilio;

abstract class TwilioMessage
{
    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * @var null|string
     */
    public $statusCallback;

    /**
     * @var null|string
     */
    public $statusCallbackMethod;

    /**
     * Create a message object.
     * @return static
     */
    public static function create(string $content = ''): self
    {
        return new static($content);
    }

    /**
     * Create a new message instance.
     */
    public function __construct(string $content = '')
    {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @return $this
     */
    public function content(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     *
     * @return $this
     */
    public function from(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get the from address.
     */
    public function getFrom(): ?string
    {
        return $this->from;
    }

    /**
     * Set the status callback.
     *
     * @return $this
     */
    public function statusCallback(string $statusCallback): self
    {
        $this->statusCallback = $statusCallback;

        return $this;
    }

    /**
     * Set the status callback request method.
     *
     * @return $this
     */
    public function statusCallbackMethod(string $statusCallbackMethod): self
    {
        $this->statusCallbackMethod = $statusCallbackMethod;

        return $this;
    }
}
