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
     *
     * @return mixed
     * @throws Exception
     */
    public function send(mixed $notifiable, Notification $notification)
    {
        if (! $this->isEnabled()) {
            return;
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
                return;
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
                return;
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
     *
     * @param mixed $notifiable
     * @param Notification|null $notification
     * @param TwilioMessage $message
     *
     * @return mixed
     * @throws CouldNotSendNotification
     */
    protected function getTo($notifiable, $notification = null, $message = null)
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
     *
     * @return mixed|null
     * @throws CouldNotSendNotification
     */
    protected function canReceiveAlphanumericSender($notifiable)
    {
        return method_exists($notifiable, 'canReceiveAlphanumericSender') &&
            $notifiable->canReceiveAlphanumericSender();
    }
}
