<?php

namespace MarryOn\Api\Database;

final class DatabaseManager {
	protected $dbConfig = [];

	protected $mysqli = null;

	/**
	 * Call this method to get singleton
	 *
	 * @return DatabaseManager
	 */
	public static function Instance() {
		static $inst = null;
		if ($inst === null) {
			$inst = new DatabaseManager();
		}

		return $inst;
	}

	/**
	 * Private ctor so nobody else can instance it
	 *
	 */
	private function __construct() {
		$config = include($_SERVER['DOCUMENT_ROOT'] . '/api/config/config.php');
		if ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === 'localhost') {
			$this->dbConfig = $config['local']['database'];
		} else {
			$this->dbConfig = $config['live']['database'];
		}
	}

	/**
	 * @return \mysqli
	 */
	public function connect() {
		$dbConfig = $this->dbConfig;
		$mysqli = new \mysqli($dbConfig["host"], $dbConfig["user"], $dbConfig["password"], $dbConfig["database"]);
		if ($mysqli->connect_errno) {
			die("Connection error: " . $mysqli->connect_error);
		}
		$this->mysqli = $mysqli;

		return $mysqli;
	}

}