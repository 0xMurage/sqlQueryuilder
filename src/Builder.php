<?php
/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 29-Oct-17
 * Time: 09:42
 */
namespace QueryBuilder\db;

use PDOException;

class Builder {
	
	protected static $table;
	protected $columns;
	protected $whereby;
	protected $order;
	protected $limit;
	
	public static function table( $table ) {
		self::$table = $table;
		
		return new static;
	}
	
	public function select( $columns = "" ) {
		$this->columns = is_array( $columns ) ? $this->columnize( $columns ) //check if an array of columns was passed(a hack)
				: $columns;
		
		return $this;
	}
	
	/**
	 * Convert an array of column names into a comma delimited string.
	 *
	 * @param  array $columns
	 *
	 * @return string
	 */
	public function columnize( array $columns ) {
		return implode( ",", array_values( $columns ) );
	}
	
	

	public function where( $objectColumns ) {
		$this->whereby = $objectColumns;
		
		return $this;
	}
	
	public function latest(){
		$this->order="ASC";
		return $this;
	}
	
	/**
	 * get a specific value where id= ?
	 * @param $id
	 */
	public function find($id){
	
	}
	
	public function get( $limit = 500 ) {
		
		$table_name = self::$table;

		/* if no column passed as param, select all	 */
		$columns=empty( $this->columns ) ? "*" : $this->columns;
		
		
		
		$query = /** @lang text */
			"SELECT {$columns} FROM {$table_name}";
		
		
		echo $query;
	}
	
	
	/**
	 * Executes a query
	 * @param $sql
	 */
	protected function exec($sql){
		try{
			
		}catch (PDOException $e){
		
		}
	}
	
}