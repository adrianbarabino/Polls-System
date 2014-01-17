<?php

require_once("./classes/misc.class.php");
require_once("./classes/validate.class.php");

class Vote extends Misc {
	protected $_validate = null;
	protected $_options = null;
 
	public function setValidator(Validate $validate) {
		return $this->_validate = $validate;
	}
 
	public function getValidator() {
		if(null == $this->_validate) {
			$this->setValidator(new Validate());

		}
		return $this->_validate;
	}

 
	public function setOptions(Option $options) {
		return $this->_options = $options;
	}
 
	public function getOptions() {
		if(null == $this->_options) {
			$this->setOptions(new Option());

		}
		return $this->options;
	}
    public function __construct() {
        $this->getValidator();
        $this->getOptions();
    }

	private function isDuplicated($id_poll, $ip)
	{	


		if(isset($_COOKIE['pollsVoted'])){

			$cookie_array = json_decode(urldecode($_COOKIE['pollsVoted']));
			$array_to_return;
			foreach ($cookie_array as $key => $value) {
				if(isset($value->id_poll) == $id_poll){

					$array_to_return = array(true, $value->id_vote);
				}

					// // If we want to check by IP, uncomment this...

					// if($this->checkByIP($id_poll, $ip) == true){
					// 	return true;
					// }else{
					// 	return false;
					// }
			}			
				if(isset($array_to_return))
				{
					return $array_to_return;
				}else{

					return false;
				}
		}

		// // If we want to check by IP, uncomment this
		// else{
		// 	if($this->checkByIP($id_poll, $ip) == true){
		// 		return true;
		// 	}else{
		// 		return false;
		// 	}
		// }
	}

    public function deleteVote($id_vote)
    {
		$where_array = array("id", "=", $id_vote);
		if($this->_db->deleteToDB("votes", $where_array)){
			// Vote deleted sucefully ! 
		}else{
			// Error
			die("Error deleting the option!");
		}
    }
    public function isExist($id_vote)
    {

		$result = $this->_db->simpleSelect("votes V", "V.id", array("id", "=", $id_vote));
		return $this->_db->haveRows($result);
		
    }

    
    public function editVote($id_vote, $id_option, $ip = NULL, $date = NULL)
    {
    	if($this->isExist($id_vote))
    	{
    		if($ip == NULL)
    			$ip = $_SERVER['REMOTE_ADDR'];
    		if($date == NULL)
    			$date = date("Y-m-d H:i:s");

	 		$id_option = intval($id_option);
			$id_poll = $this->getPollByOption($id_option); // We get the Poll ID

			$array_values = array(
				"id_option" => $id_option,
				"ip" => $ip,
				"date" => $date
				);
			$where_array = array("id", "=", $id_vote);
			if($this->_db->updateToDB("votes", $array_values, $where_array)){

					// If all is fine, we proceed to save the vote on a cookie

					
				$array_to_cookie = array(
					"id_poll" => $id_poll,
					"id_option" => $id_option,
					"id_vote" => $id_vote
					);
				

					if(isset($_COOKIE['pollsVoted'])){
						$cookie_array = json_decode(urldecode($_COOKIE['pollsVoted']));
						foreach($cookie_array as $key => $value)
						{
							if($value->id_vote == $id_vote){
								$array_id_in_cookie = $key;
							}

						}
					}else{

						$cookie_array = array();
					}
						if(isset($array_id_in_cookie)){
							$cookie_array[$array_id_in_cookie] = $array_to_cookie;
						}else{
							array_push($cookie_array, $array_to_cookie);
						}
						// print_r($array_to_cookie);
					$cookie_array = urlencode(json_encode($cookie_array));
					setcookie("pollsVoted", $cookie_array, time()+72000);
					print_r(json_decode(urldecode($cookie_array)));
			}else{
				// Error
				die("Error updating the vote!");
			}
		}else{
			die("This vote doesn't exist!");
		}
    }
    public function newVote($id_option, $ip = NULL, $date = NULL)
	{

		if($ip == NULL)
			$ip = $_SERVER['REMOTE_ADDR'];
		if($date == NULL)
			$date = date("Y-m-d H:i:s");

		$id_option = intval($id_option);
		if($this->_options->isExist($id_option))
		{

			$id_poll = $this->getPollByOption($id_option); // We get the Poll ID
			
			$array_values = array(
				"id_option" => $id_option,
				"ip" => $ip,
				"date" => $date
				);

			if(!$id_vote = $this->isDuplicated($id_poll, $array_values['ip'])){
				if($id_vote = $this->_db->insertToDB("votes", $array_values)){

					// If all is fine, we proceed to save the vote on a cookie

					$array_to_validate = array(
						"id_poll" => $id_poll,
						"id_option" => $id_option,
						"id_vote" => $id_vote
						);

					return json_decode(urldecode($this->_validate->newVoteValidation($array_to_validate)));
					

				}
			}else{
				if($this->isExist($id_vote[1])){

					$this->editVote($id_vote[1], $id_option, $ip, $date);

				}else{

					$this->_validate->removeVoteValidation($id_vote[1]);
				}
			}
		}else{
			die("Option doesn't exist!");
			return false;
		}



	}
}