<?php
/**
 * All rights reserved.
 * User: Dread Pirate Roberts
 * Date: 08-Nov-17
 * Time: 08:49
 */

class Eloquent {
	
	//retrieves all records
	public static function all(){
	
	}
	
	
	public function latest() {
		$this->order = "ASC";
		
		return $this;
	}
	
	/**
	 * get a specific value where id= ?
	 *
	 * @param $id
	 */
	public function find( $id ) {
		//TODO sanitize the parameter
		
	}
}