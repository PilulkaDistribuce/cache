<?php

class ArrayCacheTest extends PHPUnit_Framework_TestCase
{

    private $cache;

    public function testAdd()
    {
        $this->cache->add('foo', 'bar', 5);
        $this->assertEquals('bar', $this->cache->get('foo'));
    }

    public function setUp()
    {
        $this->cache = new \Pilulka\Cache\ArrayCache();
    }

}