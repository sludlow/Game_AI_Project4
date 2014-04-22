<?php

$GameState = json_decode(stripslashes($_POST['_']),true);
$cards = json_decode(file_get_contents('data/cards.json'),true);
$card_by_name = array();
foreach ($cards as $card)
{
	$card_by_name[$card['name']]=$card;
}
$playerResponse = array('moves' => array());

// play all the coppers from hand
$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
$coppers = 0;
foreach ($hand as $cardName)
{
	if ($cardName == 'copper')
	{
		$coppers++;
		$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => $cardName
			);
	}
}

$stacks=$GameState['actionStacks']+$GameState['moneyStacks']+$GameState['pointStacks'];
function randomize_stacks($a, $b)
{
	return (rand(0,1)==1)?1:-1;
}
uasort($stacks,'randomize_stacks');
foreach ($stacks as $cardName => $stack)
{
	if ($card_by_name[$cardName]['cost']<$coppers and $stack['amount']>0 and $cardName != 'curse')
	{
		$playerResponse['moves'][]=array(
				'action' => 'buy',
				'object' => $cardName
			);
		$coppers-=$card_by_name[$cardName]['cost'];
		$stack['amount']--;
	}
}

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>