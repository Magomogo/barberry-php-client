#!/usr/bin/env php
<?php
require __DIR__ . '/../vendor/autoload.php';

set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting()) {
        throw new ErrorException($message, $severity, $severity, $file, $line);
    }
});

$client = new \Barberry\Client('example.com');

/**
 * @param \Barberry\Client $client
 * @return bool
 */
function testWillThrowExceptionWhenContentIsNotPresent($client)
{
    try {
        $client->get('not-existing-uri');
    } catch (\Barberry\Exception $e) {
        return true;
    }

    return false;
}


assert(testWillThrowExceptionWhenContentIsNotPresent($client));

echo "OK!\n";
exit(0);
