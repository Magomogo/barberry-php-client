<?php

namespace Barberry\IntegrationTests;

use PHPUnit\Framework\TestCase;
use GuzzleHttp;
use Barberry;

class PostTest extends TestCase
{
    private static $contentId;

    /**
     * @var \Barberry\Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Barberry\Client(getenv('BARBERRY'));
    }

    public function testCanTransmitADocumentToBarberry()
    {
        $fileStream = GuzzleHttp\Psr7\Utils::streamFor(
            GuzzleHttp\Psr7\Utils::tryFopen(__DIR__ . '/data/image.jpg', 'rb')
        );
        $meta = $this->client->post($fileStream, 'test-image.jpg');

        self::assertMatchesRegularExpression('/.+/', $meta->id);
        self::assertSame('test-image.jpg', $meta->filename);
        self::assertSame(49161, $meta->length);
        self::assertSame('jpg', $meta->ext);
        self::assertSame('image/jpeg', $meta->contentType);

        self::$contentId = $meta->id;
    }

    public function testUploadedFileIsOk()
    {
        self::assertMatchesRegularExpression('/.+/', self::$contentId, 'Content was uploaded');

        $guzzle = new GuzzleHttp\Client([
            'base_uri' => getenv('BARBERRY')
        ]);

        self::assertSame(
            file_get_contents(__DIR__ . '/data/image.jpg'),
            $guzzle->get(self::$contentId)->getBody() . ''
        );
    }
}
