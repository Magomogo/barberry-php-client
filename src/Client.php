<?php

namespace Barberry;

use GuzzleHttp;
use Psr\Http\Message\StreamInterface;

class Client
{
    public const RETRIES = 1;
    public const DELAY_IN_SEC = 1;

    /**
     * @var GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * @var int
     */
    private $connectionTimeout;

    /**
     * @param string $serviceLocation
     * @param int $connectionTimeout
     */
    public function __construct(string $serviceLocation, int $connectionTimeout = 30)
    {
        $handlerStack = GuzzleHttp\HandlerStack::create();
        $retryMiddleware = GuzzleMiddleware::retryRequest(self::RETRIES, self::DELAY_IN_SEC);
        $handlerStack->push($retryMiddleware);

        $this->httpClient = new GuzzleHttp\Client([
            'base_uri' => $serviceLocation,
            'handler' => $handlerStack
        ]);
        $this->connectionTimeout = $connectionTimeout;
    }

    /**
     * @param string $idWithCommand
     * @return string Binary data
     * @throws Exception
     */
    public function get(string $idWithCommand): string
    {
        return $this->handleExceptions(function () use ($idWithCommand) {
            $response = $this->httpClient->get($idWithCommand, ['timeout' => $this->connectionTimeout]);

            if ($response->getStatusCode() === 200) {
                return (string) $response->getBody();
            }

            throw new Exception(
                'Unexpected response from barberry. HTTP #' . $response->getStatusCode() . ' ' . $response->getBody()
            );
        });
    }

    /**
     * @param StreamInterface $stream
     * @param string $filename
     * @return \stdClass
     * @throws Exception
     */
    public function post(StreamInterface $stream, string $filename): \stdClass
    {
        return $this->handleExceptions(function () use ($stream, $filename) {
            $request = new GuzzleHttp\Psr7\Request(
                'POST',
                '/',
                [],
                new GuzzleHttp\Psr7\MultipartStream([
                    [
                        'name' => 'file',
                        'filename' => $filename,
                        'contents' => $stream
                    ]
                ])
            );
            $response = $this->httpClient->send($request, ['timeout' => $this->connectionTimeout]);

            if ($response->getStatusCode() === 201) {
                return json_decode($response->getBody(), false);
            }

            throw new Exception(
                'Unexpected response from barberry. HTTP #' . $response->getStatusCode() . ' ' . $response->getBody()
            );
        });
    }

    /**
     * @param string $id
     * @return mixed
     * @throws Exception
     */
    public function delete(string $id)
    {
        return $this->handleExceptions(function () use ($id) {
            $response = $this->httpClient->delete($id, ['timeout' => $this->connectionTimeout]);

            if ($response->getStatusCode() === 200) {
                return json_decode($response->getBody(), false);
            }

            throw new Exception(
                'Unexpected response from barberry. HTTP #' . $response->getStatusCode() . ' ' . $response->getBody()
            );
        });
    }

    /**
     * @param callable $targetFunction
     * @return mixed
     * @throws Exception
     */
    private function handleExceptions(callable $targetFunction)
    {
        try {
            return $targetFunction();
        } catch (Exception $e) {
            throw $e;
        } catch (GuzzleHttp\Exception\ConnectException $e) {
            throw new Exception('Barberry service temporary unavailable');
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }
}
