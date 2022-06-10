<?php

namespace Barberry;

function sleep()
{
    return;
}

use PHPUnit\Framework\TestCase;use function foo\func;

class GetTest extends TestCase
{
    /**
     * @var \Barberry\Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client(getenv('BARBERRY'));
    }

    public function testNotExistingContentCausesException(): void
    {
        $this->expectException(Exception::class);

        $this->client->get(getenv('BARBERRY') . '/not-existing');
    }

    public function testExistingContent(): void
    {
        $id = self::uploadImage(__DIR__ . '/data/image.jpg');

        self::assertEquals(
            file_get_contents(__DIR__ . '/data/image.jpg'),
            $this->client->get($id)
        );
    }

    public function testUnavailableService(): void
    {
        $client = new Client('192.0.0.1', 300);
        try {
            $client->get('service-unavailable');
        } catch (Exception $e) {
            $this->assertSame('Barberry service temporary unavailable', $e->getMessage());
        }
    }

    private static function uploadImage($filePath)
    {
        $guzzle = new \GuzzleHttp\Client();
        $response = $guzzle->post('http://' . getenv('BARBERRY') . '/', [
            'multipart' => [
                [
                    'name'     => 'file',
                    'contents' => file_get_contents($filePath),
                    'filename' => basename($filePath)
                ]
            ]
        ]);

        return json_decode($response->getBody())->id;
    }
}
