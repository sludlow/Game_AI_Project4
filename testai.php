<?php

chdir('ai');
include 'lib/common.php';
setValues();

$hasSmithy = false;
$hasGardens = false;
$hasWorkshop = false;

$hasVillage = false;
$hasRemodel = false;
$hasMarket = false;

$hasWoodcutter = false;

$stacks=$GameState['actionStacks'];
foreach ($stacks as $cardName)
{
	if ($cardName['cardName'] == 'smithy')
	{
		$hasSmithy = true;
	}
	if($cardName['cardName'] == 'gardens')
	{
		$hasGardens = true;
	}
	if($cardName['cardName'] == 'workshop')
	{
		$hasWorkshop = true;
	}
	if($cardName['cardName'] == 'village')
	{
		$hasVillage = true;
	}
	if($cardName['cardName'] == 'market')
	{
		$hasMarket = true;
	}
	if($cardName['cardName'] == 'woodcutter')
	{
		$hasWoodcutter = true;
	}
}

$preferred_strategy = $GameState['players'][$GameState['currentPlayer']]['strategy'];
$strategy_applicable = true;
switch ($preferred_strategy)
{
	case 'It Takes A Village':
		if (!($hasSmithy and $hasVillage and $hasMarket))
			$strategy_applicable = false;
		break;
	case 'Working Garden':
		if (!($hasGardens and $hasWorkshop))
			$strategy_applicable = false;
		break;
	case 'Big Money':
		break;
}
if (!$strategy_applicable)
{
	if ($hasGardens and $hasWorkshop)
	{
		$preferred_strategy = 'Working Garden';
	}
	else if ($hasSmithy and $hasVillage and $hasMarket)
	{
		$preferred_strategy = 'It Takes A Village';
	}
	else
	{
		$preferred_strategy = 'Big Money';
	}
}
switch ($preferred_strategy)
{
	case 'It Takes A Village':
		itTakesAVillage();
		break;
	case 'Working Garden':
		workingGarden();
		break;
	case 'Big Money':
		bigMoney();
		break;
}



function workingGarden()
{
	global $GameState, $hasWoodcutter, $hasVillage;
	
	$numVillages = numOfCardsOwned('village');
	$numWorkshops = numOfCardsOwned('market');
	
	$numSilvers = numOfCardsOwned('silver');
	$numGolds = numOfCardsOwned('gold');
	

	$actions = getActions();
	$cardsPlayed = array();
	while($actions > 0)
	{
		$actionTaken = false;
		$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
		$index = 0;
		foreach ($hand as $cardName)
		{
			if ($cardName == 'village' and in_array($index, $cardsPlayed) != true)
			{
				village();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			if($cardName == 'workshop' and in_array($index, $cardsPlayed) != true)
			{
				workshop();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			else if($cardName == 'woodcutter' and in_array($index, $cardsPlayed) != true)
			{
				woodcutter();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			$index++;
		}
		$actions = getActions();
		if($actionTaken == false)
		{	
			$actions = 0;
		}
	}
	
	$coins = count_money();
	play_money();
	$buys = getBuys();
	while($buys > 0)
	{
		$buys--;
		if($coins >= 3 and $numWorkshops <= 2 and buy_card('workshop'))
		{
			$coins -= 3;
		}
		else if($coins >= 4 and buy_card('gardens'))
		{
			$coins -= 4;
		}
		else if($coins >= 3)
		{
			$num = rand(0,19);
			$coins -= 3;
			if($num < 5 and $hasVillage and buy_card('village'))
			{}
			else if($num < 10 and buy_card('workshop'))
			{}
			else if($num < 15 and $hasWoodcutter and buy_card('woodcutter'))
			{}
			else
			{
				if(buy_card('estate'))
				{
					$coins += 1;
				}
				else if(buy_card('workshop'))
				{}
				else
				{
					buy_card('sliver');
				}
			}
		}
		else if($coins >= 2 and buy_card('estate'))
		{}
		else
		{
			buy_card('copper');
		}
	}
}

function itTakesAVillage()
{
	global $GameState;
	
	$numSmithys  = numOfCardsOwned('smithy');
	$numVillages = numOfCardsOwned('village');
	$numMarkets = numOfCardsOwned('market');
	
	$numSilvers = numOfCardsOwned('silver');
	$numGolds = numOfCardsOwned('gold');
	

	$actions = getActions();
	$cardsPlayed = array();
	while($actions > 0)
	{
		$actionTaken = false;
		$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
		$index = 0;
		foreach ($hand as $cardName)
		{
			if ($cardName == 'market' and in_array($index, $cardsPlayed) != true)
			{
				market();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			else if($cardName == 'village' and in_array($index, $cardsPlayed) != true)
			{
				village();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			else if($cardName == 'smithy' and in_array($index, $cardsPlayed) != true)
			{
				smithy();
				$cardsPlayed[] = $index;
				$actionTaken = true;
				break;
			}
			$index++;
		}
		$actions = getActions();
		if($actionTaken == false)
		{	
			$actions = 0;
		}
	}
	
	$coins = count_money();
	play_money();
	$buys = getBuys();
	while($buys > 0)
	{
		$cardsBought = false;
		if($coins >= 4 and $numSmithys == 0)
		{
			buy_card('smithy');
			$coins -= 4;
			$cardsBought = true;
			$buys--;
		}
		else if($coins >= 3 and $numSilvers == 0)
		{
			buy_card('silver');
			$coins -= 3;
			$cardsBought = true;
			$buys--;
		}
		else if($coins >= 6 and $numGolds == 0)
		{
			buy_card('gold');
			$coins -= 6;
			$cardsBought = true;
			$buys--;
		}
		else if($coins >= 5 and $numMarkets < 5)
		{
			buy_card('market');
			$coins -= 5;
			$cardsBought = true;
			$buys--;
		}
		else if($coins >= 3 and $numVillages <= $numSmithys and $numVillages < 4)
		{
			buy_card('village');
			$coins -= 3;
			$cardsBought = true;
			$buys--;
		}	
		else if($coins >= 4 and $numSmithys <= $numVillages and $numSmithys < 4)
		{
			buy_card('smithy');
			$coins -= 4;
			$cardsBought = true;
			$buys--;
		}		
		else if($coins >= 8 and $numSmithys == 4)
		{
			buy_card('province');
			$coins -= 8;
			$cardsBought = true;
			$buys--;
		}
		
		if($cardsBought == false)
		{
			$buys = 0;
		}
	}
}


// Big Money/Big Smithy
function bigMoney()
{
	global $GameState;
	// Big Money/ Big Smithy
	$hasSmithy = false;
	$numSmithys = -1;
	$stacks=$GameState['actionStacks'];
	foreach ($stacks as $cardName)
	{
		if ($cardName['cardName'] == 'smithy')
		{
			$hasSmithy = true;
			$numSmithys = numOfCardsOwned('smithy');
		}
	}

	$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];

	while(getActions() > 0)
	{
		if($hasSmithy == true)
		{
			$playSmithy = false;
			foreach ($hand as $cardName)
			{
				if ($cardName == 'smithy')
				{
					$playSmithy = true;
				}
			}
			if($playSmithy == true)
			{
				smithy();
			}
			else
			{
				break;
			}
		}
		else
		{
			break;
		}
	}
	$coins = count_money();
	play_money();
	if($coins >= 4 and $hasSmithy == true and $numSmithys == 0)
	{
		buy_card('smithy');
	}
	else if($coins >= 8)
	{
		buy_card('province');
	}
	else if($coins == 6 or $coins == 7)
	{
		buy_card('gold');
	}
	else if($coins == 5 or $coins == 4 or $coins == 3)
	{
		buy_card('silver');
	}
}

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>