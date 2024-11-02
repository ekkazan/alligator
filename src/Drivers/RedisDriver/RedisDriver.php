<?php

/**
 * Driver based on Redis for Alligator rate limiter.
 * Dependent on the PHP Redis extension.
 *
 * See for more details: https://github.com/phpredis/phpredis
 */

namespace Ekkazan\Alligator\Drivers\RedisDriver;

use Ekkazan\Alligator\Drivers\Driver;
use Redis;
use RedisException;

class RedisDriver implements Driver {
    /**
     * Redis instance that will be used to store rate limits.
     *
     * @var Redis
     */
    private Redis $redis;

    /**
     * RedisDriver constructor.
     *
     * If a Redis instance is not provided, create a new one with the provided configuration.
     *
     * @param Redis|array $redis
     */
    public function __construct(Redis|array $redis) {
        if (is_array($redis)) {
            $this->redis = new Redis($redis);
        } else {
            $this->redis = $redis;
        }
    }

    /**
     * Get the rate limit for the given alias.
     * Create a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return int
     * @throws RedisException
     */
    public function get(string $alias, int $interval): int {
        if ($this->redis->exists($alias)) {
            $rate = $this->redis->get($alias);
        } else {
            $this->redis->set($alias, 1, $interval);

            $rate = 1;
        }

        return $rate;
    }

    /**
     * Increment the rate limit for the given alias if it exists.
     *
     * @param string $alias
     * @return bool
     * @throws RedisException
     */
    public function increment(string $alias): bool {
        if (!$this->redis->exists($alias)) {
            $result = $this->redis->incr($alias) > 0;
        }

        return $result ?? false;
    }
}