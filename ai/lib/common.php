<?php

function read_gamestate()
{
	return json_decode(stripslashes($_POST['_']),true);;
}

?>