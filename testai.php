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
}

//bigMoney();

//$GameState = read_gamestate();

/*
if($hasGardens && $hasWorkshop)
{
	workingGarden();
}
*/
if($hasSmithy and $hasVillage and $hasMarket)
{
	itTakesAVillage();
}
else
{
	bigMoney();
}

function workingGarden()
{
	global $GameState;
	$hasSmithy = false;
	$numSmithys = -1;
	$stacks=$GameState['actionStacks'];
	
	$playedWorkshop = 0;
	$playedVillage = 0;
	$playedWoodcutter = 0;

	while(getActions() > 0)
	{
			$numWorkshop = 0;
			$numVillage = 0;
			$numWoodcutter = 0;
			$playWorkshop = false;
			$playVillage = false;
			$playWoodcutter = false;
			$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
			
			foreach ($hand as $cardName)
			{
				if ($cardName == 'Workshop')
				{
					$numWorkshop +=1;
					if($numWorkshop - $playedWorkshop > 0)
					{
						$playWorkshop = true;
					}
				}
				if ($cardName == 'village')
				{
					$numVillage +=1;
					if($numVillage - $playedVillage > 0)
					{
						$playVillage = true;
					}
				}
				if ($cardName == 'Woodcutter')
				{
					$numWoodcutter +=1;
					if($numWoodcutter - $playedWoodcutter > 0)
					{
						$playWoodcutter = true;
					}
				}
			}
			if($playVillage == true)
			{
				village();
			}
			if($playWorkshop)
			{
				workshop();
			}
			else if($playWoodcutter)
			{
				woodcutter();
			}
	}
	$coins = count_money();
	play_money();
	
	$buys = getBuys();
	
	while($buys > 0)
	{
		$buys--;
	}
	if($coins >= 4)
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

function itTakesAVillage()
{
	global $GameState;
	
	$numSmithys  = numOfCardsOwned('smithy');
	$numVillages = numOfCardsOwned('village');
	$numMarkets = numOfCardsOwned('market');
	
	$numSilvers = numOfCardsOwned('silver');
	$numGolds = numOfCardsOwned('gold');
	
<<<<<<< HEAD
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
=======
	while(getActions() > 0)
	{
		$actionTaken = false;
		$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
		foreach ($hand as $cardName)
		{
			if ($cardName == 'market')
			{
				market();
				$actionTaken = true;
				break;
			}
			else if($cardName == 'village')
			{
				village();
				$actionTaken = true;
				break;
			}
			else if($cardName == 'smithy')
			{
				smithy();
				$actionTaken = true;
				break;
			}
			
		}
		if($actionTaken == false)
		{	
			break;
>>>>>>> 08a19b42706c492a42a8521f6a53034280c9a41e
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
<<<<<<< HEAD
			$buys = 0;
=======
			break;
>>>>>>> 08a19b42706c492a42a8521f6a53034280c9a41e
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