<?php

namespace NotificationChannels\Twilio;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Twilio\AuthStrategy\AuthStrategy;
use Twilio\Exceptions\HttpException;
use Twilio\Http\Client;
use Twilio\Http\File;
use Twilio\Http\Response;

/**
 * An HTTP Client for Twilio that uses Laravel's built-in HTTP Client features.
 *
 * Based on https://github.com/twilio/twilio-php/blob/8.10.1/src/Twilio/Http/GuzzleClient.php
 */
class LaravelHttpClient implements Client
{
    public function __construct(
        public readonly ?int $retryCount = 3,
        public readonly ?int $retryDelay = 1000,
    ) { }

    public function request(
        string $method, string $url,
        array $params = [], array $data = [], array $headers = [],
        ?string $user = null, ?string $password = null,
        ?int $timeout = null, ?AuthStrategy $authStrategy = null
    ): Response
    {
        $request = Http::withoutRedirecting();

        if ($this->retryCount && $this->retryDelay) {
            $request->retry(
                $this->retryCount,
                function (int $attempt, Exception $exception) {
                    return $this->retryDelay * $attempt;
                },
                function (Exception $exception, PendingRequest $request) {
                    // Retry if we get a connection problem, a 500 error (server problem), or a 429 error (rate limiting)
                    // TODO: Is there any other situation in which we should/shouldn't retry?
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    if ($exception instanceof RequestException) {
                        return $exception->response->serverError() || $exception->response->tooManyRequests();
                    }

                    return false;
                },
                throw: false,
            );
        }

        if ($user && $password) {
            $request->withBasicAuth($user, $password);
        } elseif ($authStrategy) {
            $request->withHeader('Authorization', $authStrategy->getAuthString());
        }

        if ($timeout !== null) {
            $request->timeout($timeout);
        }

        if ($params) {
            $request->withQueryParameters($params);
        }

        if ($method === 'POST' || $method === 'PUT') {
            $request->asForm();

            $data = $this->attachAndFilterFiles($request, $data);
        }

        // Filter out Twilio's 'Content-Type' header, since the HTTP Client needs to set it and we don't want it duplicated
        $request->withHeaders(collect($headers)->filter(fn ($value, $key) => $key !== 'Content-Type')->all());

        try {
            $response = (match(strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'HEAD' => $request->head($url, $data),
                'POST' => $request->post($url, $data),
                'PATCH' => $request->patch($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
            });

            // If the API call was successful (2xx), was a client error (4xx), or was a server error (5xx), let the
            // Twilio SDK handle it. If it's anything else, we have no idea what is going on so throw an exception.
            if ($response->successful() || $response->failed()) {
                return new Response($response->status(), $response->body(), $response->headers());
            } else {
                throw new HttpException('Unexpected result from HTTP request', $response->status());
            }
        } catch (\Exception $e) {
            throw new HttpException('Unable to complete the HTTP request', 0, $e);
        }
    }

    /**
     * If there are any files in the input data, attach them.
     *
     * @return array All non-File data
     */
    private function attachAndFilterFiles(PendingRequest $request, array $data) : array {
        [$attachments, $other] = collect($data)->partition(fn ($value) => $value instanceof File);
        foreach ($attachments as $key => $value) {
            $type = $value->getContentType();
            $request->attach($key, $value->getContents(), $value->getFileName(), $type ? ['Content-Type' => $type] : []);
        }
        return $other->all();
    }
}
