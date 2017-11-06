<?php
/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 29-Oct-17
 * Time: 09:42
 */

namespace QueryBuilder\db;

use PDO;
use PDOException;

require 'config.php';

class Connect {
	
	private static $conn = null;
	
	/**
	 * Connect constructor.
	 */
	public function __construct() {
		self::makeConnection();
	}
	
	public static function makeConnection() {
		try {
			self::$conn = new PDO( "host:mysql=" . host . ':' . port . ';dbname=' . database, username, password );
			
			// set the PDO error mode to exception
			self::$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch ( PDOException $e ) {
			
			echo "Connection failed: " . $e->getMessage();
		}
	}
	
	protected static function connection() {
		return self::$conn;
	}
	
}