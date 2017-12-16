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


class Connect {
	
	private static $conn;

	protected static function makeConnection() {
		 $dotenv = new Dotenv\Dotenv(__DIR__);
		 $dotenv->load();
		 /*ENSURE THE FOLLOWING ARE PROVIDEND*/
		$dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME','DB_USERNAME', 'DB_PASSWORD']);
		
		/*CHECK TO ENSURE THERE ARE NOT EMPTY*/
		$dotenv->required('DB_HOST')->notEmpty();
		$dotenv->required('DB_PORT')->notEmpty();
		$dotenv->required('DB_NAME')->notEmpty();
		
		/*CHECK IF PORT IS AN INTEGER*/
		$dotenv->required('DB_PORT')->isInteger();
		
		
		
		try {
			self::$conn = new PDO( "mysql:host=" . $_SERVER["DB_HOST"] . ':' . $_SERVER["DB_PORT"]. ';dbname=' .  $_SERVER["DB_NAME"], $_SERVER["DB_USERNAME"], $_SERVER["DB_PASSWORD"] );
			
			// set the PDO error mode to exception
			self::$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		} catch ( PDOException $e ) {
			
			die("Connection failed: " . $e->getCode());
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