<?php
/*
TypesRequest.php
*/

$connection = curl_init();

switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		/*
		GET Request Handler
		Performs a GET Request to POI Types

		Response
			- Returns a list of all types of POIs.
		*/

	 	$parameters = '?region=Hampshire';

	 	$api_path = '/ewt/poi/api/poi/types/' . $parameters;

		curl_setopt($connection, CURLOPT_URL, 'http://' . $_SERVER['HTTP_HOST'] . $api_path);
		curl_setopt($connection,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($connection,CURLOPT_HEADER, 0);

		header("Content-type: application/json");
		echo curl_exec($connection);

		break;

	default:
		// Request not supported
		header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
		echo "<strong>Error 400</strong>: Request method not supported.";
		break;
}

curl_close($connection);
?>