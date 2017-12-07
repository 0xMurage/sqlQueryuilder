<?php

use QueryBuilder\db\Connect;

/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 29-Oct-17
 * Time: 09:42
 */
class Builder {
	
	protected static $table;
	protected $columns;
	protected $values = [];
	protected $whereby;
	protected $order;
	protected $limit;
	protected $condition=['<','>','<>','!=','<=','>=','='];
	
	protected $response = array( "error" => false );
	

	
	
	public static function table( $table ) {
		//TODO sanitize the table name
		self::$table = $table;
		
		return new static;
	}
	
	/**
	 * @param string $columns
	 *
	 * @return $this
	 */
	public function select( $columns = "" ) {
		//TODO sanitize the columns
		
		//check if columns were passed as individual string parameters
		
		if ( func_num_args() > 1 ) {
			$this->columns = $this->columnize( func_get_args() );
		} else {
			//check if a simgle array of columns was passed(a hack)
			$this->columns = is_array( $columns ) ? $this->columnize( $columns )
				: $columns;
		}
		
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
	public function where( $params ) {
		//TODO sanitize the parameters
		//TODO add functionality for (and ,or) multiple where clauses
		
		if ( func_num_args() == 3 ) {
			
			$operator=func_get_arg(1);
			if(is_numeric(array_search($operator,$this->condition))) {
				$this->whereby = func_get_arg( 0 )
				                 . $operator . '\''
				                 . func_get_arg( 2 ) . '\'';
			}else{
				$this->response["error"]=true;
				$this->response["response"]="Invalid condition provided in where function";
			}
		} else if ( func_num_args() == 2 ) {
			$this->whereby = func_get_arg( 0 ) . ' = \''
			                 . func_get_arg( 1 ) . '\'';
		}
		else{
			$this->response["error"]=true;
			$this->response["response"]="Invalid parameters provided in where function";
		}
		
		return $this;
	}
	
	
	public function get( $limit = 0,$offet=0 ) {
		
		//check if there is an error
		if($this->response['error']==true){
			return $this->exit();
		}
		
		//check if the limit is a number
		if(!is_numeric($limit)){
			$this->response["error"]   = false;
			$this->response["response"] = "Parameter limit should be numeric function get()";
			return $this->exit($this->response);
		}
		
		//check if the offsel is a number
		if(!is_numeric($offet)){
			$this->response["error"]   = false;
			$this->response["response"] = "Parameter offset should be numeric in function get()";
			return $this->exit($this->response);
		}
		
		$table_name = self::$table;
		
		/* if no column passed as param, select all	 */
		$columns = empty( $this->columns ) ? "*" : $this->columns;
		
		$query = /** @lang text */
			"SELECT {$columns} FROM {$table_name}";
		
		if ( ! empty( $this->whereby ) ) {
			
			$query = $query . ' WHERE ' . $this->whereby;
		}
		
		
		if ( ! empty( $limit ) ) {
			$query = $query . ' LIMIT ' . $limit;
		}
		if ( ! empty( $offset ) ) {
			$query = $query . ' OFFSET ' . $offset;
		}
		
		return $this->exit(
			$this->fetch( $query )
		);
	}
	

	/**
	 * @param $data
	 *
	 * @return string
	 */
	protected function exit( $data=null ) {
		if($this->response["error"]!=false){
			return json_encode($this->response);
		}
		return json_encode( $data );
		
	}
	
	/**
	 * Executes a query that returns data
	 *
	 * @param $sql
	 */
	protected function fetch( $sql ) {
		//TODO sanitize the sql query
		try {
			try {
				$stm = Connect::getConn()->prepare( $sql );
			}catch (Exception $e){
				$this->response["error"]=true;
				$this->response["response"]=$e->getMessage();
			}
			try {
				$stm->execute();
			}catch (Exception $e){
				$this->response["error"]=true;
				$this->response["response"]=$e->getMessage();
				
			}
			$data = null;
			// set the resulting array to associative
			$result = $stm->setFetchMode( PDO::FETCH_ASSOC );
			foreach ( new RecursiveArrayIterator( $stm->fetchAll() ) as $k => $v ) {
				$data[] = $v;
			}
			
			if($data==null) {
				$this->response["error"]=false;
				$this->response["response"]="No data found";
				return $this->response;
			}
			$this->response["error"]=false;
			$this->response["response"]=$data;
			return $this->response;
			
			
		} catch ( PDOException $e ) {
			$this->response["error"]=true;
			$this->response["response"]=$e->getMessage();
			
		}
		
		return $this->exit();
	}
	
	/**
	 * @return string
	 */
	public function all() {
		$table = trim(self::$table);
		if ( ! empty( $table ) ) {
			$query = /** @lang text */
				"SELECT * FROM {$table}";
			
			//execute the query and return the data or error message
			return $this->exit(
				$this->fetch( $query )
			);
			
		}else {
			$this->response["error"]   = true;
			$this->response["response"] = "Table name cannot be empty";
			return $this->exit( null );
		}
	}
	
	public function insert( $values ) {
		// TODO sanitize the values
		try {
			if ( func_num_args() > 0 && ! is_array( $values ) ) {
				$this->values = array_merge( $this->values, func_get_args() );
			} else if ( is_array( $values ) ) {
				$this->values = $values;
			} else {
				throw new Exception( "unrecognized parameter options in the insert values" );
			}
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["response"] = $e->getMessage();
		}
		
		return $this;
	}
	
	
	public function into( $columns ) {
		//if columns count does not match values count, throw an error.
		
		$valuesCount    = count( $this->values );
		$colStringCount = 0;
		if ( is_string( $columns ) ) {
			try {
				$colStringCount = count(
					explode( ',', $columns )
				);
			} catch ( Exception $e ) {
				
				$this->response["error"]   = true;
				$this->response["response"] = "Unrecognized characters. Please refer to documentation on how to insert..";
				
			}
		}
		try {
			if ( func_num_args() > 1 && func_num_args() == $valuesCount ) {
				$this->columns = $this->columnize( func_get_args() );
			} else if ( is_array( $columns ) && count( $columns ) == $valuesCount ) {
				$this->columns = $this->columnize( $columns );
			} else if ( $colStringCount == $valuesCount ) {
				$this->columns = $columns;
			} else {
				//throw an error (columns count not equal to values count)
				throw new Exception(  );
			}
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["response"] = "operation unsuccessful.Columns count does not equal the values count";
		}
		
		return $this->doInsert();
	}
	
	/**
	 * Perform the actusl database insert
	 * @return string
	 */
	protected function doInsert() {
		//check if there is an error from previous function execution
		if($this->response["error"]==true){
			return $this->exit( );
		}
		//convert each columns to ? parameter
		$columnParam = array_map( function () {
			return '?';
		}, $this->values );
		
		
		$sql = /** @lang sql */
			'INSERT INTO ' . self::$table .
			' (' . $this->columns .
			') VALUES(' . implode( ',', $columnParam ) . ')';
			
			try {
				$stm = Connect::getConn()->prepare( $sql );
			}catch (Exception $e){
				$this->response["error"]=true;
				$this->response["response"]=$e->getMessage();
				return $this->exit();
			}
		
		try {
			$stm->execute( $this->values );
			
			$this->response["error"]   = false;
			$this->response["response"] = "Record insert successfully";
			
			return $this->exit( $this->response );
		}catch (Exception $e){
			$this->response["error"]=true;
			$this->response["response"]=$e->getMessage();
			return $this->exit( $this->response );
		}
	}
	
	public function truncate() {
		//todo validate the table name
		
		$sql = "TRUNCATE TABLE " . self::$table;
		try {
			$this->exec( $sql );
			
			$this->response["error"]   = false;
			$this->response["response"] = "Table truncated successfully";
			
			return $this->exit( $this->response );
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["response"] = $e->getMessage();
			
			return $this->exit( $this->response );
		}
	}
	
	/**
	 * Executes a query that does not return any results
	 *
	 * @param $query
	 */
	protected function exec( $query ) {
		try {
			Connect::getConn()->exec( $query );
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["response"] = $e->getMessage();
		}
	}
	
	public function drop() {
		//todo validate the table name
		
		$sql = /** @lang text */
			"DROP TABLE " . self::$table;
			try {
				$this->exec( $sql );
				$this->response["error"]   = false;
				$this->response["response"] = "Table deleted successfully";
				return $this->exit($this->response);
			}catch (Exception $e){
				$this->response["error"]   = true;
				$this->response["response"] = $e->getMessage();
				return $this->exit($this->response);
			}
		
	}
	
	protected function formatValues( $values ) {
		//TODO : sanitize the data
		
	}
}