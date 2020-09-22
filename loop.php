<?php

$testArray = array (1, 2, 4, 7, 13, 22, 36, 51, 80);

foreach ($testArray as $num)
{
	if($num % 2 == 0)
	{
		echo "$num <br>\n";
	}
}

?>
