<?php

namespace Barberry;

class Client
{
    private $serviceLocation;
    private $resource;

    public function __construct($serviceLocation)
    {
        $this->serviceLocation = $serviceLocation;
        $this->resource = curl_init();
        curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, 1);
    }

    public function __destruct()
    {
        curl_close($this->resource);
    }

    public function get($idWithCommand)
    {
        curl_setopt($this->resource, CURLOPT_URL, 'http://' . $this->serviceLocation . '/' . $idWithCommand);
        $content = curl_exec($this->resource);

        $responseHttpCode = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);

        if ($responseHttpCode !== 200) {
            throw new Exception('HTTP error', $responseHttpCode);
        }

        return $content;
    }

    public function post($content, $filename)
    {
        $multipartBoundary = '--------------------------' . microtime(true);
        $postBody = <<<MULTIPART_FORM_DATA
--$multipartBoundary\r
Content-Disposition: "form-data"; name="file"; filename="$filename"\r
\r
$content\r
--$multipartBoundary--\r

MULTIPART_FORM_DATA;

        curl_setopt_array(
            $this->resource,
            array(
                CURLOPT_URL => 'http://' . $this->serviceLocation,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postBody,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: multipart/form-data; boundary=' . $multipartBoundary,
                    'Content-Length: ' . strlen($postBody)
                )
            )
        );

        $result = curl_exec($this->resource);
        $responseHttpCode = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);

        if ($responseHttpCode !== 201) {
            throw new Exception($filename . ': File upload failure. HTTP #' . $responseHttpCode . ' ' . $result);
        }

        return json_decode($result, true);
    }

    public function delete($id)
    {
        curl_setopt_array(
            $this->resource,
            array(
                CURLOPT_URL => 'http://' . $this->serviceLocation . '/' . $id,
                CURLOPT_CUSTOMREQUEST => 'DELETE'
            )
        );
        $result = curl_exec($this->resource);
        $responseHttpCode = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);

        if ($responseHttpCode !== 200) {
            throw new Exception('Can not delete content ' . $id);
        }

        return json_decode($result, true);
    }
}
