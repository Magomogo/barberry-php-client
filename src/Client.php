<?php

namespace Barberry;

class Client
{
    const RETRIES = 12;
    const DELAY_IN_SEC = 10;

    private $serviceLocation;
    private $resource;
    private $retries;

    private $networkErrors = [
        CURLE_COULDNT_CONNECT,
        CURLE_OPERATION_TIMEOUTED,
        CURLE_RECV_ERROR,
        CURLE_SEND_ERROR
    ];

    public function __construct($serviceLocation, $timeoutMs = 300, $retries = null)
    {
        $this->serviceLocation = $serviceLocation;
        $this->retries = $retries;
        $this->resource = curl_init();
        curl_setopt($this->resource, CURLOPT_TIMEOUT_MS, $timeoutMs);
        curl_setopt($this->resource, CURLOPT_RETURNTRANSFER, 1);
    }

    public function __destruct()
    {
        curl_close($this->resource);
    }

    public function get($idWithCommand)
    {
        curl_setopt($this->resource, CURLOPT_URL, 'http://' . $this->serviceLocation . '/' . $idWithCommand);
        $content = $this->exec($this->resource);

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

        $result = $this->exec($this->resource);
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

        $result = $this->exec($this->resource);
        $responseHttpCode = curl_getinfo($this->resource, CURLINFO_HTTP_CODE);

        if ($responseHttpCode !== 200) {
            throw new Exception('Can not delete content ' . $id);
        }

        return json_decode($result, true);
    }

    /**
     * @param $resource
     * @return mixed
     * @throws Exception
     */
    private function exec($resource)
    {
        $numberOfAttempts = $this->retries === null ? self::RETRIES : $this->retries;

        while ($numberOfAttempts) {
            $result = curl_exec($resource);
            if ($result !== false) {
                return $result;
            }
            if (!in_array(curl_errno($resource), $this->networkErrors)) {
                throw new Exception(curl_error($resource));
            }
            $numberOfAttempts--;
            sleep(self::DELAY_IN_SEC);
        }
        throw new Exception('Barberry service temporary unavailable', 503);
    }
}
