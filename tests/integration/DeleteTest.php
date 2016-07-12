<?php

class DeleteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Barberry\Client
     */
    private $client;

    public function setUp()
    {
        $this->client = new Barberry\Client(getenv('BARBERRY'));
    }

    public function testCanDeleteContent()
    {
        $id = self::uploadImage(__DIR__ . '/data/image.jpg');

        $guzzle = new \GuzzleHttp\Client();

        $this->assertEquals(
            200,
            $guzzle->get('http://' . getenv('BARBERRY') . '/' .  $id)->getStatusCode()
        );

        $this->client->delete($id);

        $this->assertEquals(
            404,
            $guzzle->get('http://' . getenv('BARBERRY') . '/' .  $id, array('exceptions' => false))->getStatusCode()
        );

    }

    public function testThrowsWhenContentCannotBeDeleted()
    {
        $this->setExpectedException('Barberry\\Exception');

        $this->client->delete('not-existing-id');
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
