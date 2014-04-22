<?php

include_once 'lib/common.php';

// play all the coppers from hand
$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
$coins = 0;
foreach ($hand as $cardName)
{
	if ($cardName == 'copper')
	{
		$coins++;
		$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => $cardName
			);
	}
	else if ($cardName == 'silver')
	{
		$coins+=2;
		$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => $cardName
			);
	}
	else if ($cardName == 'gold')
	{
		$coins+=3;
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
	if ($card_by_name[$cardName]['cost']<$coins and $stack['amount']>0 and !in_array($cardName,array('curse','copper')))
	{
		$playerResponse['moves'][]=array(
				'action' => 'buy',
				'object' => $cardName
			);
		$coins-=$card_by_name[$cardName]['cost'];
		$stack['amount']--;
	}
}

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>