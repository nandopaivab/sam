<?php
declare(strict_types=1);

namespace TrendHunter;

use PDO;
use PDOException;
use RuntimeException;

class Database {
    private static ?PDO $pdo = null;
    private static string $driverType = 'mysql';

    /**
     * Get database connection (Singleton with SQLite auto-fallback)
     */
    public static function getConnection(): PDO {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $configPath = dirname(__DIR__) . '/config/config.php';
        if (!file_exists($configPath)) {
            throw new RuntimeException("Configuration file not found.");
        }

        $config = require $configPath;
        $dbConfig = $config['db'];

        $dsn = sprintf(
            "mysql:host=%s;port=%d;dbname=%s;charset=%s",
            $dbConfig['host'],
            $dbConfig['port'],
            $dbConfig['database'],
            $dbConfig['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            self::$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $options);
            self::$driverType = 'mysql';
            self::setupMysqlTables();
            self::checkAndCreateSavedSuppliersTable();
            return self::$pdo;
        } catch (PDOException $e) {
            // Check if connection was refused (code 2002) or operation not permitted
            // Fallback to SQLite so local execution is fully database-functional without MySQL
            $errCode = $e->getCode();
            if ($errCode === 2002 || $e->errorInfo[1] === 2002 || str_contains($e->getMessage(), '2002') || str_contains($e->getMessage(), 'refused')) {
                return self::connectSqlite();
            }
            throw new RuntimeException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Get active database driver name ('mysql' or 'sqlite')
     */
    public static function getDriverType(): string {
        self::getConnection();
        return self::$driverType;
    }

    /**
     * Connect to SQLite fallback file database and create schema
     */
    private static function connectSqlite(): PDO {
        $storageDir = dirname(__DIR__) . '/storage';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $sqlitePath = $storageDir . '/database.sqlite';
        $isNew = !file_exists($sqlitePath);

        try {
            self::$pdo = new PDO("sqlite:" . $sqlitePath, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            self::$driverType = 'sqlite';

            // Auto-compile SQLite schema if new database file was created
            if ($isNew) {
                self::setupSqliteTables();
            }
            self::checkAndCreateSavedSuppliersTable();

            return self::$pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("SQLite fallback database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Check and create saved_suppliers table on connection if it does not exist
     */
    private static function checkAndCreateSavedSuppliersTable(): void {
        $dbType = self::$driverType;
        $aiKeyType = $dbType === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $textType = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $longTextType = $dbType === 'sqlite' ? 'TEXT' : 'TEXT';
        $doubleType = $dbType === 'sqlite' ? 'REAL' : 'DOUBLE';
        $timestampType = $dbType === 'sqlite' ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

        $query = "
            CREATE TABLE IF NOT EXISTS saved_suppliers (
                id {$aiKeyType},
                user_id INT NOT NULL,
                name {$textType} NOT NULL,
                type {$textType},
                wholesale_price {$doubleType},
                profit_margin {$doubleType},
                margin_percent {$doubleType},
                roi_percent {$doubleType},
                url {$longTextType},
                address {$longTextType},
                phone {$textType},
                notes {$longTextType},
                product_title {$textType},
                created_at {$timestampType}
            )
        ";
        self::$pdo->exec($query);
    }

    /**
     * Build standard tables in MySQL
     */
    private static function setupMysqlTables(): void {
        $queries = [
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password_hash VARCHAR(255) NOT NULL,
                role VARCHAR(50) DEFAULT 'user',
                dark_mode INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                marketplace VARCHAR(100) NOT NULL,
                external_id VARCHAR(100) NOT NULL,
                title VARCHAR(500) NOT NULL,
                url TEXT NOT NULL,
                image_url TEXT,
                price DOUBLE NOT NULL,
                original_price DOUBLE,
                sales_count_est INT DEFAULT 0,
                reviews_count INT DEFAULT 0,
                rating DOUBLE DEFAULT 0.00,
                store_name VARCHAR(255),
                shipping_type VARCHAR(100),
                category VARCHAR(150),
                trend_score INT DEFAULT 0,
                competition_level VARCHAR(50) DEFAULT 'medium',
                last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_mp_ext (marketplace, external_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS product_history (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                price DOUBLE NOT NULL,
                sales_count_est INT DEFAULT 0,
                recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS searches (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                query VARCHAR(255) NOT NULL,
                category VARCHAR(150),
                marketplace VARCHAR(100),
                results_count INT DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS favorites (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_user_prod (user_id, product_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS alerts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                product_id INT NOT NULL,
                alert_type VARCHAR(100) NOT NULL DEFAULT 'price_drop',
                target_value DOUBLE NOT NULL,
                is_active INT DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS niche_analysis (
                id INT AUTO_INCREMENT PRIMARY KEY,
                keyword VARCHAR(255) NOT NULL UNIQUE,
                category VARCHAR(150),
                trend_score INT DEFAULT 0,
                avg_price DOUBLE DEFAULT 0.00,
                demand_level VARCHAR(50) DEFAULT 'medium',
                competition_level VARCHAR(50) DEFAULT 'medium',
                growth_rate DOUBLE DEFAULT 0.00,
                seasonality VARCHAR(100),
                last_analyzed TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];

        foreach ($queries as $sql) {
            try {
                self::$pdo->exec($sql);
            } catch (PDOException $e) {
                // Ignore if exists
            }
        }
    }

    /**
     * Build standard tables in SQLite
     */
    private static function setupSqliteTables(): void {
        $schema = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            role TEXT DEFAULT 'user',
            dark_mode INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            marketplace TEXT NOT NULL,
            external_id TEXT NOT NULL,
            title TEXT NOT NULL,
            url TEXT NOT NULL,
            image_url TEXT,
            price REAL NOT NULL,
            original_price REAL,
            sales_count_est INTEGER DEFAULT 0,
            reviews_count INTEGER DEFAULT 0,
            rating REAL DEFAULT 0.00,
            store_name TEXT,
            shipping_type TEXT,
            category TEXT,
            trend_score INTEGER DEFAULT 0,
            competition_level TEXT DEFAULT 'medium',
            last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(marketplace, external_id)
        );

        CREATE TABLE IF NOT EXISTS product_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            product_id INTEGER NOT NULL,
            price REAL NOT NULL,
            sales_count_est INTEGER DEFAULT 0,
            recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS searches (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            query TEXT NOT NULL,
            category TEXT,
            marketplace TEXT,
            results_count INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS favorites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, product_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS alerts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            product_id INTEGER NOT NULL,
            alert_type TEXT NOT NULL DEFAULT 'price_drop',
            target_value REAL NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS niche_analysis (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            keyword TEXT NOT NULL UNIQUE,
            category TEXT,
            trend_score INTEGER DEFAULT 0,
            avg_price REAL DEFAULT 0.00,
            demand_level TEXT DEFAULT 'medium',
            competition_level TEXT DEFAULT 'medium',
            growth_rate REAL DEFAULT 0.00,
            seasonality TEXT,
            ai_summary TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );

        INSERT OR IGNORE INTO users (id, name, email, password_hash, role) VALUES 
        (1, 'Administrador', 'admin@trendhunter.com.br', '$2y$10$O0HlP452D3kZq91w2vQYSu.W.8W6x9LpZcWf0n2lT4z31vBqKx3iG', 'admin');
        ";

        self::$pdo->exec($schema);
    }
}
