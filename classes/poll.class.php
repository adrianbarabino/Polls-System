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
    	$sql = sprintf("SELECT V.id_option, O.id, O.id_poll, P.id as 'IdPoll'
    			FROM votes V 
    			INNER JOIN options O on V.id_option = O.id
    			LEFT JOIN polls P on O.id_poll = P.id
    			WHERE P.id = '%s' ", $id_poll);
		if($result = $this->glob['db']->query($sql)){
			return $result->num_rows;
		}else{
			// Error.... 
			die("Error in the query");
		}
		
    }

    public function getAllVotesByOption($id_option)
    {
    	$sql = sprintf("SELECT V.id_option, O.id, O.id_poll
    			FROM votes V 
    			INNER JOIN options O on V.id_option = O.id
    			WHERE O.id = '%s' ", $id_option);
		if($result = $this->glob['db']->query($sql)){
			return $result->num_rows;
		}else{
			// Error.... 
			die("Error in the query");
		}
		
    }


    public function getOptionsByPoll($id_poll){
   	$sql = sprintf("SELECT O.id, O.id_poll, P.id as 'IdPoll'
    			FROM options O
    			INNER JOIN polls P on O.id_poll = P.id
    			WHERE P.id = '%s' ", $id_poll);
		if($result = $this->glob['db']->query($sql)){
			return $result->num_rows;
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
     	$sql = sprintf("SELECT P.id from polls P where id = '%s' ", $id_poll);
		if($result = $this->glob['db']->query($sql)){
			return true;
		}else{
			return false;
		}    	
    }

    private function validPoll($id_poll)
    {
     	$sql = sprintf("SELECT P.id, P.finish_date from polls P where id = '%s' ", $id_poll);
		$result = $this->glob['db']->query($sql); 
        if($row = $result->fetch_assoc()){
    		if(strtotime($row['finish_date']) > time()){
    			return true;
    		}else{
    			return false;
    		}
    	}
    }
    public function getPollData($id_poll){

		$sql = sprintf("SELECT P.* FROM polls P
		WHERE P.id = '%s' ", $id_poll);
		$result = $this->glob['db']->query($sql); 
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
	   	$sql = sprintf("SELECT O.id as 'id_option', O.name as 'option_name', O.description as 'option_description', O.id_poll, P.id as 'IdPoll'
	    			FROM options O
	    			INNER JOIN polls P on O.id_poll = P.id
	    			WHERE P.id = '%s' ", $id_poll);

	   	$result = $this->glob['db']->query($sql);
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



    public function newPoll($name, $createdBy, $date, $finish_date, $category = 1)
    {
   		
    }

}