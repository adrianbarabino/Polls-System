<?php



class Db {
	public $raw;

    public function __construct() {
    	global $db;
    	$this->raw = $db;
    }


	public function insertToDB($table, $array_values)
	{

		$fields;
		$values;
		foreach ($array_values as $key => $value) {
			$fields = $fields.$key.", ";
			$values = $values."'".$value."', ";
		}
		$fields = substr($fields, 0, -2);
		$values = substr($values, 0, -2);
		$sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", $table, $fields, $values);

		if($result = $this->raw->query($sql)){
			return $this->raw->insert_id;
		}else{
			return false;
			die("ERROR in the query: ".$this->raw->error);
		}
	}

	public function updateToDB($table, $array_values, $where_array)
	{
		// Where array example: array (field, operator, value);

		$updateString;

		foreach ($array_values as $key => $value) {
			$updateString = $updateString."`".$key."` = '".$value."', ";
		}
		$updateString = substr($updateString, 0, -2);
		$sql = sprintf("UPDATE `%s` SET %s WHERE `%s` %s %s", $table, $updateString, $where_array[0], $where_array[1], $where_array[2]);
		if($result = $this->raw->query($sql)){
			return true;

		}else{
			return false;

			die("ERROR in the query: ".$this->raw->error);
		}
	}
	public function simpleSelect($table, $fields, $where_array)
	{
		// Where array example: array (table, fields, where array);

		$sql = sprintf("SELECT %s FROM %s WHERE `%s` %s '%s'", $fields, $table, $where_array[0], $where_array[1], $where_array[2]);
		
		if($result = $this->raw->query($sql)){
			return $result;

		}else{
			die("ERROR in the query: ".$this->raw->error);
			return false;

		}
	}

	public function advancedSelect($table, $fields_array, $where_array, $join_array = NULL, $limit = NULL)
	{
		// Example of advencedSelect:

		// $this->advancedSelect(
		// 	"books", 
		// 	array("books.name", "books.id as BookID", "author.id"),
		// 	array( 
		// 		array("books.name", "LIKE", "wars") 
		// 		),
		// 	array(
		// 		array("INNER", "author", "book.id_author", "=", "author.id")
		// 		)
		// 	);

		// The example returns: 
		// SELECT `books.name, books.id as BookID, author.id` FROM books 
		// INNER JOIN author ON `book.id_author` = 'author.id' 
		// WHERE `books.name` LIKE 'wars'



		// Looking inside Fields Array:

		$fields;

		foreach ($fields_array as $key) {
			$fields = $fields."".$key.", ";
		}
		$fields = substr($fields, 0, -2);

		// Looking inside Where Array

		if(count($where_array) > 1){

			$whereString;

			foreach ($where_array as $key) {
				$whereString .= sprintf("%s %s '%s' AND", $key[0], $key[1], $key[2]);
			}
			$whereString .= substr($whereString, 0, -3);

		}else{
			$key = $where_array[0];
			$whereString .= sprintf("%s %s '%s' ", $key[0], $key[1], $key[2]);
		}

		// Looking inside Join Array
		// Example of Join Array = array( array("INNER", "author", "book.id_author", "=", "author.id") );


			$joinString;

			$i = 0;
			foreach ($join_array as $key) {
				if($i = 0){

					$joinString .= sprintf("%s JOIN %s ON %s %s %s ", $key[0], $key[1], $key[2], $key[3], $key[4]);
				}else{

					$joinString .= sprintf("%s JOIN %s ON %s %s %s ", $key[0], $key[1], $key[2], $key[3], $key[4]);
				}
				$i++;
			}

			



		$sql = sprintf("SELECT %s FROM %s %s WHERE %s", $fields, $table, $joinString, $whereString);
		if($result = $this->raw->query($sql)){
			return $result;

		}else{
			die("ERROR in the query: ".$this->raw->error);
			return false;

		}
	}


	public function deleteToDB($table, $where_array)
	{
		$sql = sprintf("DELETE FROM `%s` WHERE %s %s '%s'", $table, $where_array[0], $where_array[1], $where_array[2]);
		if($result = $this->raw->query($sql)){
			return true;
		}else{
			return false;
			die("ERROR in the query: ".$this->raw->error);
		}
	}

	
	public function haveRows($result)
	{
        if($result->num_rows > 0){
        	return true;
		}else{
			return false;
		}
	}
	public function numRows($result)
	{
		if($this->haveRows($result))
		{
			return $result->num_rows;
		}else{
			return false;
		}

	}

}