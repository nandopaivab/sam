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
            self::checkAndCreateApiColumns();
            self::checkAndCreateErpTables();
            self::checkAndCreateKeywordTrendsTable(self::$pdo);
            self::checkAndSeedNicheProducts(self::$pdo);
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
            self::checkAndCreateApiColumns();
            self::checkAndCreateErpTables();
            self::checkAndCreateKeywordTrendsTable(self::$pdo);
            self::checkAndSeedNicheProducts(self::$pdo);

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
     * Check and create API keys columns on users table if missing
     */
    private static function checkAndCreateApiColumns(): void {
        $dbType = self::$driverType;
        $type = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $providerType = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(50)';
        
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN openai_api_key {$type} DEFAULT NULL;");
        } catch (\Exception $e) {}
        
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN gemini_api_key {$type} DEFAULT NULL;");
        } catch (\Exception $e) {}
        
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN ai_provider {$providerType} DEFAULT 'local';");
        } catch (\Exception $e) {}
    }

    /**
     * Check and create ERP products and sales tables
     */
    private static function checkAndCreateErpTables(): void {
        $dbType = self::$driverType;
        $aiKeyType = $dbType === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $textType = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $longTextType = $dbType === 'sqlite' ? 'TEXT' : 'TEXT';
        $doubleType = $dbType === 'sqlite' ? 'REAL' : 'DOUBLE';
        $intType = $dbType === 'sqlite' ? 'INTEGER' : 'INT';
        $timestampType = $dbType === 'sqlite' ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

        // 1. Create erp_products table
        $queryProducts = "
            CREATE TABLE IF NOT EXISTS erp_products (
                id {$aiKeyType},
                user_id {$intType} NOT NULL,
                sku {$textType} NOT NULL,
                title {$textType} NOT NULL,
                cost_price {$doubleType} NOT NULL DEFAULT 0.0,
                selling_price {$doubleType} NOT NULL DEFAULT 0.0,
                stock_quantity {$intType} NOT NULL DEFAULT 0,
                min_stock {$intType} NOT NULL DEFAULT 5,
                created_at {$timestampType}
            )
        ";
        self::$pdo->exec($queryProducts);

        // 2. Create erp_sales table
        $querySales = "
            CREATE TABLE IF NOT EXISTS erp_sales (
                id {$aiKeyType},
                user_id {$intType} NOT NULL,
                product_id {$intType} NOT NULL,
                platform {$textType} NOT NULL,
                quantity {$intType} NOT NULL DEFAULT 1,
                sale_price {$doubleType} NOT NULL DEFAULT 0.0,
                total_amount {$doubleType} NOT NULL DEFAULT 0.0,
                sale_date {$timestampType}
            )
        ";
        self::$pdo->exec($querySales);
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
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS crm_activities (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                company_name VARCHAR(255) NOT NULL,
                contact_type VARCHAR(50) NOT NULL,
                contact_date DATE NOT NULL,
                contact_time TIME NOT NULL,
                responsible_name VARCHAR(100) NOT NULL,
                objective VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                result_summary TEXT DEFAULT NULL,
                negotiation_status VARCHAR(50) NOT NULL,
                interest_level VARCHAR(50) DEFAULT 'Alto',
                priority VARCHAR(50) DEFAULT 'Média',
                next_action VARCHAR(255) DEFAULT NULL,
                followup_date DATE DEFAULT NULL,
                product_id INT DEFAULT NULL,
                product_title VARCHAR(255) DEFAULT NULL,
                marketplace VARCHAR(100) DEFAULT NULL,
                attachment_url VARCHAR(500) DEFAULT NULL,
                created_by_name VARCHAR(100) DEFAULT 'Sistema',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS activity_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT DEFAULT NULL,
                user_name VARCHAR(100) DEFAULT 'Sistema',
                module VARCHAR(100) NOT NULL,
                action_type VARCHAR(100) NOT NULL,
                target_record VARCHAR(255) DEFAULT NULL,
                old_values TEXT DEFAULT NULL,
                new_values TEXT DEFAULT NULL,
                ip_address VARCHAR(50) DEFAULT '127.0.0.1',
                device_info VARCHAR(255) DEFAULT 'Navegador Web',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS blue_ocean_products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                category VARCHAR(100) NOT NULL,
                niche VARCHAR(100) NOT NULL,
                target_audience VARCHAR(255) NOT NULL,
                problem_solved TEXT NOT NULL,
                avg_price DECIMAL(10,2) NOT NULL,
                est_cost DECIMAL(10,2) NOT NULL,
                proj_margin DECIMAL(5,2) NOT NULL,
                approx_competitors INT DEFAULT 10,
                trend_score INT DEFAULT 90,
                seasonality VARCHAR(100) DEFAULT 'Ano Todo',
                related_suppliers TEXT DEFAULT NULL,
                suggested_kits TEXT DEFAULT NULL,
                opportunity_badge VARCHAR(50) DEFAULT 'Alta Oportunidade',
                investment_recommendation TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

            "CREATE TABLE IF NOT EXISTS baby_niche_products (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                sub_category VARCHAR(100) NOT NULL,
                age_range VARCHAR(100) NOT NULL,
                safety_cert VARCHAR(100) DEFAULT 'INMETRO / Atóxico',
                material_info VARCHAR(255) DEFAULT 'Livre de BPA',
                cleaning_ease VARCHAR(100) DEFAULT 'Fácil higienização',
                small_parts_risk VARCHAR(50) DEFAULT 'Baixo Risco',
                avg_price DECIMAL(10,2) NOT NULL,
                est_cost DECIMAL(10,2) NOT NULL,
                suggested_kits TEXT DEFAULT NULL,
                income_bracket VARCHAR(100) DEFAULT 'Todas as faixas (Acessível)',
                ai_analysis TEXT DEFAULT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
        ];

        foreach ($queries as $sql) {
            try {
                self::$pdo->exec($sql);
            } catch (PDOException $e) {
                // Ignore if exists
            }
        }

        // Seed MySQL default records if tables are empty
        try {
            $cntCrm = (int)self::$pdo->query("SELECT COUNT(*) FROM crm_activities")->fetchColumn();
            if ($cntCrm === 0) {
                $today = date('Y-m-d');
                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                $insCrm = self::$pdo->prepare("INSERT INTO crm_activities (user_id, company_name, contact_type, contact_date, contact_time, responsible_name, objective, description, negotiation_status, interest_level, priority, next_action, followup_date, product_title, marketplace, created_by_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $insCrm->execute([1, 'Distribuidora SP Tech Express', 'WhatsApp', $today, '10:30:00', 'Fernando Paiva', 'Verificar tabela progressiva de desconto', 'Conversa inicial para lote acima de 100 un. do Projetor 4K.', 'Descoberta', 'Alto', 'Média', 'Solicitar envio de catálogo', $tomorrow, 'Projetor 4K', 'Shopee', 'Sistema']);
                $insCrm->execute([1, 'Forn. Bebê Feliz Atacado', 'WhatsApp', $today, '14:00:00', 'Fernando Paiva', 'Amostra de Prato Ventosa Silicone', 'Solicitado envio de amostra nas cores azul e rosa para testes de durabilidade e aderência.', 'Amostra Solicitada', 'Alto', 'Alta', 'Verificar código de rastreio da amostra', $tomorrow, 'Prato com Ventosa Silicone', 'Shopee', 'Sistema']);
                $insCrm->execute([1, 'Atacadista MegaUtil BR', 'E-mail', $today, '09:15:00', 'Fernando Paiva', 'Cotação de Organizadores de Temperos', 'Recebida cotação inicial de R$ 12,50/unidade. Negociando para R$ 10,00.', 'Negociação', 'Médio', 'Média', 'Aguardar resposta sobre lote de 500 un.', $tomorrow, 'Organizador de Temperos', 'Mercado Livre', 'Sistema']);
            }
            
            // Alter users table to add API configuration fields if missing
            try {
                self::$pdo->exec("ALTER TABLE users ADD COLUMN openai_api_key VARCHAR(255) DEFAULT NULL;");
            } catch (\Exception $e) {}
            try {
                self::$pdo->exec("ALTER TABLE users ADD COLUMN gemini_api_key VARCHAR(255) DEFAULT NULL;");
            } catch (\Exception $e) {}
            try {
                self::$pdo->exec("ALTER TABLE users ADD COLUMN ai_provider VARCHAR(50) DEFAULT 'local';");
            } catch (\Exception $e) {}
        } catch (\Exception $e) {
            // Ignore seeding errors
        }
    }

    /**
     * Unified seeder for Blue Ocean and Baby Niche tables on both SQLite & MySQL.
     * Ensures at least 10 high-quality products are present in each.
     */
    public static function checkAndSeedNicheProducts(PDO $db, bool $force = false): void {
        $dbType = self::$driverType;
        $aiKeyType = $dbType === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $textType = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $longTextType = $dbType === 'sqlite' ? 'TEXT' : 'TEXT';
        $doubleType = $dbType === 'sqlite' ? 'REAL' : 'DECIMAL(10,2)';
        $decimal52Type = $dbType === 'sqlite' ? 'REAL' : 'DECIMAL(5,2)';
        $intType = $dbType === 'sqlite' ? 'INTEGER' : 'INT';
        $timestampType = $dbType === 'sqlite' ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';

        // Ensure tables exist
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS blue_ocean_products (
                id {$aiKeyType},
                title {$textType} NOT NULL,
                category {$textType} NOT NULL,
                niche {$textType} NOT NULL,
                target_audience {$textType} NOT NULL,
                problem_solved {$longTextType} NOT NULL,
                avg_price {$doubleType} NOT NULL,
                est_cost {$doubleType} NOT NULL,
                proj_margin {$decimal52Type} NOT NULL,
                approx_competitors {$intType} DEFAULT 10,
                trend_score {$intType} DEFAULT 90,
                seasonality {$textType} DEFAULT 'Ano Todo',
                related_suppliers {$longTextType} DEFAULT NULL,
                suggested_kits {$longTextType} DEFAULT NULL,
                opportunity_badge {$textType} DEFAULT 'Alta Oportunidade',
                investment_recommendation {$longTextType} DEFAULT NULL,
                created_at {$timestampType}
            )");
        } catch (\Exception $e) {}

        try {
            $db->exec("CREATE TABLE IF NOT EXISTS baby_niche_products (
                id {$aiKeyType},
                title {$textType} NOT NULL,
                sub_category {$textType} NOT NULL,
                age_range {$textType} NOT NULL,
                safety_cert {$textType} DEFAULT 'INMETRO / Atóxico',
                material_info {$textType} DEFAULT 'Livre de BPA',
                cleaning_ease {$textType} DEFAULT 'Fácil higienização',
                small_parts_risk {$textType} DEFAULT 'Baixo Risco',
                avg_price {$doubleType} NOT NULL,
                est_cost {$doubleType} NOT NULL,
                suggested_kits {$longTextType} DEFAULT NULL,
                income_bracket {$textType} DEFAULT 'Todas as faixas (Acessível)',
                ai_analysis {$longTextType} DEFAULT NULL,
                created_at {$timestampType}
            )");
        } catch (\Exception $e) {}

        try {
            // Seed Blue Ocean
            $cntBlue = (int)$db->query("SELECT COUNT(*) FROM blue_ocean_products")->fetchColumn();
            if ($cntBlue < 10 || $force) {
                $db->exec("DELETE FROM blue_ocean_products");
                $insBlue = $db->prepare("INSERT INTO blue_ocean_products (title, category, niche, target_audience, problem_solved, avg_price, est_cost, proj_margin, approx_competitors, trend_score, seasonality, related_suppliers, suggested_kits, opportunity_badge, investment_recommendation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $insBlue->execute(['Organizador de Temperos para Gaveta', 'Organização', 'Cozinha', 'Donas de casa e entusiastas de culinária', 'Organização e facilidade de visualização de potes de condimento em gavetas padrão.', 59.90, 15.00, 299.30, 4, 94, 'Ano Todo', 'Atacadista MegaUtil BR', 'Kit com 4 Organizadores | Kit Completo com 12 potes de vidro incluídos | Kit Duplo Organizador + Etiquetas de Identificação', 'Alta Oportunidade', 'Excelente para anúncios segmentados no TikTok focados em organização.']);
                $insBlue->execute(['Suporte Organizador de Tampas de Panela', 'Organização', 'Cozinha', 'Pessoas com cozinhas compactas', 'Otimização de espaço em armários de cozinha ao organizar tampas verticalmente.', 45.00, 12.00, 275.00, 8, 88, 'Ano Todo', 'Mega Importadora SP', 'Kit Organizador de Tampas + Organizador de Panelas | Kit Duplo para armários grandes', 'Média Oportunidade', 'Bom para cross-sell com o Organizador de Gavetas.']);
                $insBlue->execute(['Mini Selador de Embalagens Portátil', 'Utensílios Domésticos', 'Cozinha & Organização', 'Donas de casa e pessoas que moram sozinhas', 'Preserva alimentos abertos vedando embalagens plásticas instantaneamente por calor.', 24.90, 5.00, 398.00, 12, 95, 'Ano Todo', 'Lojas 10 BR Atacado', 'Kit com 2 Seladores (Cores Sortidas) | Kit Selador + 4 Pilhas Recarregáveis | Lote com 5 Seladores Família', 'Alta Oportunidade', 'Excelente apelo visual para vídeos rápidos demonstrativos no Instagram Reels.']);
                $insBlue->execute(['Espelho de Maquiagem com Luz LED Touch', 'Beleza & Organização', 'Quarto & Closet', 'Jovens e blogueiras de maquiagem', 'Iluminação ideal e ajuste de ângulo para maquiagem em ambientes escuros.', 79.90, 22.00, 263.18, 7, 91, 'Ano Todo', 'Atacadista ImportaExpress', 'Kit Espelho LED + Organizador Acrílico | Kit Viagem Espelho + Necessaire | Kit Duplo Espelho Maquiagem', 'Alta Oportunidade', 'Alto valor percebido, ideal para criativos de antes e depois (make).']);
                $insBlue->execute(['Organizador Acrílico de Maquiagem Giratório 360°', 'Beleza & Organização', 'Quarto & Banheiro', 'Mulheres com foco em organização de cosméticos', 'Otimiza espaço na penteadeira organizando dezenas de cosméticos em base giratória de fácil acesso.', 89.90, 24.00, 274.58, 6, 93, 'Ano Todo', 'Mega Importadora SP', 'Organizador 360 + Espelho Maquiagem | Kit Duplo Organizador Giratório | Organizador com Conjunto de Pincéis', 'Alta Oportunidade', 'Produto muito procurado para presentes de fim de ano.']);
                $insBlue->execute(['Lixeira Automática com Sensor de Presença', 'Tecnologia & Casa', 'Banheiro & Cozinha', 'Pessoas focadas em higiene e casas inteligentes', 'Abertura sem toque físico, prevenindo contaminação por germes e odores desagradáveis.', 129.90, 38.00, 241.84, 5, 96, 'Ano Todo', 'Atacadista TechUtil BR', 'Kit Lixeira Automática Banheiro + Cozinha | Lixeira Automática + Pilhas Premium | Kit Lixeiras Inteligentes Triplo', 'Alta Oportunidade', 'Alta taxa de cliques com anúncios em formato unboxing.']);
                $insBlue->execute(['Mini Triturador de Alho Elétrico Sem Fio', 'Utensílios Domésticos', 'Cozinha', 'Cozinheiros iniciantes e experientes', 'Tritura temperos em 5 segundos, poupando tempo e evitando cheiro forte nas mãos.', 39.90, 9.50, 320.00, 14, 92, 'Ano Todo', 'Atacadista MegaUtil BR', 'Kit Triturador + Mini Selador de Embalagens | Triturador Duplo (Cozinha e Churrasqueira) | Triturador + Escova de Limpeza', 'Média Oportunidade', 'Volume gigante de buscas no TikTok Shop. Focar em precificação agressiva.']);
                $insBlue->execute(['Removedor de Fiapos de Roupas Elétrico Recarregável', 'Lavanderia & Utilidades', 'Casa & Roupas', 'Pessoas com pets ou casacos de lã', 'Remove bolinhas e fiapos de tecidos antigos, devolvendo o aspecto de novo às roupas.', 49.90, 12.50, 299.20, 9, 89, 'Inverno', 'Lojas 10 BR Atacado', 'Removedor de Fiapos + 2 Lâminas Extras | Removedor + Rolo Lavável Tirar Pelos | Kit Lavanderia Premium', 'Alta Oportunidade', 'Sazonalidade forte no outono e inverno, excelente conversão orgânica.']);
                $insBlue->execute(['Mop de Limpeza Triangular Articulado', 'Limpeza & Utilidades', 'Casa & Limpeza', 'Diaristas e donos de casa práticos', 'Limpa cantos de difícil acesso em paredes e tetos sem necessidade de escadas.', 69.90, 18.00, 288.33, 5, 94, 'Ano Todo', 'Mega Importadora SP', 'Mop Triangular + 2 Panos Microfibra Reservas | Kit Mop Triangular + Mop Spray | Kit Limpeza Geral 360', 'Alta Oportunidade', 'Vídeos demonstrativos de limpeza extrema geram enorme viralização.']);
                $insBlue->execute(['Luminária Projetor de Astronauta Galáxia LED', 'Decoração & Infantil', 'Quarto & Decoração', 'Pais com crianças pequenas ou jovens geeks', 'Ajuda crianças a dormir simulando constelações e nebulosas no teto do quarto.', 119.90, 32.00, 274.68, 11, 97, 'Ano Todo', 'Atacadista ImportaExpress', 'Projetor Astronauta + Fita LED RGB | Astronauta Duplo (Quarto das Crianças) | Projetor Astronauta + Luminária Lua', 'Alta Oportunidade', 'Forte apelo visual para anúncios noturnos e presentes infantis.']);
            }

            // Seed Baby Niche
            $cntBaby = (int)$db->query("SELECT COUNT(*) FROM baby_niche_products")->fetchColumn();
            if ($cntBaby < 10 || $force) {
                $db->exec("DELETE FROM baby_niche_products");
                $insBaby = $db->prepare("INSERT INTO baby_niche_products (title, sub_category, age_range, safety_cert, material_info, cleaning_ease, small_parts_risk, avg_price, est_cost, suggested_kits, income_bracket, ai_analysis) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $insBaby->execute(['Prato com Ventosa Silicone BPA Free', 'Alimentação', '6 meses a 3 anos', 'INMETRO / Atóxico', 'Silicone 100% Alimentar Livre de BPA', 'Pode ir à lava-louças', 'Sem peças pequenas', 39.90, 10.00, 'Kit Alimentação Completa (Prato + Babador + Colher de Silicone) | Kit com 2 Pratos Cores Sortidas | Kit Introdução Alimentar Premium', 'Todas as faixas (Acessível)', 'Produto altamente recomendado para introdução alimentar. O diferencial é a ventosa forte que evita quedas e bagunça.']);
                $insBaby->execute(['Mordedor Sensorial Girafa Antiasfixia', 'Desenvolvimento Infantil', '3 meses a 18 meses', 'INMETRO / Atóxico', 'Silicone Macio BPA Free', 'Esterilizável em água fervente', 'Sem peças pequenas', 29.90, 7.50, 'Kit Mordedor + Preendedor de chupeta | Kit Mordedores Fofos (Girafa + Elefante) | Kit Dentinho Saudável Premium', 'Todas as faixas (Acessível)', 'Mordedor anatômico que alivia as gengivas do bebê com segurança.']);
                $insBaby->execute(['Alimentador Alimentar de Silicone para Frutas', 'Alimentação', '6 meses a 12 meses', 'INMETRO / Atóxico', 'Bico de Silicone e Alça Plástica BPA Free', 'Fácil desmontagem e lavagem', 'Sem peças pequenas', 19.90, 4.50, 'Alimentador de Frutas + 2 Bicos Reservas | Kit Alimentador + Babador Impermeável | Kit Lanche Prático Bebê', 'Todas as faixas (Acessível)', 'Permite ao bebê experimentar frutas e legumes sólidos com segurança, sem risco de asfixia.']);
                $insBaby->execute(['Babador de Silicone com Coletor de Migalhas', 'Alimentação', '4 meses a 3 anos', 'INMETRO / Atóxico', 'Silicone Impermeável Livre de BPA', 'Limpeza simples com pano ou água', 'Sem peças pequenas', 24.90, 6.00, 'Kit com 2 Babadores Silicone Cores Sortidas | Kit Alimentação (Babador + Prato Ventosa) | Kit Passeio Sem Sujeira', 'Todas as faixas (Acessível)', 'Ajuda a manter as roupas limpas durante as refeições. O coletor frontal de migalhas evita sujeira no chão.']);
                $insBaby->execute(['Ninho Redutor de Berço Algodão Macio', 'Sono', 'Recém-nascido a 6 meses', 'Tecido Hipoalergênico', 'Algodão 100% com enchimento de Poliéster', 'Capa removível e lavável', 'Baixo Risco', 99.90, 32.00, 'Ninho Redutor + Almofada Amamentação | Ninho Redutor + Manta Microfibra Macia | Kit Sono Seguro Recém-nascido', 'Todas as faixas (Acessível)', 'Simula o ambiente do útero materno, acalmando o bebê e garantindo um sono mais longo e tranquilo.']);
                $insBaby->execute(['Almofada de Banho para Bebê Ergonômica', 'Banho', 'Recém-nascido a 10 meses', 'Hipoalergênica / Segura', 'Enchimento EPS Microesferas e Lycra', 'Secagem rápida por pendurar', 'Sem peças pequenas', 79.90, 24.00, 'Almofada de Banho + Brinquedos Ventosa | Kit Banho Feliz (Almofada + Toalha com Capuz) | Almofada Banho + Termômetro Água', 'Todas as faixas (Acessível)', 'Mantém a cabeça do bebê acima da água de forma estável, permitindo que a mãe fique com as manos livres.']);
                $insBaby->execute(['Protetor de Quinas de Silicone Transparente (Kit)', 'Segurança', '9 meses a 4 anos', 'Adesivo 3M Extra Forte', 'Silicone Flexível Hipoalergênico', 'Pano úmido', 'Atenção no encaixe', 15.90, 3.00, 'Kit com 8 Protetores de Quina | Kit Segurança Casa (8 Protetores + 2 Travas de Gaveta) | Kit Segurança Max (20 peças)', 'Todas as faixas (Acessível)', 'Essencial para a fase em que o bebê começa a engatinhar e andar. Evita acidentes graves em móveis pontiagudos.']);
                $insBaby->execute(['Organizador de Brinquedos de Banho com Ventosa', 'Organização', '6 meses a 4 anos', 'Ventosas Reforçadas', 'Rede de Nylon de alta resistência', 'Lavável em máquina', 'Sem peças pequenas', 29.90, 7.00, 'Organizador Rede + 3 Brinquedos de Água | Kit Organizadores Banheiro Duplo | Organizador Banho + Toalha Infantil', 'Todas as faixas (Acessível)', 'Evita mofo nos brinquedos mantendo-os escorridos e guardados de forma organizada na parede do banheiro.']);
                $insBaby->execute(['Copo de Treinamento com Alças 360° Antivazamento', 'Alimentação', '6 meses a 2 anos', 'INMETRO / Atóxico', 'Silicone e Plástico Livre de BPA', 'Desmontável para higienização', 'Sem peças pequenas', 34.90, 8.50, 'Copo 360 + Copo Canudo | Kit Transição de Mamadeira Completo | Kit Copo 360 Cores Gêmeos', 'Todas as faixas (Acessível)', 'Permite beber de qualquer lado da borda sem derramar, auxiliando no desenvolvimento motor oral da criança.']);
                $insBaby->execute(['Espelho Retrovisor para Banco Traseiro de Carro', 'Passeio', 'Recém-nascido a 3 anos', 'Ajuste 360° Anticaída', 'Plástico ABS e Espelho Acrílico Inquebrável', 'Pano seco', 'Sem peças pequenas', 49.90, 14.00, 'Espelho Carro + Protetor Solar Janela | Kit Viagem Bebê (Espelho + Organizador de Banco) | Espelho Carro Duplo', 'Todas as faixas (Acessível)', 'Permite monitorar o bebê no bebê conforto de costas para os pais enquanto dirigem, com total segurança e sem distrações.']);
            }
        } catch (\Exception $e) {
            // Ignore seeding errors
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

        // Alter users table to add API configuration fields if missing
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN openai_api_key TEXT DEFAULT NULL;");
        } catch (\Exception $e) {}
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN gemini_api_key TEXT DEFAULT NULL;");
        } catch (\Exception $e) {}
        try {
            self::$pdo->exec("ALTER TABLE users ADD COLUMN ai_provider TEXT DEFAULT 'local';");
        } catch (\Exception $e) {}
    }

    /**
     * Creates keyword_trends table if not exist and seeds initial keywords.
     */
    public static function checkAndCreateKeywordTrendsTable(PDO $db): void {
        $dbType = self::$driverType;
        $aiKeyType = $dbType === 'sqlite' ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
        $textType = $dbType === 'sqlite' ? 'TEXT' : 'VARCHAR(255)';
        $doubleType = $dbType === 'sqlite' ? 'REAL' : 'DOUBLE';
        $timestampType = $dbType === 'sqlite' ? 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP';

        // 1. Create table
        $db->exec("CREATE TABLE IF NOT EXISTS keyword_trends (
            id {$aiKeyType},
            marketplace {$textType} NOT NULL,
            keyword {$textType} NOT NULL,
            volume INTEGER DEFAULT 0,
            cpc_cpm {$doubleType} DEFAULT 0.0,
            growth {$textType} DEFAULT '+0%',
            competition_ctr {$textType} DEFAULT 'Média',
            category {$textType} DEFAULT 'Geral',
            last_updated {$timestampType},
            UNIQUE(marketplace, keyword)
        )");

        // 2. Check if table is empty
        $count = (int)$db->query("SELECT COUNT(*) FROM keyword_trends")->fetchColumn();
        if ($count > 0) {
            return;
        }

        // 3. Seed initial keywords (150 keywords total - 50 per marketplace)
        $mlKeywords = [
            'garrafa termica stanley' => [12000, 1.25, '+45%', 'Alta', 'Casa & Cozinha'],
            'mini liquidificador portatil' => [35000, 0.45, '+120%', 'Média', 'Casa & Cozinha'],
            'organizador de gavetas' => [15000, 0.25, '+35%', 'Baixa', 'Casa & Cozinha'],
            'fone de ouvido bluetooth sem fio' => [95000, 1.80, '+65%', 'Alta', 'Eletrônicos'],
            'ring light de mesa led' => [25000, 0.65, '-10%', 'Média', 'Eletrônicos'],
            'smartwatch relogio inteligente' => [75000, 1.40, '+80%', 'Alta', 'Eletrônicos'],
            'suporte veicular magnético' => [18000, 0.35, '+25%', 'Baixa', 'Eletrônicos'],
            'carregador sem fio inducao' => [22000, 0.85, '+50%', 'Média', 'Eletrônicos'],
            'kit ferramentas completo profissional' => [8000, 1.20, '+15%', 'Alta', 'Geral'],
            'central multimidia universal android' => [12000, 1.50, '+30%', 'Alta', 'Eletrônicos'],
            'cameras de seguranca wifi kit' => [14000, 1.10, '+40%', 'Média', 'Eletrônicos'],
            'lampada led automotiva par' => [9000, 0.40, '+10%', 'Baixa', 'Geral'],
            'par de palhetas limpador' => [5000, 0.20, '+5%', 'Baixa', 'Geral'],
            'capa de chuva motoqueiro moto' => [11000, 0.30, '+15%', 'Baixa', 'Geral'],
            'luva de academia musculacao' => [7000, 0.25, '+12%', 'Baixa', 'Esportes & Saúde'],
            'colchonete ginastica fitness' => [6000, 0.35, '+8%', 'Baixa', 'Esportes & Saúde'],
            'roda abdominal exercicio' => [4000, 0.30, '+5%', 'Baixa', 'Esportes & Saúde'],
            'faixa elastica mini band' => [9000, 0.18, '+22%', 'Média', 'Esportes & Saúde'],
            'corda de pular rolamento' => [5000, 0.22, '+10%', 'Baixa', 'Esportes & Saúde'],
            'balança de bioimpedancia digital' => [16000, 0.60, '+55%', 'Média', 'Esportes & Saúde'],
            'tensimetro aparelho pressao' => [12000, 0.70, '+20%', 'Média', 'Esportes & Saúde'],
            'termometro digital infravermelho' => [6000, 0.45, '-5%', 'Baixa', 'Esportes & Saúde'],
            'oximetro de pulso portatil' => [8000, 0.50, '+10%', 'Baixa', 'Esportes & Saúde'],
            'nebulizador inalador portatil' => [14000, 0.65, '+85%', 'Média', 'Esportes & Saúde'],
            'escova de dentes eletrica' => [15000, 0.75, '+35%', 'Média', 'Moda & Beleza'],
            'barbeador eletrico recarregavel' => [28000, 0.55, '+60%', 'Média', 'Moda & Beleza'],
            'aparador de pelos nariz' => [12000, 0.20, '+18%', 'Baixa', 'Moda & Beleza'],
            'secador de cabelo profissional' => [24000, 1.10, '+25%', 'Alta', 'Moda & Beleza'],
            'chapinha de cabelo ceramica' => [18000, 0.95, '+15%', 'Média', 'Moda & Beleza'],
            'maquina de costura portatil' => [7000, 0.60, '+8%', 'Baixa', 'Casa & Cozinha'],
            'ferro de passar a vapor' => [13000, 0.40, '+5%', 'Baixa', 'Casa & Cozinha'],
            'aspirador de po vertical' => [32000, 0.85, '+75%', 'Alta', 'Casa & Cozinha'],
            'robo aspirador inteligente' => [45000, 1.30, '+110%', 'Alta', 'Eletrônicos'],
            'mop giratorio com balde' => [27000, 0.50, '+50%', 'Média', 'Casa & Cozinha'],
            'vaporizador de roupas portatil' => [19000, 0.75, '+90%', 'Média', 'Casa & Cozinha'],
            'balança comercial 40kg' => [5000, 0.80, '+10%', 'Média', 'Geral'],
            'maquina de cartao mercado' => [15000, 1.40, '+20%', 'Alta', 'Eletrônicos'],
            'impressora termica etiqueta' => [11000, 0.90, '+65%', 'Média', 'Eletrônicos'],
            'leitor codigo de barras' => [4000, 0.55, '+8%', 'Baixa', 'Eletrônicos'],
            'gaveta de dinheiro organizadora' => [3000, 0.45, '+5%', 'Baixa', 'Geral'],
            'bobina termica 80x40' => [8000, 0.15, '+12%', 'Baixa', 'Geral'],
            'etiqueta adesiva termica' => [9000, 0.18, '+25%', 'Baixa', 'Geral'],
            'envelope de segurança correios' => [15000, 0.22, '+30%', 'Média', 'Geral'],
            'fita adesiva larga transparente' => [7000, 0.12, '+8%', 'Baixa', 'Geral'],
            'plastico bolha rolo 50m' => [6000, 0.30, '+10%', 'Baixa', 'Geral'],
            'caixa de papelão mudança' => [12000, 0.28, '+15%', 'Baixa', 'Geral'],
            'organizador de cabos velcro' => [8000, 0.15, '+20%', 'Baixa', 'Eletrônicos'],
            'filtro de linha 6 tomadas' => [14000, 0.35, '+18%', 'Baixa', 'Eletrônicos'],
            'extensão eletrica 5 metros' => [9000, 0.25, '+12%', 'Baixa', 'Geral'],
            'adaptador HDMI para VGA' => [16000, 0.30, '+15%', 'Média', 'Eletrônicos']
        ];

        $shopeeKeywords = [
            'achadinhos da shopee' => [250000, 0.12, '+180%', 'Alta', 'Geral'],
            'organizador de maquiagem' => [45000, 0.18, '+65%', 'Média', 'Moda & Beleza'],
            'fone bluetooth sem fio' => [180000, 0.45, '+120%', 'Alta', 'Eletrônicos'],
            'garrafa motivacional 2l' => [95000, 0.08, '+250%', 'Alta', 'Casa & Cozinha'],
            'mini processador usb' => [55000, 0.15, '+95%', 'Média', 'Casa & Cozinha'],
            'copo stanley termico' => [120000, 0.35, '+150%', 'Alta', 'Casa & Cozinha'],
            'relogio masculino smart' => [85000, 0.28, '+75%', 'Média', 'Eletrônicos'],
            'mochila escolar impermeavel' => [65000, 0.22, '+110%', 'Alta', 'Geral'],
            'luminaria led decorativa' => [38000, 0.14, '+45%', 'Baixa', 'Casa & Cozinha'],
            'tapete para banheiro' => [28000, 0.09, '+20%', 'Baixa', 'Casa & Cozinha'],
            'escova secadora rotativa' => [42000, 0.32, '+55%', 'Média', 'Moda & Beleza'],
            'maquina de cortar cabelo' => [58000, 0.18, '+40%', 'Média', 'Moda & Beleza'],
            'meia sapatilha antiderrapante' => [15000, 0.08, '+15%', 'Baixa', 'Moda & Beleza'],
            'bolsa feminina transversal' => [110000, 0.25, '+85%', 'Alta', 'Moda & Beleza'],
            'capinha de iphone' => [190000, 0.15, '+90%', 'Alta', 'Eletrônicos'],
            'pelicula de vidro temperado' => [45000, 0.08, '+25%', 'Média', 'Eletrônicos'],
            'kit pincel maquiagem' => [38000, 0.10, '+35%', 'Baixa', 'Moda & Beleza'],
            'colar feminino prata' => [62000, 0.18, '+50%', 'Média', 'Moda & Beleza'],
            'brinco de argola' => [25000, 0.09, '+15%', 'Baixa', 'Moda & Beleza'],
            'anel regulavel' => [18000, 0.07, '+22%', 'Baixa', 'Moda & Beleza'],
            'carteira masculina couro' => [32000, 0.16, '+30%', 'Média', 'Moda & Beleza'],
            'cinto masculino social' => [14000, 0.11, '+10%', 'Baixa', 'Moda & Beleza'],
            'oculos de sol quadrado' => [48000, 0.18, '+45%', 'Média', 'Moda & Beleza'],
            'corrente de prata masculina' => [29000, 0.20, '+35%', 'Média', 'Moda & Beleza'],
            'pulseira inteligente fit' => [35000, 0.22, '+50%', 'Média', 'Eletrônicos'],
            'carregador portatil powerbank' => [78000, 0.30, '+85%', 'Média', 'Eletrônicos'],
            'suporte de celular carro' => [29000, 0.12, '+25%', 'Baixa', 'Eletrônicos'],
            'cabo usb tipo c' => [88000, 0.08, '+40%', 'Média', 'Eletrônicos'],
            'adaptador tomada universal' => [18000, 0.15, '+18%', 'Baixa', 'Geral'],
            'fone de ouvido com fio' => [42000, 0.09, '+10%', 'Baixa', 'Eletrônicos'],
            'mouse sem fio recarregavel' => [52000, 0.18, '+70%', 'Média', 'Eletrônicos'],
            'teclado mecanico gamer' => [45000, 0.32, '+95%', 'Alta', 'Eletrônicos'],
            'pad mouse grande' => [28000, 0.14, '+40%', 'Baixa', 'Eletrônicos'],
            'caixa de som bluetooth bluetooth' => [72000, 0.28, '+60%', 'Média', 'Eletrônicos'],
            'microfone lapela sem fio' => [39000, 0.25, '+110%', 'Média', 'Eletrônicos'],
            'tripod celular selfie' => [22000, 0.12, '+30%', 'Baixa', 'Eletrônicos'],
            'anel de luz ring light' => [34000, 0.16, '-15%', 'Média', 'Eletrônicos'],
            'camera de segurança wifi' => [27000, 0.35, '+45%', 'Média', 'Eletrônicos'],
            'lampada inteligente rgb' => [31000, 0.24, '+70%', 'Média', 'Eletrônicos'],
            'difusor de aromas ultrassonico' => [46000, 0.18, '+80%', 'Média', 'Casa & Cozinha'],
            'mini ventilador portatil' => [39000, 0.11, '+120%', 'Média', 'Casa & Cozinha'],
            'esponja eletrica limpeza' => [19000, 0.13, '+35%', 'Baixa', 'Moda & Beleza'],
            'massageador corporal eletrico' => [28000, 0.16, '+50%', 'Baixa', 'Esportes & Saúde'],
            'balança digital cozinha' => [24000, 0.09, '+15%', 'Baixa', 'Casa & Cozinha'],
            'garrafa termica inox' => [33000, 0.15, '+30%', 'Baixa', 'Casa & Cozinha'],
            'marmita termica eletrica' => [16000, 0.22, '+45%', 'Baixa', 'Casa & Cozinha'],
            'escorredor de pratos pia' => [12000, 0.18, '+12%', 'Baixa', 'Casa & Cozinha'],
            'cabide de veludo fino' => [28000, 0.08, '+25%', 'Baixa', 'Geral'],
            'organizador de sapatos' => [19000, 0.14, '+18%', 'Baixa', 'Casa & Cozinha'],
            'caixa organizadora plastico' => [34000, 0.16, '+30%', 'Média', 'Casa & Cozinha']
        ];

        $tiktokKeywords = [
            '#achadinhos' => [12500000, 2.50, '1.8%', 'Alta', 'Geral'],
            'organizador acrilico giratorio' => [185000, 3.20, '1.5%', 'Média', 'Casa & Cozinha'],
            'fone bluetooth bluetooth' => [450000, 4.10, '1.3%', 'Alta', 'Eletrônicos'],
            'garrafa termica motivacional' => [680000, 2.20, '2.1%', 'Alta', 'Casa & Cozinha'],
            '#tiktokmademebuyit' => [18900000, 3.50, '2.4%', 'Alta', 'Geral'],
            'mini selador de embalagens' => [320000, 1.80, '1.9%', 'Média', 'Casa & Cozinha'],
            'removedor de fiapos eletrico' => [240000, 2.10, '1.6%', 'Baixa', 'Casa & Cozinha'],
            'mini projetor portatil 4k' => [890000, 4.80, '2.6%', 'Alta', 'Eletrônicos'],
            'luminaria projetor astronauta' => [540000, 3.10, '2.2%', 'Média', 'Decoração'],
            'copo termico com tampa' => [380000, 2.90, '1.4%', 'Média', 'Casa & Cozinha'],
            'mini massageador de pescoço' => [410000, 2.40, '1.7%', 'Média', 'Esportes & Saúde'],
            'maquina de mini donuts' => [295000, 3.50, '2.3%', 'Média', 'Casa & Cozinha'],
            'mini processador de alho' => [180000, 1.50, '1.3%', 'Baixa', 'Casa & Cozinha'],
            'rolo tirar pelos lavavel' => [120000, 1.60, '1.2%', 'Baixa', 'Pet'],
            'esponja magica melamina' => [95000, 1.40, '1.1%', 'Baixa', 'Casa & Cozinha'],
            'tira pelos de roupas pet' => [135000, 1.80, '1.4%', 'Baixa', 'Pet'],
            'escova de limpeza eletrica' => [460000, 2.80, '1.8%', 'Média', 'Casa & Cozinha'],
            'mop limpa vidros triangular' => [210000, 2.10, '1.5%', 'Baixa', 'Casa & Cozinha'],
            'organizador de geladeira acrilico' => [165000, 2.40, '1.3%', 'Baixa', 'Casa & Cozinha'],
            'dispenser de detergente automatico' => [140000, 2.20, '1.4%', 'Baixa', 'Casa & Cozinha'],
            'porta temperos giratorio' => [115000, 1.90, '1.2%', 'Baixa', 'Casa & Cozinha'],
            'mini maquina de lavar roupa' => [380000, 3.10, '2.0%', 'Média', 'Casa & Cozinha'],
            'varal retratil de parede' => [125000, 1.80, '1.3%', 'Baixa', 'Casa & Cozinha'],
            'organizador de maquiagem led' => [195000, 2.70, '1.6%', 'Média', 'Moda & Beleza'],
            'espelho com luz touch' => [280000, 3.40, '1.9%', 'Média', 'Moda & Beleza'],
            'kit skin care facial' => [720000, 4.20, '2.1%', 'Alta', 'Moda & Beleza'],
            'saboneteira automatica sensor' => [110000, 2.10, '1.2%', 'Baixa', 'Casa & Cozinha'],
            'suporte de celular articulado' => [160000, 1.80, '1.3%', 'Baixa', 'Eletrônicos'],
            'luminaria de leitura clip' => [90000, 1.50, '1.1%', 'Baixa', 'Decoração'],
            'quadro lousa luminosa led' => [145000, 2.30, '1.5%', 'Baixa', 'Decoração'],
            'mini impressora de fotos bluetooth' => [340000, 3.80, '1.8%', 'Média', 'Eletrônicos'],
            'projetor de galaxia teto' => [480000, 3.40, '2.0%', 'Média', 'Decoração'],
            'fita led rgb 5m' => [230000, 2.20, '1.4%', 'Baixa', 'Eletrônicos'],
            'caixa de som bluetooth colorida' => [310000, 2.80, '1.7%', 'Média', 'Eletrônicos'],
            'fone gatinho com led' => [180000, 2.10, '1.3%', 'Baixa', 'Infantil'],
            'microfone infantil karaoke bluetooth' => [195000, 2.50, '1.5%', 'Baixa', 'Infantil'],
            'mini ar condicionado portatil' => [620000, 3.80, '2.2%', 'Média', 'Casa & Cozinha'],
            'humidificador de ar com light' => [340000, 2.40, '1.6%', 'Baixa', 'Decoração'],
            'vela aromatica perfumada' => [125000, 2.10, '1.2%', 'Baixa', 'Decoração'],
            'copo de vidro com canudo' => [280000, 1.90, '1.5%', 'Baixa', 'Casa & Cozinha'],
            'marmita termica de inox' => [160000, 2.60, '1.3%', 'Baixa', 'Casa & Cozinha'],
            'garrafa de agua infantil' => [220000, 2.10, '1.7%', 'Baixa', 'Infantil'],
            'lancheira termica escolar' => [180000, 2.30, '1.4%', 'Baixa', 'Infantil'],
            'mochila antifurto usb' => [420000, 3.20, '1.9%', 'Média', 'Geral'],
            'carteira slim automatica' => [190000, 2.40, '1.5%', 'Baixa', 'Geral'],
            'chaveiro multiuso ferramentas' => [75000, 1.60, '1.1%', 'Baixa', 'Geral'],
            'mini compressor de ar pneu' => [185000, 2.90, '1.4%', 'Baixa', 'Geral'],
            'suporte de tablet cama' => [140000, 2.10, '1.3%', 'Baixa', 'Eletrônicos'],
            'mouse pad ergonomico apoio' => [95000, 1.80, '1.2%', 'Baixa', 'Eletrônicos'],
            'suporte notebook articulado' => [28000, 2.40, '1.3%', 'Baixa', 'Eletrônicos']
        ];

        // 4. Batch Insert
        $db->beginTransaction();
        try {
            $stmt = $db->prepare("INSERT INTO keyword_trends (marketplace, keyword, volume, cpc_cpm, growth, competition_ctr, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($mlKeywords as $kw => $data) {
                $stmt->execute(['mercadolivre', $kw, $data[0], $data[1], $data[2], $data[3], $data[4]]);
            }
            foreach ($shopeeKeywords as $kw => $data) {
                $stmt->execute(['shopee', $kw, $data[0], $data[1], $data[2], $data[3], $data[4]]);
            }
            foreach ($tiktokKeywords as $kw => $data) {
                $stmt->execute(['tiktok', $kw, $data[0], $data[1], $data[2], $data[3], $data[4]]);
            }
            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
        }
    }
}
