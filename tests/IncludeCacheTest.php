<?php

class IncludeCacheTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $this->cache()->add('foo', 'bar', 5);
        $this->assertEquals('bar', $this->cache()->get('foo'));
    }

    private function cache()
    {
        return new \Pilulka\Cache\IncludeCache(
            __DIR__ . "/temp/include_cache.php"
        );
    }

}