<?php

require_once("./classes/misc.class.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Poll extends Misc {
    protected $glob;

    public function __construct() {
        global $GLOBALS;
        $this->glob =& $GLOBALS;
    }
    public function getAllVotesFromPoll($id_poll)
    {

        $fields_array = array("V.id_option", "O.id", "O.id_poll", "P.id as 'IdPoll'");
        $where_array = array(array("P.id", "=", $id_poll));
        $join_array = array(
            array("INNER", "options O", "V.id_option", "=", "O.id"),
            array("LEFT", "polls P", "O.id_poll", "=", "P.id")
            );

		if($result = $this->advancedSelect("votes V",$fields_array,$where_array, $join_array)){
			return $this->numRows($result);
		}else{
			// Error.... 
			die("Error in the query");
		}
		
    }

    public function getAllVotesByOption($id_option)
    {
        $fields_array = array("V.id_option", "O.id", "O.id_poll");
        $where_array = array(array("O.id", "=", $id_option));
        $join_array = array(
            array("INNER", "options O", "V.id_option", "=", "O.id")            
            );

        if($result = $this->advancedSelect("votes V",$fields_array,$where_array, $join_array)){
			return $this->numRows($result);
		}else{
			// Error.... 
			die("Error in the query");
		}
		
    }


    public function getOptionsByPoll($id_poll){

        $fields_array = array("O.id", "O.id_poll", "P.id as 'IdPoll'");
        $where_array = array(array("P.id", "=", $id_poll));
        $join_array = array(
            array("INNER", "polls P", "O.id_poll", "=", "P.id")            
            );

        if($result = $this->advancedSelect("options O",$fields_array,$where_array, $join_array)){
			return $this->numRows($result);
		}else{
			// Error.... 
			die("Error in the query");
		}
    }
    private function getPercentage($num_amount, $num_total)
    {

		$count1 = $num_amount / $num_total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 0);
		return $count;

    }

    private function validatePoll($id_poll)
    {
        

		if($result = $this->simpleSelect("polls P", "P.id", array("id", "=", $id_poll))){
			return true;
		}else{
			return false;
		}    	
    }

    private function validPoll($id_poll)
    {
        $result = $this->simpleSelect("polls P", "P.id, P.finish_date", array("id", "=", $id_poll));

        if($row = $result->fetch_assoc()){
    		if(strtotime($row['finish_date']) > time()){
    			return true;
    		}else{
    			return false;
    		}
    	}
    }
    public function getPollData($id_poll){

		$result = $this->simpleSelect("polls P", "P.*", array("id", "=", $id_poll));
        if($row = $result->fetch_assoc()){
    	$pollData = array(
    		"name" => $row['name'],
    		"date" => $row['date'],
    		"finish_date" => $row['finish_date'],
    		"votes" => $this->getAllVotesFromPoll($id_poll),
    		"results" => $this->getPollResults($id_poll),
    		"created_by" => $row['created_by'],
    		"category" =>$row['category']
    		);
        	return $pollData;
        }else{
        	return $this->glob['db']->error;
        }
    }
    public function getPollResults($id_poll){
    	$pollResults = array();

        $fields_array = array("O.id as 'id_option'", "O.name as 'option_name'", "O.description as 'option_description'", "O.id_poll", "P.id as 'IdPoll'");
        $where_array = array(array("P.id", "=", $id_poll));
        $join_array = array(
            array("INNER", "polls P", "O.id_poll", "=", "P.id")            
            );

        $result = $this->advancedSelect("options O",$fields_array,$where_array, $join_array);

	   	while ($row = $result->fetch_assoc()) {


	   		if($row){


				$option_array = array(
					"id" => $row['id_option'],
					"name"  => $row['option_name'],
					"description"  => $row['option_description'],
					"votes" => $this->getAllVotesByOption($row['id_option']),
					"percentage" => $this->getPercentage($this->getAllVotesByOption($row['id_option']), $this->getAllVotesFromPoll($id_poll))
					);
				array_push($pollResults, $option_array);


		   	}else{
		   		// If we don't have options, the array be empty.
		   	}
		}


	return($pollResults);

    }

    public function deletePoll($id_poll)
    {
        $where_array = array("id", "=", $id_poll);
        if($this->deleteToDB("polls", $where_array)){
            // Poll deleted sucefully ! 
        }else{
            // Error
            die("Error deleting the poll!");
        }
    }

    public function editPoll($id_poll, $name, $createdBy, $date, $finish_date, $category = 1)
    {
        $id_poll = intval($id_poll);
        
        $array_values = array(
            "name" => $name,
            "date" => $date,
            "finish_date" => $finish_date,
            "created_by" => $createdBy,
            "category" => $category
            );
        $where_array = array("id", "=", $id_poll);
        if($this->updateToDB("polls", $array_values, $where_array)){
            // Poll update sucefully ! 
        }else{
            // Error
            die("Error updating the poll!");
        }
    }


    public function newPoll($name, $createdBy, $date, $finish_date, $category = 1)
    {
        
        $array_values = array(
            "name" => $name,
            "date" => $date,
            "finish_date" => $finish_date,
            "created_by" => $createdBy,
            "category" => $category
            );


        if($id_poll = $this->insertToDB("polls", $array_values)){
            // Poll created sucefully !
            return $id_poll;
        }else{
            // Error
            die("Error creating the poll!");
        }

    }

}