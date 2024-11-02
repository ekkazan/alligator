<?php

namespace ekkazan;

use ekkazan\Alligator\Drivers\Driver;
use ekkazan\Alligator\Periods;

class Alligator {
    /**
     * The driver that will be used to track rates.
     *
     * @var Driver
     */
    private Driver $driver;

    /**
     * Global callback that will be called when the rate limit is not exceeded.
     *
     * This will be called only if it is provided and will be called with the provided callback.
     *
     * It takes 1 parameter: alias of the called rate limit.
     *
     * @var callable
     */
    private $onSuccessCallback;

    /**
     * Global callback that will be called when the rate limit is exceeded.
     *
     * This will be called only if it is provided and will be called with the provided callback.
     *
     * It takes 1 parameter: alias of the exceeded rate limit.
     *
     * An example usage can be a logging mechanism.
     *
     * @var callable
     */
    private $onFailCallback;

    /**
     * Alligator constructor.
     * Set the driver.
     *
     * @param Driver $driver
     * @param callable|null $onSuccess
     * @param callable|null $onFail
     */
    public function __construct(Driver $driver, callable $onFail = null, callable $onSuccess = null) {
        $this->driver = $driver;
        $this->onSuccessCallback = $onSuccess;
        $this->onFailCallback = $onFail;
    }

    /**
     * Change the driver if it is necessary at some point.
     * Allow to use multiple drivers in the same application.
     * Possibly not going to be used much.
     *
     * @param Driver $driver
     * @return void
     */
    public function setDriver(Driver $driver): void {
        $this->driver = $driver;
    }

    /**
     * Get the driver that is currently being used.
     *
     * @return Driver
     */
    public function getDriver(): Driver {
        return $this->driver;
    }

    /**
     * Set the callback that will be called when the rate limit is not exceeded.
     *
     * @param callable $callback
     * @return void
     */
    public function setOnSuccessCallback(callable $callback): void {
        $this->onSuccessCallback = $callback;
    }

    /**
     * Set the callback that will be called when the rate limit is exceeded.
     *
     * @param callable $callback
     * @return void
     */
    public function setOnFailCallback(callable $callback): void {
        $this->onFailCallback = $callback;
    }

    /**
     * Check the rate limit for the given alias. If the rate limit is not exceeded,
     * call the onSuccess callback.
     *
     * @param string $alias
     * @param int|array $rate
     * @param callable $onSuccess
     * @param int $interval
     * @return mixed
     */
    public function limit(string $alias, int|array $rate, callable $onSuccess, int $interval = 60): mixed {
        if (!is_array($rate)) {
            $intervals = [$interval => $rate];
        } else {
            $intervals = $rate;
        }

        $failed = false;
        $result = false;

        foreach ($intervals as $int => $rate) {
            $aliasCustom = $alias . '_' . $int;

            $countRate = $this->driver->get($aliasCustom, $int);

            $this->driver->increment($aliasCustom);

            if ($countRate > $rate) {
                $failed = true;

                if ($this->onFailCallback) {
                    call_user_func($this->onFailCallback, $alias, $int);
                    break;
                }
            }
        }

        if (!$failed) {
            $result = call_user_func($onSuccess);

            if (is_null($result)) {
                $result = true;
            }

            if ($this->onSuccessCallback) {
                call_user_func($this->onSuccessCallback, $alias);
            }
        }

        return $result;
    }

    /**
     * Check the rate limit for the given alias and interval.
     *
     * @param string $alias
     * @param int|array $rate
     * @param int $interval
     * @return bool
     */
    public function isRateLimitExceeded(string $alias, int|array $rate, int $interval = 60): bool {
        if (!is_array($rate)) {
            $intervals = [$interval => $rate];
        } else {
            $intervals = $rate;
        }

        $failed = false;

        foreach ($intervals as $int => $rate) {
            $aliasCustom = $alias . '_' . $rate;

            $countRate = $this->driver->get($aliasCustom, $int);

            if ($countRate > $rate) {
                $failed = true;
                break;
            }
        }

        return !$failed;
    }

    /**
     * Set the rate limit per minute for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function perMinute(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_MINUTE);
    }

    /**
     * Set the rate limit per 5 minutes for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function per5Minutes(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_5_MINUTE);
    }

    /**
     * Set the rate limit per 15 minutes for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function per15Minutes(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_15_MINUTE);
    }

    /**
     * Set the rate limit per 30 minutes for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function per30Minutes(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_30_MINUTE);
    }

    /**
     * Set the rate limit per hour for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function perHour(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_HOUR);
    }

    /**
     * Set the rate limit per 12 hours for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function per12Hour(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_12_HOURS);
    }

    /**
     * Set the rate limit per day for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function perDay(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_DAY);
    }

    /**
     * Set the rate limit per week for the given alias.
     *
     * @param string $alias
     * @param int $rate
     * @param callable $onSuccess
     * @return mixed
     */
    public function perWeek(string $alias, int $rate, callable $onSuccess): mixed {
        return $this->limit($alias, $rate, $onSuccess, Periods::PER_WEEK);
    }


    /**
     * Get current rate for the given alias.
     * It creates a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return bool
     */
    public function get(string $alias, int $interval): bool {
        $alias = $alias . '_' . $interval;

        return $this->driver->get($alias, $interval);
    }

    /**
     * Use this method to manually increment the rate limit for the given alias.
     * It creates a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return void
     */
    public function increment(string $alias, int $interval): void {
        $alias = $alias . '_' . $interval;

        $this->driver->increment($alias, $interval);
    }
}