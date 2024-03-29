<?php

/*
Regions
*/

include "../../../core/db.php";
$db = new DBManager();


switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		
		/*
		GET Request Handler
		Returns a list of Regions
		*/
		header("Content-type: application/json");
		$values = array();
		foreach ($db->select('pointsofinterest',array('DISTINCT region as v')) as $v) {
			$values[] = $v['v'];
		}
		echo json_encode($values);

		break;

	default:
		// Request not supported
		header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
		echo "<strong>Error 400</strong>: Request method not supported.";
		break;
}


?>