<?php

chdir('ai');
include 'lib/common.php';

<<<<<<< HEAD
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
		foreach ($hand as $cardName)
		{
			if ($cardName == 'smithy')
			{
				smithy();
				$actions--;
			}
		}
	}
	$actions--;
}
$coins = count_money();
play_money();
$numSmithys = numOfCardsOwned('smithy');
if($coins >= 4 and $hasSmithy == true and $numSmithys == 0)
{
	buy_card('smithy');
}
else if($coins >= 8)
=======
// Big Money
$coins = count_money();
play_money();
if($coins >= 8)
>>>>>>> 422b20d3c836c25ca0799cc219822593178925c2
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
<<<<<<< HEAD
/*
else if($coins == 5 or $coins == 4 or $coins == 3)
{
	buy_card('silver');
}
*/
=======

>>>>>>> 422b20d3c836c25ca0799cc219822593178925c2

echo json_encode($playerResponse); // playerResponse is an instance of PlayerResponse

?>