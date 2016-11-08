<?php
header("Access-Control-Allow-Origin: *");
$body = $_POST["Body"];

//Get location information
if (!empty($_POST["From"]))
	$from = $_POST["From"];
else
	$from = $_SERVER['REMOTE_ADDR'];

if (strlen($body) > 140)
{
	pubSub("Too long",$from);
	die(twiML("The demogorgon is coming!  Give me a shorter message!"));
}	
$message = clean($body);

//Get location information
if (!empty($_POST["From"]))
	$from = $_POST["From"];
else
	$from = $_SERVER['REMOTE_ADDR'];
	
//Check for cursing
if (badWords($message))
{
	pubSub("CURSING : " . $body,$from);
	die(twiML(""));
}

//Write to the queue
writeToFile($message,fromPhone($from));

//Publish the fact that I have received a new request
pubSub($body,$from);

echo twiML("Your message will soon appear from The Upside Down . . .");

function writeToFile($string,$fromPhone)
{
	//Put it at the end of the queue if it's from the web
	if (!$fromPhone)  
	{
		$shouldAddCarriageReturn = false;
		if(sizeof(file("queue.txt")) != 0)
			$shouldAddCarriageReturn = true;
			
		$handle = fopen("queue.txt", "a+");

		if ($handle) {
			$stringToAdd = "";
			if ($shouldAddCarriageReturn)
				$stringToAdd = "\n" . $string;
			else
				$stringToAdd = $string;
				
			fputs($handle,$stringToAdd);
			fclose($handle);
		} else {
			die (twiML("I'm hiding from the demogorgon.  Try again later!"));
			// error opening the file.
		}
	}
	else
	{
		$lines = file("queue.txt");
		
		$handle = fopen("queue.txt", "w+");

		if ($handle) {
			fputs($handle,$string);
			
			$first = true;
			foreach($lines as $line)
			{
				if ($first)
					$stringToAdd = "\n" . $line;
				else
					$stringToAdd = $line;
				fputs($handle,$stringToAdd);
				$first = false;
			}

			fclose($handle);
		} else {
			die (twiML("I'm hiding from the demogorgon.  Try again later!"));
			// error opening the file.
		}
		//Put it at the front of the queue if it's from a phone
	}
}

function clean($string) {

   $noSpecial = preg_replace('/[^A-Za-z ]/', '', $string); // Removes special chars.
   $noRepeatSpace = preg_replace('!\s+!', ' ', $noSpecial);
   return strtolower($noRepeatSpace);
}

function twiML($string)
{
	return "<Response><Message>".$string."</Message></Response>";
}

function fromPhone($origin)
{
	if (strpos($origin, ".") !== false)
		return false;
		
	return true;
}

function badWords($string)
{
	$handle = fopen("en", "r");
	if ($handle) 
	{
    	while (($line = fgets($handle)) !== false) 
    	{
    		$withoutLastCharacter = substr($line,0,strlen($line) - 1);
    		$swearLength = strlen($withoutLastCharacter);
        	if (strpos($string, $withoutLastCharacter) !== false) 
        	{
        		$lastPos = 0;
				$positions = array();

				while (($lastPos = strpos($string, $withoutLastCharacter, $lastPos))!== false) {
					$positions[] = $lastPos;
					$lastPos = $lastPos + 1;
				}
				
				//Check that first character is a word boundary
				$firstBoundary = false;
				foreach ($positions as $position)
				{
					if ($position == 0 || !ctype_alpha($string[$position - 1]))
					{
						$firstBoundary = true;
						break;
					}
				}
				
				if (!$firstBoundary)
					continue;
					
				//Check that first character is a word boundary
				$firstBoundary = false;
				foreach ($positions as $position)
				{
					if ($position == 0 || $string[$position - 1] == ' ')
					{
						$firstBoundary = true;
						break;
					}
				}
				
				if (!$firstBoundary)
					continue;
					
				//Check that last character is a word boundary
				$endBoundary = false;
				foreach ($positions as $position)
				{
					if ($position + $swearLength == strlen($string)  || $string[$position + $swearLength] == ' ')
					{
						$endBoundary = true;
						break;
					}
				}
				
				if (!$endBoundary)
					continue;

    			return true;
			}
    	}

    	fclose($handle);
    	
    	return false;
	} 
	else 
	{
    	// error opening the file.
    	die (twiML("I'm hiding from the demogorgon.  Try again later."));
	} 
}

function pubSub($message,$from)
{

	$url = "https://maker.ifttt.com/trigger/newStrangerThingsMessage/with/key/cH_Ro_Wc-FdudqL4Pk6QuU";   
	
	$data = array(
		"value1" => $message,
		"value2" => $from
	);
	 
	$content = json_encode($data);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
			array("Content-type: application/json"));
	curl_setopt($curl, CURLOPT_POST, true);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

	$json_response = curl_exec($curl);

	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	curl_close($curl);

	$response = json_decode($json_response, true);
}
?>