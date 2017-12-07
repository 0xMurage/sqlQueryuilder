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
		//TODO check if the operator is correct
		//TODO add functionality for (and ,or) multiple where clauses
		
		
		if ( func_num_args() == 3 ) {
			$this->whereby = func_get_arg( 0 ) . ''
			                 . func_get_arg( 1 ) . '\''
			                 . func_get_arg( 2 . '\'' );
		} else if ( func_num_args() == 2 ) {
			$this->whereby = func_get_arg( 0 ) . ' = \''
			                 . func_get_arg( 1 ) . '\'';
		}
		else{
			$this->response["error"]=true;
			$this->response["message"]="Invalid parameters given in where function";
			echo json_encode($this->response);
			return;
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
		
		if ( ! empty( $this->whereby ) ) {
			
			$query = $query . ' WHERE ' . $this->whereby;
		}
		
		
		if ( ! empty( $limit ) ) {
			$query = $query . ' LIMIT ' . $limit;
		}
		
		return $this->pretty_return(
			$this->fetch( $query )
		);
	}
	
	/**
	 * @param $data
	 *
	 * @return string
	 */
	protected function pretty_return( $data ) {
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
				$this->response["message"]=$e->getMessage();
				echo json_encode($this->response);
				return;
			}
			try {
				$stm->execute();
			}catch (Exception $e){
				$this->response["error"]=true;
				$this->response["message"]=$e->getMessage();
				echo json_encode($this->response);
				return;
			}
			$data = null;
			// set the resulting array to associative
			$result = $stm->setFetchMode( PDO::FETCH_ASSOC );
			foreach ( new RecursiveArrayIterator( $stm->fetchAll() ) as $k => $v ) {
				$data[] = $v;
			}
			
			return $data;
			
		} catch ( PDOException $e ) {
			array_push( $this->error,
				$e->getCode(),
				$e->getMessage()
			);
			
		}
		
		return "error:";
	}
	
	/**
	 * @return string
	 */
	public function all() {
		$table = self::$table;
		if ( ! empty( $table ) ) {
			$query = /** @lang text */
				"SELECT * FROM {$table}";
			
			//execute the query and return the data or error message
			return $this->pretty_return(
				$this->fetch( $query )
			);
			
		}
		
		//TODO return an error here
	}
	
	public function insert( $values ) {
		// TODO sanitize the values
		try {
			if ( func_num_args() > 0 && ! is_array( $values ) ) {
				$this->values = array_merge( $this->values, func_get_args() );
			} else if ( is_array( $values ) ) {
				$this->values = $values;
			} else {
				//TODO throw an error of unrecognized parameters option
				throw new Exception( "unrecognized parameter options in the insert values" );
			}
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["message"] = $e->getMessage();
			echo $this->response;
			
			return;
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
				//TODO throw invalid characters error
				
				$this->response["error"]   = true;
				$this->response["message"] = "Unrecognized characters. Please refer to documentation on how to insert..";
				echo json_encode( $this->response );
				
				return;
				
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
				throw new Exception( "Columns count does not equal values count" );
			}
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["message"] = $e->getMessage();
			echo json_encode( $this->response );
			
			return;
		}
		
		return $this->doInsert();
	}
	
	/**
	 * Perform the actusl database insert
	 * @return string
	 */
	protected function doInsert() {
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
				$this->response["message"]=$e->getMessage();
				echo json_encode($this->response);
				return;
			}
		
		try {
			$stm->execute( $this->values );
			
			$this->response["error"]   = false;
			$this->response["message"] = "Record insert successfully";
			
			return json_encode( $this->response );
		}catch (Exception $e){
			$this->response["error"]=true;
			$this->response["message"]=$e->getMessage();
			echo json_encode($this->response);
			return;
		}
	}
	
	public function truncate() {
		//todo validate the table name
		
		$sql = "TRUNCATE TABLE " . self::$table;
		try {
			$this->exec( $sql );
			
			$this->response["error"]   = false;
			$this->response["message"] = "Table truncated successfully";
			
			return json_encode( $this->response );
		} catch ( Exception $e ) {
			$this->response["error"]   = true;
			$this->response["message"] = $e->getMessage();
			
			return json_encode( $this->response );
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
			$this->response["message"] = $e->getMessage();
			echo json_encode( $this->response );
			
			return;
		}
	}
	
	public function drop() {
		//todo validate the table name
		
		$sql = /** @lang text */
			"DROP TABLE " . self::$table;
		$this->exec( $sql );
		
	}
	
	protected function formatValues( $values ) {
		//TODO : sanitize the data
		
	}
}