<?php

namespace Barberry\IntegrationTests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Barberry;

class GetTest extends TestCase
{
    /**
     * @var \Barberry\Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Barberry\Client(getenv('BARBERRY'));
    }

    public function testNotExistingContentCausesException(): void
    {
        $this->expectException(Barberry\Exception::class);

        $this->client->get('not-existing');
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
        $client = new Barberry\Client('192.0.0.1', 10);
        try {
            $client->get('service-unavailable');
        } catch (Barberry\Exception $e) {
            $this->assertSame('Barberry service temporary unavailable', $e->getMessage());
        }
    }

    private static function uploadImage($filePath)
    {
        $guzzle = new GuzzleHttp\Client([
            'base_uri' => getenv('BARBERRY')
        ]);
        $response = $guzzle->post('/', [
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
