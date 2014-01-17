<?php
require_once("./classes/validate.class.php");
require_once("./classes/db.class.php");
// We need to use our $db variable (for mysqli) into the class

$GLOBALS = array(
    'db' => $db
);

class Misc {
	protected $_db = null;
 
	public function setDB(Db $db) {
		return $this->_db = $db;
	}
 
	public function getDB() {
		if(null == $this->_db) {
			$this->setDB(new Db());

		}
		return $this->_db;
	}

    protected $glob;

    public function __construct() {
    	$this->getDB();
        global $GLOBALS;
        $this->_db =& $GLOBALS;
    }

	public function cleanString($string)
	{
		$string = str_replace("<","<",$string);
		$string = str_replace(">",">",$string);
		$string = str_replace("\'","'",$string);
		$string = str_replace('\"',"\"",$string);
		return $string;
	}


	protected function getPollByOption($id_option)
	{
		$id_option = intval($id_option);
		// We connect the Votes table with Options and with the Polls by the ids.
		$sql = sprintf("SELECT O.id, O.id_poll, P.id as 'idPoll' 
		FROM options O 
		INNER JOIN polls P on O.id_poll = P.id 
		WHERE O.id = '%s' ", $id_option);
		$result = $this->_db->raw->query($sql); 
        if($row = $result->fetch_assoc()){
        	return $row['idPoll'];
        }else{
        	return $this->_db->raw->error;
        }
		
	}
}