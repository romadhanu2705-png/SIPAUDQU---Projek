<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
	private static string $host = 'localhost';
	private static string $db   = 'projek';
	private static string $user = 'root';
	private static string $pass = '';
	private static string $charset = 'utf8mb4';

	/** @var PDO|null */
	private static $pdo = null;

	public static function setConfig(string $host, string $db, string $user, string $pass, string $charset = 'utf8mb4'): void
	{
		self::$host = $host;
		self::$db = $db;
		self::$user = $user;
		self::$pass = $pass;
		self::$charset = $charset;
		self::$pdo = null;
	}

	public static function connect(): PDO
	{
		if (self::$pdo instanceof PDO) {
			return self::$pdo;
		}

		$dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', self::$host, self::$db, self::$charset);

		try {
			$options = [
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				PDO::ATTR_EMULATE_PREPARES => false,
			];

			self::$pdo = new PDO($dsn, self::$user, self::$pass, $options);
			return self::$pdo;
		} catch (PDOException $e) {
			http_response_code(500);
			exit('Database connection failed: ' . $e->getMessage());
		}
	}
}

// Example usage:
// \App\Config\Database::setConfig('localhost','nama_database','root','password');
// $pdo = \App\Config\Database::connect();