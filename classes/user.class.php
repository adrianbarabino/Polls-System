<?php

require_once("./classes/misc.class.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class User extends Misc {

    protected $glob;

    public function __construct() {
        global $GLOBALS;
        $this->glob =& $GLOBALS;
    }

    private function checkMailFree($mail)
    {
        $result = $this->_db->simpleSelect("users", "email", array("email", "=", $mail));
        
		return $this->haveRows($result);
    }

    private function checkPwd($user, $pwd)
    {
        $result = $this->_db->simpleSelect("users", "username, password", array("username = '".$user."' AND password", "=", $this->hashPwd($pwd)));
        return $this->haveRows($result);
    }

    private function checkUsername($user)
    {

        $result = $this->_db->simpleSelect("users", "username", array("username", "=", $user));

		return $this->haveRows($result);
    }



    public function hashPwd($pwd)
    {
    	$newPassword = md5(sha1($pwd."9iu".crc32($pwd))."10u3jhkl");
    	return $newPassword;
    }

    public function getCurrentUser()
    {
    	if(isset($_COOKIE['userLogged'])){
    		$user_array = json_decode(urldecode($_COOKIE['userLogged']));
    		if(isset($user_array['id'])){
    			return $user_array['id'];
    		}
    	}else{
                return false;
		} 	
    }
    public function getUserData($userid)
    {

		$sql = sprintf("SELECT U.* FROM users U
		WHERE U.id = '%s' ", $userid);
		$result = $this->glob['db']->query($sql); 
        if($row = $result->fetch_assoc()){
    	$userData = array(
    		"id" => $row['id'],
    		"username" => $row['username'],
    		"email" => $row['email'],
    		"rank" => $row['rank'],
    		"last_ip" => $row['last_ip'],
    		"password" => $row['password'],
    		);
        	return $userData;
        }else{
        	return $this->glob['db']->error;
        }
    }
    
    public function isAdmin($userid = NULL)
    {
        if($userid == NULL)
            $userid = $this->getCurrentUser();

        if($this->getRank($userid) > 0){
            return true;
        }else{
    		return false;
    	}
    }


    private function getRank($userid = NULL){
        if($userid == NULL)
            $userid = $this->getCurrentUser();

		$result = $this->_db->simpleSelect("users", "rank", array("id", "=", $userid));
        if($row = $result->fetch_assoc()){
        	return $row['rank'];
        }else{
        	die("User doesn't exist!");
        }    	
    }
    private function getUserId($username)
    {
        $result = $this->_db->simpleSelect("users", "id", array("username", "=", $username));
        if($row = $result->fetch_assoc()){
        	return $row['id'];
        }else{
        	die("User doesn't exist!");
        }
    }

    private function isLogged()
    {
    	if(isset($_COOKIE['userLogged'])){
    		$user_array = json_decode(urldecode($_COOKIE['userLogged']));
    		if(isset($user_array['username'])){
    			return true;
    		}
    	}else{
    		return false;
    	}
    }
    public function login($user, $pwd)
    {
    	if(!$this->isLogged()){

	    	if($this->checkPwd($user, $pwd)){

		    	$login_array = array(
		    		"id" => $this->getUserId($user),
		    		"username" => $user,
		    		"pwd" => $this->hashPwd($pwd),
		    		"rank" => $this->getRank($this->getUserId($user)) 
		    	);

		    	$login_array = urlencode(json_encode($login_array));
		    	setcookie("userLogged", $login_array, time()+72000);
    			print_r(json_decode(urldecode($login_array)));
	    		
	    	}else{
	    		die("Wrong user/password combination !");
	    	}
    	}else{
    		die("You are already logged !! Please log out for login again");
    	}
    }

    public function editUser($id, $username, $pwd, $email, $rank)
    {
        # code...
    }
    public function register($username, $pwd, $email, $rank = 0)
    {
    	if($this->checkMailFree($email))
    	{
    		if($this->validateMail($email)){

	    		if($this->checkUsername($username)){
					$array_values = array(
						"username" => $username,
						"email" => $email,
						"rank" => $rank,
						"password" => $this->hashPwd($pwd),
						"last_ip" => $_SERVER['REMOTE_ADDR'],
					);
					if($id_vote = $this->insertToDB("users", $array_values)){
						return true;
					}else{
						return false;
						die("Error!");
					}


	    		}else{
	    			die("Username already used!");
	    		}
    		}else{
    			die("Invalid Email");
    		}
    	}else{
    		die("Email already used!");
    	}
    	# code...
    }

    public function logout()
    {
    	setcookie("userLogged", "", time()-3600);
    }
}

