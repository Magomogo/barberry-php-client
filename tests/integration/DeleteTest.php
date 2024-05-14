<?php

namespace Barberry\IntegrationTests;

use GuzzleHttp;
use PHPUnit\Framework\TestCase;
use Barberry;

class DeleteTest extends TestCase
{
    /**
     * @var Barberry\Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Barberry\Client(getenv('BARBERRY') . '/');
    }

    public function testCanDeleteContent(): void
    {
        $id = self::uploadImage(__DIR__ . '/data/image.jpg');

        $guzzle = new GuzzleHttp\Client([
            'base_uri' => getenv('BARBERRY')
        ]);

        $this->assertEquals(
            200,
            $guzzle->get('/' . $id)->getStatusCode()
        );

        $this->client->delete($id);

        $this->assertEquals(
            404,
            $guzzle->get('/' .  $id, ['http_errors' => false])->getStatusCode()
        );
    }

    public function testThrowsWhenContentCannotBeDeleted(): void
    {
        $this->expectException(Barberry\Exception::class);

        $this->client->delete('not-existing-id');
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

        return json_decode($response->getBody(), false)->id;
    }
}
