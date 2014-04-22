<?php

$GameState = json_decode(stripslashes($_POST['_']),true);
$playerResponse = array('moves' => array());

// play all the coppers from hand
$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
foreach ($hand as $cardName)
{
	if ($cardName == 'copper')
	{
		$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => $cardName
			);
	}
}

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>