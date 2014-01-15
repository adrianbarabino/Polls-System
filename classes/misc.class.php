<?php

// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Misc {
    protected $glob;

    public function __construct() {
        global $GLOBALS;
        $this->glob =& $GLOBALS;
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

		if($result = $this->glob['db']->query($sql)){
			return $this->glob['db']->insert_id;
		}else{
			return false;
			die("ERROR in the query: ".$this->glob['db']->error);
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
		if($result = $this->glob['db']->query($sql)){
			return true;

		}else{
			return false;

			die("ERROR in the query: ".$this->glob['db']->error);
		}
	}
	public function simpleSelect($table, $fields, $where_array)
	{
		// Where array example: array (table, fields, where array);

		$sql = sprintf("SELECT `%s` FROM %s WHERE `%s` %s '%s'", $fields, $table, $where_array[0], $where_array[1], $where_array[2]);
		if($result = $this->glob['db']->query($sql)){
			return $result;

		}else{
			die("ERROR in the query: ".$this->glob['db']->error);
			return false;

		}
	}

	public function advancedSelect($table, $fields_array, $where_array, $join_array)
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
				$whereString = sprintf("`%s` %s '%s' AND", $key[0], $key[1], $key[2]);
			}
			$whereString = substr($whereString, 0, -3);

		}else{
			$key = $where_array[0];
			$whereString = sprintf("`%s` %s '%s' ", $key[0], $key[1], $key[2]);
		}

		// Looking inside Join Array
		// Example of Join Array = array( array("INNER", "author", "book.id_author", "=", "author.id") );


			$joinString;

			$i = 0;
			foreach ($join_array as $key) {
				if($i = 0){

					$joinString = sprintf("%s JOIN %s ON `%s` %s '%s' ", $key[0], $key[1], $key[2], $key[3], $key[4]);
				}else{

					$joinString = sprintf("%s JOIN %s ON `%s` %s '%s' ", $key[0], $key[1], $key[2], $key[3], $key[4]);
				}
				$i++;
			}

			



		$sql = sprintf("SELECT `%s` FROM %s %s WHERE %s", $fields, $table, $joinString, $whereString);
		print_r($sql);
		if($result = $this->glob['db']->query($sql)){
			return $result;

		}else{
			die("ERROR in the query: ".$this->glob['db']->error);
			return false;

		}
	}


	public function deleteToDB($table, $where_array)
	{
		$sql = sprintf("DELETE FROM `%s` WHERE `%s` %s '%s'", $table, $where_array[0], $where_array[1], $where_array[2]);
		if($result = $this->glob['db']->query($sql)){
			return true;
		}else{
			return false;
			die("ERROR in the query: ".$this->glob['db']->error);
		}
	}

	public function validateMail($mail)
	{
		if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
			return true;
		}else{
			return false;
		}

	}

	public function cleanString($string)
	{
		$string = str_replace("<","<",$string);
		$string = str_replace(">",">",$string);
		$string = str_replace("\'","'",$string);
		$string = str_replace('\"',"\"",$string);
		return $string;
	}



	protected function checkByIP($id_poll, $ip)
	{
			
		$sql = sprintf("SELECT V.ip as 'ip', V.id_option, O.id, O.id_poll, P.id as 'idPoll' 
		FROM polls P 
		INNER JOIN options O on P.id = O.id_poll 
		LEFT JOIN votes V on O.id = V.id_option 
		WHERE P.id = '%s' AND ip = '%s' ", $id_poll, $ip);


		$result = $this->glob['db']->query($sql); 
        if($result->num_rows > 0){
        	return true;
		}else{
			return false;
		}
	}

	protected function getPollByOption($id_option)
	{
		$id_option = intval($id_option);
		// We connect the Votes table with Options and with the Polls by the ids.
		$sql = sprintf("SELECT O.id, O.id_poll, P.id as 'idPoll' 
		FROM options O 
		INNER JOIN polls P on O.id_poll = P.id 
		WHERE O.id = '%s' ", $id_option);
		$result = $this->glob['db']->query($sql); 
        if($row = $result->fetch_assoc()){
        	return $row['idPoll'];
        }else{
        	return $this->glob['db']->error;
        }
		
	}
}