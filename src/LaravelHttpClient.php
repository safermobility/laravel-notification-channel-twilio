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
                }
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

            if ($this->hasFile($data)) {
                $this->buildMultipartBody($request, $data);
            }
        }

        $request->withHeaders($headers);

        try {
            $response = (match(strtoupper($method)) {
                'GET' => $request->get($url, $data),
                'HEAD' => $request->head($url, $data),
                'POST' => $request->post($url, $data),
                'PATCH' => $request->patch($url, $data),
                'PUT' => $request->put($url, $data),
                'DELETE' => $request->delete($url, $data),
            })->throw();

            return new Response($response->status(), $response->body(), $response->headers());
        } catch (Exception $exception) {
            throw new HttpException('Unable to complete the HTTP request', 0, $exception);
        }
    }

    private function hasFile(array $data): bool {
        foreach ($data as $value) {
            if ($value instanceof File) {
                return true;
            }
        }

        return false;
    }

    private function buildMultipartBody(PendingRequest $request, array $data) {
        foreach ($data as $key => $value) {
            if ($value instanceof File) {
                $type = $value->getContentType();
                $request->attach($key, $value->getContents(), $value->getFileName(), $type ? ['Content-Type' => $type] : []);
            } else {
                $request->attach($key, $value);
            }
        }
    }
}
