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
            ]
        ]);

        $result = file_get_contents('http://' . $this->serviceLocation, false, $context);

        if ($result === false) {
            throw new Exception('File upload fails.');
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

        $result = file_get_contents('http://' . $this->serviceLocation . '/' . $id, false, $context);

        return json_decode($result, true);
    }
}
