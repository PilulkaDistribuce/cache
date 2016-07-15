<?php

namespace Pilulka\Cache;

use Closure;

class ArrayCache implements Repository
{

    private $isChanged = false;
    protected $filePath;
    private $data = [];

    public function has($key)
    {
        return !is_null($this->getKey($key));
    }

    public function get($key, $default = null)
    {
        $value = $this->getKey($key);
        if (is_null($value) && !is_null($default)) {
            return $default;
        }
        return $value;
    }


    public function pull($key, $default = null)
    {
        $value = $this->getKey($key);
        if (!is_null($value)) {
            $this->unsetKey($key);
        }
        return $value;
    }

    public function put($key, $value, $ttl)
    {
        $this->setKey($key, $value, $ttl);
    }

    public function add($key, $value, $ttl)
    {
        if (!$this->has($key)) {
            $this->setKey($key, $value, $ttl);
        }
    }

    public function forever($key, $value)
    {
        $this->put($key, $value, PHP_INT_MAX);
    }

    public function remember($key, $ttl, Closure $callback)
    {
        if($this->has($key)) {
            return $this->get($key);
        }
        $this->setKey($key, $callback(), $ttl);
    }

    public function rememberForever($key, Closure $callback)
    {
        if($this->has($key)) {
            return $this->get($key);
        }
        $this->setKey($key, $callback(), PHP_INT_MAX);
    }

    public function forget($key)
    {
        $this->unsetKey($key);
    }

    private function getKey($key)
    {
        $value = (isset($this->data[$key])) ? $this->data[$key] : null;
        if (!is_null($value) && $value['ttl'] > time()) {
            return $value['data'];
        }
        return null;
    }

    private function setKey($key, $data, $ttl)
    {
        $ttl = $this->getTimeFromTtl($ttl);
        if (!isset($this->data[$key])
            || $this->data[$key]['data'] != $data
            || $this->data[$key]['ttl'] != $ttl
        ) {
            $this->data[$key] = [
                'data' => $data,
                'ttl' => $ttl,
            ];
            $this->isChanged = true;
        }
    }

    private function unsetKey($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
            $this->isChanged = true;
        }
    }

    private function getTimeFromTtl($ttl)
    {
        if ($ttl instanceof \DateTime) {
            /** @var \DateTime $ttl */
            $ttl = $ttl->getTimestamp();
        } else {
            $ttl += time();
        }
        return $ttl;
    }

}