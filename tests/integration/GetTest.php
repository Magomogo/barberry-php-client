<?php

class GetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Barberry\Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Barberry\Client(getenv('BARBERRY'));
    }

    public function testNotExistingContentCausesException()
    {
        $this->setExpectedException('Barberry\\Exception');
        $this->client->get(getenv('BARBERRY') . '/not-existing');
    }

    public function testExistingContent()
    {
        $id = self::uploadImage(__DIR__ . '/data/image.jpg');

        $this->assertEquals(
            file_get_contents(__DIR__ . '/data/image.jpg'),
            $this->client->get($id)
        );
    }

    public function testUnavailableService()
    {
        $client = new Barberry\Client('127.0.0.1', 300, 2);
        $timeStart = microtime(true);
        try {
            $client->get('service-unavailable');
        } catch (Barberry\Exception $e) {
            $this->assertSame('Barberry service temporary unavailable', $e->getMessage());
            $timeFinish = microtime(true);
            $this->assertSame(20, (int)($timeFinish - $timeStart));
        }
    }

    private static function uploadImage($filePath)
    {
        $guzzle = new \GuzzleHttp\Client();
        $response = $guzzle->post('http://' . getenv('BARBERRY') . '/', array(
            'body' => array(
                'field_name'     => 'file',
                'file_filed' => fopen($filePath, 'r')
            )
        ));

        $metaInfo = $response->json();

        return $metaInfo['id'];
    }
}
