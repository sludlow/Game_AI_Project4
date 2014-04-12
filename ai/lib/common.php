<?php

function read_gamestate()
{
	return json_decode($_POST['_'],true);
}

?>