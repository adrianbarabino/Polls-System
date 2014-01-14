<?php

// Starting our webpage

require("./config.php");


if(isset($config['theme'])){
}else{
	
	$config['theme'] = "2014";
}
	require("./themes/".$config['theme']."/structure.php");

?>
