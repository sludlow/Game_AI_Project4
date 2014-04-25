<?php

chdir('ai');
include 'lib/common.php';
setValues();

$hasSmithy = true;
$hasGardens = true;
$hasWorkshop = true;

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
}

bigMoney();

/*
if($hasGardens && $hasWorkshop)
{
	workingGarden();
}
else
{
	bigMoney();
}*/
$GameState = read_gamestate();

function workingGarden()
{
	global $GameState;
	// Big Money/ Big Smithy
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