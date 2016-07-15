<?php

namespace Pilulka\Cache;

use Closure;
use Predis\Client;

class RedisCache implements Repository
{
    /** @var  Client */
    private $client;

    /**
     * RedisCache constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }


    public function has($key)
    {
        return $this->client->exists($key);
    }

    public function get($key, $default = null)
    {
        return $this->has($key)
            ? $this->getValue($this->client->get($key))
            : $default;
    }

    public function put($key, $value, $ttl)
    {
        $this->client->set($key, $this->createValue($value));
        $this->client->expire($key, $ttl);
    }

    public function pull($key, $default = null)
    {
        if ($this->has($key)) {
            $value = $this->get($key);
            $this->forget($key);
            return $value;
        }
        return $default;
    }

    public function add($key, $value, $ttl)
    {
        if (!$this->client->exists($key)) {
            $this->put($key, $value, $ttl);
        }
    }

    public function forever($key, $value)
    {
        $this->put($key, $value, PHP_INT_MAX);
    }

    public function remember($key, $ttl, Closure $callback)
    {
        if (!$this->has($key)) {
            $this->put($key, call_user_func($callback), $ttl);
        }
        return $this->get($key);
    }

    public function rememberForever($key, Closure $callback)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        $this->put($key, call_user_func($callback), PHP_INT_MAX);
    }

    public function forget($key)
    {
        $this->client->expire($key, -1);
    }

    private function createValue($value)
    {
        return json_encode($value);
    }

    private function getValue($value)
    {
        return json_decode($value, true);
    }

}