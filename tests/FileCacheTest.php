<?php

class FileCacheTest extends PHPUnit_Framework_TestCase
{

    public function testAdd()
    {
        $this->cache()->add('foo', 'bar', 5);
        $this->assertEquals('bar', $this->cache()->get('foo'));
    }

    private function cache()
    {
        return new \Pilulka\Cache\FileCache(
            __DIR__ . "/temp/file_cache.txt"
        );
    }

}
