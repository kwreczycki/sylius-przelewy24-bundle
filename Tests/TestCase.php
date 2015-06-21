<?php

namespace KW\Bundle\SyliusPrzelewy24Bundle\Tests;

class TestCase extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

    protected function assertArrayHasKeys($keys, $stack, $message = '')
    {
        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $stack, $message);
        }
    }
}

