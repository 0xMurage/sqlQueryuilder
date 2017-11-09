<?php

use QueryBuilder\db\Connect;

/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 29-Oct-17
 * Time: 09:42
 */
//namespace QueryBuilder\db;

//use PDOException;

class Builder {
	
	protected static $table;
	protected $columns;
	protected $whereby;
	protected $order;
	protected $limit;
	protected $error=[];
	
	
	public static function table( $table ) {
		//TODO sanitize the table name
		self::$table = $table;
		
		return new static;
	}
	
	public function select( $columns = "" ) {
		//TODO sanitize the columns
		//check if an array of columns was passed(a hack)
		$this->columns = is_array( $columns ) ? $this->columnize( $columns )
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
	protected function columnize( array $columns ) {
		//TODO sanitize the columns
		return implode( ",", array_values( $columns ) );
	}
	
	
	/**
	 * $column, $operator = "", $value
	 * @return $this
	 */
	public function where($params) {
		//TODO sanitize the parameters
		//TODO check if the operator is correct
		//TODO add functionality for (and ,or) multiple where clauses
		
		
		if(func_num_args()==3){
			$this->whereby = func_get_arg(0) .''
			                 .func_get_arg(1).''
			                 . func_get_arg(2);
		}
		else
		if(func_num_args()==2) {
			$this->whereby = func_get_arg(0) . ' = '
			                 . func_get_arg(1) ;
		}
		
		//TODO: else return an error of invalid parameters
		
		return $this;
	}
	
	
	public function get( $limit = "" ) {
		//TODO check if the limit is a number
		$table_name = self::$table;
		
		/* if no column passed as param, select all	 */
		$columns = empty( $this->columns ) ? "*" : $this->columns;
		
		$query = /** @lang text */
			"SELECT {$columns} FROM {$table_name}";
		
			if(!empty($this->whereby)){
				$query=$query.' WHERE '.$this->whereby;
			}
			
			
			if(!empty($limit)){
				$query=$query.' LIMIT '.$limit;
			}
			return $this->pretty_return(
				$this->fetch($query)
			);
	}
	
	/**
	 * @return string
	 */
	public function all( )
	{
		$table=self::$table;
		if(!empty($table)) {
			$query = /** @lang text */
				"SELECT * FROM {$table}";
			
			//execute the query and return the data or error message
			return $this->pretty_return(
				$this->fetch($query)
			);
			
		}
		
		//TODO return an error here
	}
	/**
	 * Executes a query that returns data
	 *
	 * @param $sql
	 */
	protected function fetch( $sql ) {
		//TODO sanitize the sql query
		try {
		
			$stm=Connect::getConn()->prepare($sql);
			$stm->execute();
			$data=null;
			// set the resulting array to associative
			$result = $stm->setFetchMode( PDO::FETCH_ASSOC );
			foreach ( new RecursiveArrayIterator( $stm->fetchAll() ) as $k => $v ) {
				$data[] = $v;
			}
			
			 return $data;
			
		} catch ( PDOException $e ) {
			array_push($this->error,
				$e->getCode(),
				$e->getMessage()
			);
			
		}
	}
	

	
	protected function pretty_return($data){
		if(!empty($this->error)){
			return (json_encode($this->error));
		}
		
		return json_encode($data);

	}
	
}