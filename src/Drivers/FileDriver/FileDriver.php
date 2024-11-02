<?php

/**
 * Driver based on a file for Alligator rate limiter.
 *
 * This driver is not recommended for production use.
 *
 * It is not efficient and not safe to use in a multithreaded environment
 * like it would be in a production environment.
 *
 * Only for testing and development purposes.
 */

namespace Ekkazan\Alligator\Drivers\FileDriver;

use Ekkazan\Alligator\Drivers\Driver;

class FileDriver implements Driver {
    /**
     * Path to the file that will be used to store rate limits.
     *
     * @var string
     */
    private string $path;

    /**
     * File that will be used to store rate limits.
     *
     * @var string
     */
    private string $file;

    /**
     * FileDriver constructor.
     *
     * @param string $path
     * @param string $file
     */
    public function __construct(string $path, string $file = 'alligator-rate-limits.json') {
        $this->path = $path;
        $this->file = $file;
    }

    /**
     * Get the rate limit for the given alias.
     * Create a new rate limit if it does not exist.
     *
     * @param string $alias
     * @param int $interval
     * @return int
     */
    public function get(string $alias, int $interval): int {
        $data = $this->getFile();

        if (array_key_exists($alias, $data)) {
            if ($data[$alias]['created_at'] + $data[$alias]['interval'] < time()) {
                $data[$alias] = [
                    'rate' => 0,
                    'interval' => $interval,
                    'created_at' => time()
                ];
            }

            $data[$alias]['rate']++;
        } else {
            $data[$alias] = [
                'rate' => 1,
                'interval' => $interval,
                'created_at' => time()
            ];
        }

        $this->writeFile($data);

        return $data[$alias]['rate'];
    }

    /**
     * Increment the rate limit for the given alias if it exists.
     *
     * @param string $alias
     * @return bool
     */
    public function increment(string $alias,): bool {
        $data = $this->getFile();

        if (array_key_exists($alias, $data)) {
            if ($data[$alias]['created_at'] + $data[$alias]['interval'] >= time()) {
                $data[$alias]['rate']++;
            } else {
                unset($data[$alias]);
            }

            $result = $this->writeFile($data);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get the file content.
     *
     * @return array
     */
    private function getFile(): array {
        $pathToFile = implode(DIRECTORY_SEPARATOR, [$this->path, $this->file]);

        if (file_exists($pathToFile)) {
            $file = file_get_contents($pathToFile);
            $data = json_decode($file, true);
        } else {
            $data = [];
        }

        return $data;
    }

    /**
     * Write the data to the file.
     *
     * @param array $data
     * @return bool
     */
    private function writeFile(array $data): bool {
        $pathToFile = implode(DIRECTORY_SEPARATOR, [$this->path, $this->file]);

        return file_put_contents($pathToFile, json_encode($data), LOCK_EX) > 0;
    }
}