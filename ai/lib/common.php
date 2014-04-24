<?php

function read_gamestate()
{
	return json_decode(stripslashes($_POST['_']),true);;
}

$GameState = read_gamestate();
$playerResponse = array('moves'=>array());
$money = 0;
$actions = 1;
$buys = 1;

$cards = json_decode(file_get_contents('../data/cards.json'),true);
$card_by_name = array();
foreach ($cards as $card)
{
	$card_by_name[$card['name']]=$card;
}

function getBuys()
{
	global $buys;
	return $buys;
}

function getActions()
{
	global $actions;
	return $actions;
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

function count_money()
{
	global $GameState, $money;
	$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
	$coins = $money;
	foreach ($hand as $cardName)
	{
		if ($cardName == 'copper')
		{
			$coins++;
		}
		else if ($cardName == 'silver')
		{
			$coins+=2;
		}
		else if ($cardName == 'gold')
		{
			$coins+=3;
		}
	}
	return $coins;
}

function play_money()
{
	global $GameState,$playerResponse;
	$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
	foreach ($hand as $cardName)
	{
		if ($cardName == 'gold')
		{
			$playerResponse['moves'][]=array(
					'action' => 'play',
					'object' => $cardName
				);
		}
		
		else if ($cardName == 'silver')
		{
			$playerResponse['moves'][]=array(
					'action' => 'play',
					'object' => $cardName
				);
		}
		else if ($cardName == 'copper')
		{
			$playerResponse['moves'][]=array(
					'action' => 'play',
					'object' => $cardName
				);
		}
	}
}

function buy_card($purchaseCard)
{
	global $GameState,$playerResponse;
	$stacks=$GameState['actionStacks']+$GameState['moneyStacks']+$GameState['pointStacks'];
	$stack = $stacks[$purchaseCard];
	if($stack['amount']>0)
	{
		$playerResponse['moves'][]=array(
				'action' => 'buy',
				'object' => $purchaseCard
			);
		$stack['amount']--;
	}
}

function numOfCardsOwned($targetCard)
{
	global $GameState;
	$numCards = 0;
	$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
	$deck = $GameState['players'][$GameState['currentPlayer']]['deck'];
	$discard = $GameState['players'][$GameState['currentPlayer']]['discard'];
	foreach ($hand as $cardName)
	{
		if ($cardName == $targetCard)
		{
			$numCards++;
		}
	}
	foreach ($deck as $cardName)
	{
		if ($cardName == $targetCard)
		{
			$numCards++;
		}
	}
	foreach ($discard as $cardName)
	{
		if ($cardName == $targetCard)
		{
			$numCards++;
		}
	}
	return $numCards;
}

function smithy()
{
	global $GameState,$playerResponse;
	$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => 'smithy'
			);
	for($i = 0; $i < 3; $i++)
	{
		draw_from_deck();
	}
	$actions -= 1;
}

function laboratory()
{
	global $GameState,$playerResponse;
	$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => 'laboratory'
			);
	for($i = 0; $i < 2; $i++)
	{
		draw_from_deck();
	}
}

function moneylender()
{
	global $GameState,$playerResponse, $money, $actions;
	$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => 'moneylender'
			);
	$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
	foreach ($hand as $cardName)
	{
		if ($cardName == 'copper')
		{
			$playerResponse['moves'][]=array(
					'action' => 'trash',
					'object' => $cardName
				);
				break;
		}
	}
	$money += 3;
	$actions -= 1;
}

function witch()
{
	global $GameState,$playerResponse, $actions;
	$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => 'witch'
			);
	for($i = 0; $i < 2; $i++)
	{
		draw_from_deck();
	}
	//ADD code here to give every other player a curse
	//
	//
	//
	//
	$actions -= 1;
}

function festival()
{
	global $GameState,$playerResponse, $money, $actions;
	$playerResponse['moves'][]=array(
				'action' => 'play',
				'object' => 'festival'
			);
	$money += 2;
	$actions += 1;
}

?>