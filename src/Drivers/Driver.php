<?php

namespace Ekkazan\Alligator\Drivers;

interface Driver {
    /**
     * Get the rate limit for the given alias.
     * Create a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return int
     */
    public function get(string $alias, int $interval): int;

    /**
     * Increment the rate limit for the given alias if it exists.
     *
     * @param string $alias
     * @return bool
     */
    public function increment(string $alias): bool;

    /**
     * Get the rate limit for the given alias and increment it.
     * Create a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return int
     */
    public function getAndIncrement(string $alias, int $interval): int;
}