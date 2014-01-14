<?php

require_once("./classes/main.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class User extends Main {

    protected $glob;

    public function __construct() {
        global $GLOBALS;
        $this->glob =& $GLOBALS;
    }

    private function checkMailFree($mail)
    {
		$sql = sprintf("SELECT email from users where email = '%s' ", $mail);

		$result = $this->glob['db']->query($sql); 
		if($result->num_rows > 0){
			return false;
		}else{
			return true;
		}
    }

    private function checkPwd($user, $pwd)
    {
		$sql = sprintf("SELECT username, password FROM users WHERE username = '%s' AND password = '%s'", $user, $this->hashPwd($pwd));
		$result = $this->glob['db']->query($sql); 
        if($result->num_rows > 0){
			return true;
		}else{
			return false;
		}
    }

    private function checkUsername($user)
    {
		$sql = sprintf("SELECT username from users where username = '%s' ", $user);


		$result = $this->glob['db']->query($sql); 
		if($result->num_rows > 0){
			return false;
		}else{
			return true;
		}
    }

    public function hashPwd($pwd)
    {
    	$newPassword = md5(sha1($pwd."9iu".crc32($pwd))."10u3jhkl");
    	return $newPassword;
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
    

    public function isAdmin($userid)
    {
    	if($this->getRank($userid) > 1){
    		return true;
    	}else{
    		return false;
    	}
    }

    private function getRank($userid){
		$sql = sprintf("SELECT rank from users where id = '%s' ", $userid);
		$result = $this->glob['db']->query($sql); 
        if($row = $result->fetch_assoc()){
        	return $row['rank'];
        }else{
        	die("User doesn't exist!");
        }    	
    }
    private function getUserId($username)
    {
		$sql = sprintf("SELECT id from users where username = '%s' ", $username);
		$result = $this->glob['db']->query($sql); 
        if($row = $result->fetch_assoc()){
        	return $row['id'];
        }else{
        	die("User doesn't exist!");
        }
    }

    private function isLogged()
    {
    	if(isset($_COOKIE['userLogged'])){
    		$user_array = unserialize(urldecode($_COOKIE['userLogged']));
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

		    	$login_array = urlencode(serialize($login_array));
		    	setcookie("userLogged", $login_array, time()+72000);
    			print_r(unserialize(urldecode($login_array)));
	    		
	    	}else{
	    		die("Wrong user/password combination !");
	    	}
    	}else{
    		die("You are already logged !! Please log out for login again");
    	}
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

