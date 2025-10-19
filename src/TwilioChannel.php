<?php

namespace NotificationChannels\Twilio;

use Exception;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\Exceptions\CouldNotSendNotification;

class TwilioChannel
{
    public function __construct(
        protected Twilio $twilio,
        protected Dispatcher $events
    ) {}

    /**
     * Send the given notification.
     */
    public function send(mixed $notifiable, Notification $notification): mixed
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $message = $notification->toTwilio($notifiable);

            if (is_string($message)) {
                $message = new TwilioSmsMessage($message);
            }

            if (! $message instanceof TwilioMessage) {
                throw CouldNotSendNotification::invalidMessageObject($message);
            }

            $to = $this->getTo($notifiable, $notification, $message);
            $useSender = $this->canReceiveAlphanumericSender($notifiable);

            // Suppress notification if notifier address is found to be NULL
            if (!$to) {
                return null;
            }

            return $this->twilio->sendMessage($message, $to, $useSender);
        } catch (Exception $exception) {
            $event = new NotificationFailed(
                $notifiable,
                $notification,
                'twilio',
                ['message' => $exception->getMessage(), 'exception' => $exception]
            );

            $this->events->dispatch($event);

            if ($this->twilio->config->isIgnoredErrorCode($exception->getCode())) {
                return null;
            }

            throw $exception;
        }
    }

    /**
     * Check if twilio is enabled.
     */
    protected function isEnabled(): bool
    {
        return $this->twilio->config->enabled() ?? true;
    }

    /**
     * Get the address to send a notification to.
     */
    protected function getTo(mixed $notifiable, ?Notification $notification = null, ?TwilioMessage $message = null): mixed
    {
        if ($message->getTo()) {
            return $message->getTo();
        }
        if ($notifiable->routeNotificationFor(self::class, $notification)) {
            return $notifiable->routeNotificationFor(self::class, $notification);
        }
        if ($notifiable->routeNotificationFor('twilio', $notification)) {
            return $notifiable->routeNotificationFor('twilio', $notification);
        }
        if (isset($notifiable->phone_number)) {
            return $notifiable->phone_number;
        }

        return null;
    }

    /**
     * Get the alphanumeric sender.
     */
    protected function canReceiveAlphanumericSender($notifiable): mixed
    {
        return method_exists($notifiable, 'canReceiveAlphanumericSender') &&
            $notifiable->canReceiveAlphanumericSender();
    }
}
