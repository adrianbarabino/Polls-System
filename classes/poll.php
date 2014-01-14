<?php

require_once("./classes/main.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Poll extends Main {
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

	private function checkPollState($id_poll)
	{
		# code...
	}

	private function getPollByOption($id_option)
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
	private function checkByIP($id_poll, $ip)
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

	private function isDuplicated($id_poll, $ip)
	{

		if(isset($_COOKIE['pollsVoted'])){
			$cookie_array = unserialize(urldecode($_COOKIE['pollsVoted']));
			foreach ($cookie_array as $key => $value) {
				if($value['id_vote'] == $id_poll){
					return true;
				}else{
					if($this->checkByIP($id_poll, $ip) == true){
						return true;
					}else{
						return false;
					}
				}
			}			
		}else{
			if($this->checkByIP($id_poll, $ip) == true){
				return true;
			}else{
				return false;
			}
		}
	}
	public function newVote($id_option)
	{

		$id_option = intval($id_option);
		$id_poll = $this->getPollByOption($id_option); // We get the Poll ID
		
		$array_values = array(
			"id_option" => $id_option,
			"ip" => $_SERVER['REMOTE_ADDR'],
			"date" => date("Y-m-d H:i:s")
			);

		if(!$this->isDuplicated($id_poll, $array_values['ip'])){

			if($id_vote = $this->insertToDB("votes", $array_values)){

				// If all is fine, we proceed to save the vote on a cookie

				
				$array_to_cookie = array(
					"id_poll" => $id_poll,
					"id_option" => $id_option,
					"id_vote" => $id_vote
					);

				if(isset($_COOKIE['pollsVoted'])){
					$cookie_array = unserialize(urldecode($_COOKIE['pollsVoted']));


				}else{

					$cookie_array = array();
				}
				array_push($cookie_array, $array_to_cookie);
				$cookie_array = urlencode(serialize($cookie_array));
				setcookie("pollsVoted", $cookie_array, time()+72000);
			}
		}else{
			die("Duplicated !!");
		}



	}
}