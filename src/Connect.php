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
	
	private static $conn;
	
	protected static function makeConnection() {
		try {
			self::$conn = new PDO( "mysql:host=" . host . ':' . port . ';dbname=' . database, username, password );
			
			// set the PDO error mode to exception
			self::$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch ( PDOException $e ) {
			
			echo "Connection failed: " . $e->getMessage();
		}
	}
	
	
	/**
	 * @return mixed
	 */
	public static function getConn() {
		self::makeConnection();
		PDO:$conn=self::$conn;
		return $conn;
	}
	
}