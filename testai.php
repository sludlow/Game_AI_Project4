<?php

chdir('ai');
include 'lib/common.php';
setValues();
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

<<<<<<< HEAD
=======
//$actions = 1;
$actions = getActions();
>>>>>>> 662090e6dba5a807a3e73c794838d926ba0b09fc
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
<<<<<<< HEAD
=======
	//$actions--;
>>>>>>> 662090e6dba5a807a3e73c794838d926ba0b09fc
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