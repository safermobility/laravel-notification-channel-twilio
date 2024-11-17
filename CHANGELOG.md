# Changelog

All notable changes to `laravel-notification-channels/twilio` will be documented in this file

## 4.0.0

- Bump `twilio/sdk` to 8.3
- Improved types and use constructor property promotion everywhere.
- Added Pint and fixed PHP syntax.
- Drop support for PHP < 8.2 **BREAKING CHANGE**
- Drop support for Laravel 7.x, 8.x, 9.x, and 10.x **BREAKING CHANGE**
- Enable overriding the Twilio message source [#142](https://github.com/laravel-notification-channels/twilio/pull/142)
- Add enabled config option (`TWILIO_ENABLED`) to disable the channel [#21](https://github.com/laravel-notification-channels/twilio/pull/121)

## 3.0.0

This is a major release with breaking changes. Please see the upgrading section in the readme for more info.

- Throw exceptions on send failure (with the ability to suppress certain error codes) **BREAKING CHANGE** [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Make service provider deferrable [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Drop support for Laravel 5.7 and lower **BREAKING CHANGE** [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Move config to a dedicated config file **BREAKING CHANGE** [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Update Twilio SDK to 6.x [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Add "Default To" local debugging support [#90](https://github.com/laravel-notification-channels/twilio/pull/90)
- Switch to Github Actions CI [#91](https://github.com/laravel-notification-channels/twilio/pull/91)

## 2.0.0

- initial 2.x release

## 1.0.0

- initial release
