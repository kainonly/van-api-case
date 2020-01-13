<?php
declare (strict_types=1);

namespace App\RedisModel\System;

use Hyperf\DbConnection\Db;
use Hyperf\Support\Common\RedisModel;

class ResourceRedis extends RedisModel
{
    protected $key = 'system:resource';
    private $data = [];

    /**
     * Clear Cache
     */
    public function clear(): void
    {
        $this->redis->del($this->key);
    }

    /**
     * Get Cache
     * @return array
     */
    public function get(): array
    {
        if (!$this->redis->exists($this->key)) {
            $this->update();
        } else {
            $raws = $this->redis->get($this->key);
            $this->data = json_decode($raws, true);
        }
        return $this->data;
    }

    /**
     * Refresh Cache
     */
    private function update(): void
    {
        $queryLists = Db::table('resource')
            ->where('status', '=', 1)
            ->orderBy('sort')
            ->get(['key', 'parent', 'name', 'nav', 'router', 'policy', 'icon']);

        if ($queryLists->isEmpty()) {
            return;
        }

        $this->redis->set($this->key, $queryLists->toJson());
        $this->data = $queryLists->toArray();
    }
}