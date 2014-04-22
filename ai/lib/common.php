<?php

function read_gamestate()
{
	return json_decode(stripslashes($_POST['_']),true);;
}

$GameState = read_gamestate();
$playerResponse = array('moves'=>array());

$cards = json_decode(file_get_contents('../data/cards.json'),true);
$card_by_name = array();
foreach ($cards as $card)
{
	$card_by_name[$card['name']]=$card;
}

function draw_from_deck()
{
	global $GameState,$playerResponse;
	if (count($GameState['players'][$GameState['currentPlayer']]['deck']) == 0)
	{
		$newDeck = $GameState['players'][$GameState['currentPlayer']]['discard'];
		shuffle($newDeck);
		$GameState['players'][$GameState['currentPlayer']]['discard'] = array();
		$GameState['players'][$GameState['currentPlayer']]['deck'] = $newDeck;
		$playerResponse['moves'][]=array(
				'action'=>'shuffle',
				'updateDeck'=>$newDeck
			);
	}
	$drawnCard = array_shift($GameState['players'][$GameState['currentPlayer']]['deck']);
	array_push($GameState['players'][$GameState['currentPlayer']]['hand'],$drawnCard);
	$playerResponse['moves'][]=array(
			'action'=>'draw',
			'object'=>$drawnCard
		);
	return $drawnCard;
}

?>