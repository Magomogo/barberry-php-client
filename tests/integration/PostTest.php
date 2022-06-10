<?php

namespace Barberry;

use PHPUnit\Framework\TestCase;

class PostTest extends TestCase
{
    private static $contentId;

    /**
     * @var \Barberry\Client
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client(getenv('BARBERRY'));
    }

    public function testCanTransmitADocumentToBarberry()
    {
        $meta = $this->client->post(file_get_contents(__DIR__ . '/data/image.jpg'), 'test-image.jpg');

        self::assertMatchesRegularExpression('/.+/', $meta['id']);
        self::assertSame('test-image.jpg', $meta['filename']);
        self::assertSame(49161, $meta['length']);
        self::assertSame('jpg', $meta['ext']);
        self::assertSame('image/jpeg', $meta['contentType']);

        self::$contentId = $meta['id'];
    }

    public function testUploadedFileIsOk()
    {
        self::assertMatchesRegularExpression('/.+/', self::$contentId, 'Content was uploaded');

        $guzzle = new \GuzzleHttp\Client();

        self::assertSame(
            file_get_contents(__DIR__ . '/data/image.jpg'),
            $guzzle->get('http://' . getenv('BARBERRY') . '/' . self::$contentId)->getBody() . ''
        );
    }
}
