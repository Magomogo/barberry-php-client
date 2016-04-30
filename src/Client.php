<?php

namespace Barberry;

class Client
{
    private $serviceLocation;

    public function __construct($serviceLocation)
    {
        $this->serviceLocation = $serviceLocation;
    }

    public function get($idWithCommand)
    {
        return file_get_contents('http://' . $this->serviceLocation . '/' . $idWithCommand);
    }

    public function post($content, $filename)
    {
        $multipartBoundary = '--------------------------' . microtime(true);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: multipart/form-data; boundary=' . $multipartBoundary,
                'content' => <<<MULTIPART_FORM_DATA
--$multipartBoundary\r
Content-Disposition: "form-data"; name="file"; filename="$filename"\r
\r
$content\r
--$multipartBoundary--\r

MULTIPART_FORM_DATA
                ,
                'ignore_errors' => '1'
            ]
        ]);

        $stream = fopen('http://' . $this->serviceLocation, 'r', false, $context);
        $meta = stream_get_meta_data($stream);
        $result = stream_get_contents($stream);
        fclose($stream);

        if ($meta['wrapper_data'][0] !== 'HTTP/1.1 201 Created') {
            throw new Exception($filename . ': File upload failure. ' . $meta['wrapper_data'][0] . ' ' . $result);
        }

        return json_decode($result, true);
    }

    public function delete($id)
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'DELETE',
            ]
        ]);

        $result = @file_get_contents('http://' . $this->serviceLocation . '/' . $id, false, $context);

        if (!$result) {
            throw new Exception('Can not delete content ' . $id);
        }

        return json_decode($result, true);
    }
}
