<?php

require_once("./classes/misc.class.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Option extends Misc {
    protected $glob;

    public function __construct() {
        $this->getDB();
    }

    public function getAllVotesByOption($id_option) 
    {
        $fields_array = array("V.id_option", "O.id", "O.id_poll");
        $where_array = array(array("O.id", "=", $id_option));
        $join_array = array(
            array("INNER", "options O", "V.id_option", "=", "O.id")            
            );

        if($result = $this->_db->advancedSelect("votes V",$fields_array,$where_array, $join_array)){
			return $this->_db->numRows($result);
		}else{
			// Error.... 
			die("Error in the query");
		}
		
    }
    public function isExist($id_option)
    {
    	// print_r($this->_db);
		$result = $this->_db->simpleSelect("options O", "O.id", array("id", "=", $id_option));
		return $this->_db->haveRows($result);
		
    }
    public function deleteOption($id_option)
    {
		$where_array = array("id", "=", $id_option);
		if($this->_db->deleteToDB("options", $where_array)){
			// Option deleted sucefully ! 
		}else{
			// Error
			die("Error deleting the option!");
		}
    }
    public function editOption($id_option, $id_poll, $name, $description)
    {
 		$id_poll = intval($id_poll);
		
		$array_values = array(
			"id_poll" => $id_poll,
			"name" => $name,
			"description" => $description
			);
		$where_array = array("id", "=", $id_option);
		if($this->updateToDB("options", $array_values, $where_array)){
			// Option update sucefully ! 
		}else{
			// Error
			die("Error updating the option!");
		}
    }

    public function newOption($id_poll, $name, $description)
    {

		$id_poll = intval($id_poll);
		
		$array_values = array(
			"id_poll" => $id_poll,
			"name" => $name,
			"description" => $description
			);


		if($id_option = $this->insertToDB("options", $array_values)){
			// Option created sucefully ! 
		}else{
			// Error
			die("Error creating the option!");
		}
    }
}