<?php

class GetTest extends PHPUnit_Framework_TestCase
{
    public function testNotExistingContent()
    {
        var_dump(getenv('BARBERRY'));
    }
}
