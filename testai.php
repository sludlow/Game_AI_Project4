<?php

chdir('ai');
include 'lib/common.php';

// Big Money
$coins = count_money();
play_money();
if($coins >= 8)
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