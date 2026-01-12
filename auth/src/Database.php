<?php
/**
 * auth/src/Database.php
 *
 * What this file does:
 * - Creates a MySQL database connection using MySQLi OOP style.
 * - Other classes reuse this connection.
 *
 * Why:
 * - Avoid connecting to the database many times on the same request.
 *
 * How to use:
 * - $db = new AuthModule\Database($config);
 * - $conn = $db->conn();  // gives mysqli connection
 */

declare(strict_types=1);

namespace AuthModule;

use mysqli;

final class Database
{
    /** @var mysqli The actual MySQLi connection object */
    private mysqli $db;

    /**
     * Step 1: Create the DB connection from config.php values.
     * If connection fails, MySQLi will throw an exception (we enabled strict mode).
     */
    public function __construct(array $config)
    {
        $c = $config['db'];

        // Make MySQLi throw exceptions instead of silent warnings.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Connect: host, user, pass, database name, port
        // $this->db = new mysqli(
        //     (string) $c['host'],
        //     (string) $c['user'],
        //     (string) $c['pass'],
        //     (string) $c['name'],
        //     (int) $c['port']
        // );

        $this->db = new mysqli(
            '127.0.0.1',
            'root',
            '',
            'test1',
            8111
        );

        // Step 2: Set DB charset so text is stored correctly.
        $this->db->set_charset((string) $c['charset']);
    }

    /**
     * Get the mysqli connection.
     * Other classes call this to run queries.
     */
    public function conn(): mysqli
    {
        return $this->db;
    }
}
