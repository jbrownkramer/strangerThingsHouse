<?php

$lines = file("queue.txt");
if(sizeof($lines) == 0)
	die("");
	
//Remove the top file from the list
$handle = fopen("queue.txt", "w");
if ($handle) {
	$shouldAddCarriageReturn = false;
	for($i = 1; $i < sizeof($lines); $i++)
	{
		$message = $lines[$i];
		fputs($handle,$message);
		
		$shouldAddCarriageReturn = true;
	}

    fclose($handle);
} else {
	die ("");
    // error opening the file.
}

//Return the top line
print $lines[0];
?>