<?php

// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Main {
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

		if($register_result = $this->glob['db']->query($sql)){
			return $this->glob['db']->insert_id;
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
}