<?php
/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 29-Oct-17
 * Time: 09:42
 */

class Builder {
	
	protected static $table;
	protected $columns;
	protected $whereby;
	protected $limit;
	
	public static function table( $table ) {
		self::$table = $table;
		
		return new static;
	}
	
	public function select( $columns = "" ) {
		$this->columns =
			empty( $columns ) ? "*" //if no column passed as param, select all
				: is_array( $columns ) ? $this->columnize( $columns ) //check if an array of columns was passed(a hack)
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
	
	public function get( $limit = 500 ) {
		
		$table_name = self::$table;
		$limit      =
			
			//call the sql grammar builder
		
		$query = /** @lang text */
			"SELECT {$this->columns} FROM {$table_name}";
		
		
		echo $query;
	}
	
	
}