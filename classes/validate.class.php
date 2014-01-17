<?php

require_once("./classes/misc.class.php");

class Validate extends Misc {

    public function __construct() {

    }

    public function newVoteValidation($array_to_cookie)
    {

		if(isset($_COOKIE['pollsVoted'])){

			$cookie_array = json_decode(urldecode($_COOKIE['pollsVoted']));

		}else{

			$cookie_array = array();
		}

		array_push($cookie_array, $array_to_cookie);
		$cookie_array = urlencode(json_encode($cookie_array));
		setcookie("pollsVoted", $cookie_array, time()+72000);

		return $cookie_array;

    }
	protected function checkByIP($id_poll, $ip)
	{
			
		$fields_array = array("V.ip as 'ip'", "V.id_option", "O.id", "O.id_poll", "P.id as 'idPoll'");
		$join_array = array(
			array("INNER", "options O", "P.id", "=", "O.id_poll"),
			array("LEFT", "votes V", "O.id", "=", "V.id_option")
			);
		$where_array = array(
			array("P.id", "=", $id_poll),
			array("ip", "=", $ip),
			);
		$result = $this->_db->advancedSelect("polls P", $fields_array, $where_array, $join_array);
        return $this->_db->haveRows($result);
	}


	public function removeVoteValidation($id_vote)
	{
		$array_id_in_cookie;

		if(isset($_COOKIE['pollsVoted'])){
			$cookie_array = json_decode(urldecode($_COOKIE['pollsVoted']));
			foreach($cookie_array as $key => $value)
			{
				if($value->id_vote == $id_vote){
					$array_id_in_cookie = $key;
				}

			}
			
			unset($cookie_array[$array_id_in_cookie]);
			$cookie_array = urlencode(json_encode($cookie_array));
			setcookie("pollsVoted", $cookie_array, time()+72000);
			print_r(json_decode(urldecode($cookie_array)));

		}else{

			return false;
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