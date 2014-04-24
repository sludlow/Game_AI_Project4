<?php

chdir('ai');
include 'lib/common.php';

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

$actions = 1;
$hand = $GameState['players'][$GameState['currentPlayer']]['hand'];
while($actions != 0)
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
	}
	$actions--;
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

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>