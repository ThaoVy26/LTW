<?php
const DB_HOST = '127.0.0.1';
const DB_NAME = '52400092_52400313_52400041';
const DB_USER = 'root';
const DB_PASS = '';

// kết nối mysql 1 lần rồi dùng lại (PDO)
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('<h2 style="font-family:sans-serif">Không kết nối được cơ sở dữ liệu.</h2>'
                . '<p style="font-family:sans-serif">Hãy bật MySQL trong XAMPP và import '
                . '<code>database/01_schema.sql</code> rồi <code>database/02_seed.sql</code>.</p>');
        }
    }
    return $pdo;
}
