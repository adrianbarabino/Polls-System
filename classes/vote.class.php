<?php

require_once("./classes/misc.class.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Vote extends Misc {
    protected $glob;

    public function __construct() {
        global $GLOBALS;
        $this->glob =& $GLOBALS;
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

    public function deleteVote($id_vote)
    {
		$where_array = array("id", "=", $id_vote);
		if($this->deleteToDB("votes", $where_array)){
			// Vote deleted sucefully ! 
		}else{
			// Error
			die("Error deleting the option!");
		}
    }
    public function editVote($id_vote, $id_option, $ip, $date)
    {
 		$id_option = intval($id_option);
		
		$array_values = array(
			"id_option" => $id_option,
			"ip" => $ip,
			"date" => $date
			);
		$where_array = array("id", "=", $id_vote);
		if($this->updateToDB("votes", $array_values, $where_array)){
			// Vote update sucefully ! 
		}else{
			// Error
			die("Error updating the option!");
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