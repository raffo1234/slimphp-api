<?php

$app->get("/people-all/", function() use($app){
	
	try{
		
		require 'connect.php';

		$params = $app->request()->params();
		if ($params['modifiedSince'] == null) {
		    $params['modifiedSince'] = "0000-00-00";
		}

		$modifiedSince = $params['modifiedSince'];
		$modifiedSince = $modifiedSince != '' ? ' WHERE lastModified > "' . $modifiedSince . '" ' : '';
		
			
		$query = "SELECT *
		FROM `people` " . $modifiedSince;
        
		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

		// RESPONSE
	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode($result));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getMessage();
	}
});