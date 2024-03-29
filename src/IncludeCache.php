<?php

namespace Pilulka\Cache;

use Closure;

class IncludeCache implements Repository
{

    private $isChanged = false;
    protected $filePath;
    private $data;

    /**
     * FileCache constructor.
     * @param $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->loadData();
    }

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

    public function __destruct()
    {
        $this->save();
    }

    private function save()
    {
        if ($this->isChanged) {
            file_put_contents($this->filePath, '<?php return ' . var_export($this->data, true) . ';', LOCK_EX);
        }
    }

    private function loadData()
    {
        if (file_exists($this->filePath)) {
            $this->data = (array)include realpath($this->filePath);
        } else {
            $this->data = [];
        }
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