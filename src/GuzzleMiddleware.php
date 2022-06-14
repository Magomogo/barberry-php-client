<?php

namespace Barberry;

use GuzzleHttp;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class GuzzleMiddleware
{
    /**
     * @param int $numberOfRetries
     * @param int $delayInSec
     * @return callable
     */
    public static function retryRequest(int $numberOfRetries, int $delayInSec): callable
    {
        return GuzzleHttp\Middleware::retry(
            self::retryDecider($numberOfRetries),
            static function () use ($delayInSec) {
                return $delayInSec * 1000;
            }
        );
    }

    /**
     * @param int $numberOfRetries
     * @return callable
     */
    private static function retryDecider(int $numberOfRetries): callable
    {
        return function (
            $retries,
            RequestInterface $request,
            ResponseInterface $response = null,
            \Exception $exception = null
        ) use ($numberOfRetries) {
            if ($retries >= $numberOfRetries) {
                return false;
            }

            // Retry connection exceptions
            if ($exception instanceof GuzzleHttp\Exception\ConnectException) {
                return true;
            }

            // Retry on server errors
            if ($response && $response->getStatusCode() >= 500) {
                return true;
            }

            return false;
        };
    }
}
