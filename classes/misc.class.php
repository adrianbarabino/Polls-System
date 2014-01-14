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
}