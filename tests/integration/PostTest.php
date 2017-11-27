<?php
namespace Barberry;

function sleep($seconds)
{
    return;
}

class PostTest extends \PHPUnit_Framework_TestCase
{
    private static $contentId;

    /**
     * @var \Barberry\Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Client(getenv('BARBERRY'));
    }

    public function testCanTransmitADocumentToBarberry()
    {
        $meta = $this->client->post(file_get_contents(__DIR__ . '/data/image.jpg'), 'test-image.jpg');

        $this->assertRegExp('/.+/', $meta['id']);
        $this->assertSame('test-image.jpg', $meta['filename']);
        $this->assertSame(49161, $meta['length']);
        $this->assertSame('jpg', $meta['ext']);
        $this->assertSame('image/jpeg', $meta['contentType']);

        self::$contentId = $meta['id'];
    }

    public function testUploadedFileIsOk()
    {
        $this->assertRegExp('/.+/', self::$contentId, 'Content was uploaded');

        $gizzle = new \GuzzleHttp\Client();

        $this->assertSame(
            file_get_contents(__DIR__ . '/data/image.jpg'),
            $gizzle->get('http://' . getenv('BARBERRY') . '/' . self::$contentId)->getBody() . ''
        );
    }
}
