<?php
declare(strict_types=1);

namespace TrendHunter;

use Redis;
use RedisException;
use Exception;

class Cache {
    private static ?Redis $redis = null;
    private static bool $isRedisConnected = false;
    private static string $fallbackDir;

    /**
     * Initialize connection parameters
     */
    private static function init(): void {
        self::$fallbackDir = dirname(__DIR__) . '/storage/cache';
        if (!is_dir(self::$fallbackDir)) {
            mkdir(self::$fallbackDir, 0755, true);
        }

        if (self::$redis !== null) {
            return;
        }

        $configPath = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configPath)) {
            return;
        }

        $config = require $configPath;
        $rConfig = $config['redis'] ?? [];

        if (!($rConfig['enabled'] ?? false) || !class_exists('Redis')) {
            return;
        }

        try {
            self::$redis = new Redis();
            $connected = self::$redis->connect(
                $rConfig['host'] ?? '127.0.0.1',
                $rConfig['port'] ?? 6379,
                $rConfig['timeout'] ?? 2.5
            );

            if ($connected) {
                if (!empty($rConfig['password'])) {
                    self::$redis->auth($rConfig['password']);
                }
                
                if (!empty($rConfig['prefix'])) {
                    self::$redis->setOption(Redis::OPT_PREFIX, $rConfig['prefix']);
                }

                self::$isRedisConnected = true;
            }
        } catch (RedisException $e) {
            // Log or ignore to activate fallback mode
            self::$isRedisConnected = false;
            self::$redis = null;
        }
    }

    /**
     * Get a value from the cache
     */
    public static function get(string $key): mixed {
        self::init();

        $safeKey = self::getSafeKey($key);

        if (self::$isRedisConnected && self::$redis !== null) {
            try {
                $val = self::$redis->get($safeKey);
                return $val !== false ? unserialize($val) : null;
            } catch (RedisException) {
                // Failover to file-based
            }
        }

        // File fallback
        $file = self::$fallbackDir . '/' . md5($safeKey) . '.bin';
        if (file_exists($file)) {
            $data = file_get_contents($file);
            if ($data !== false) {
                $payload = unserialize($data);
                if (is_array($payload) && isset($payload['expire'], $payload['value'])) {
                    if ($payload['expire'] === 0 || $payload['expire'] > time()) {
                        return $payload['value'];
                    }
                    // Expired
                    unlink($file);
                }
            }
        }

        return null;
    }

    /**
     * Set a value in the cache
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool {
        self::init();

        $safeKey = self::getSafeKey($key);
        $serialized = serialize($value);

        if (self::$isRedisConnected && self::$redis !== null) {
            try {
                if ($ttl > 0) {
                    return self::$redis->setex($safeKey, $ttl, $serialized);
                } else {
                    return self::$redis->set($safeKey, $serialized);
                }
            } catch (RedisException) {
                // Failover to file-based
            }
        }

        // File fallback
        $file = self::$fallbackDir . '/' . md5($safeKey) . '.bin';
        $payload = [
            'expire' => $ttl > 0 ? time() + $ttl : 0,
            'value' => $value
        ];

        return file_put_to_file($file, serialize($payload)) !== false;
    }

    /**
     * Delete a value from the cache
     */
    public static function delete(string $key): bool {
        self::init();

        $safeKey = self::getSafeKey($key);

        if (self::$isRedisConnected && self::$redis !== null) {
            try {
                return self::$redis->del($safeKey) > 0;
            } catch (RedisException) {
                // Failover
            }
        }

        // File fallback
        $file = self::$fallbackDir . '/' . md5($safeKey) . '.bin';
        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Flush cache entries
     */
    public static function flush(): bool {
        self::init();

        $success = true;

        if (self::$isRedisConnected && self::$redis !== null) {
            try {
                $success = self::$redis->flushAll();
            } catch (RedisException) {
                $success = false;
            }
        }

        // File fallback
        $files = glob(self::$fallbackDir . '/*.bin');
        if (is_array($files)) {
            foreach ($files as $file) {
                if (file_exists($file)) {
                    $success = unlink($file) && $success;
                }
            }
        }

        return $success;
    }

    /**
     * Sanitize cache key
     */
    private static function getSafeKey(string $key): string {
        return preg_replace('/[^a-zA-Z0-9_\-\:]/', '_', $key);
    }
}

/**
 * Helper fallback for file writing
 */
function file_put_to_file(string $filename, string $content): bool|int {
    return file_put_contents($filename, $content, LOCK_EX);
}
