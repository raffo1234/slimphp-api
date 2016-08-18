<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/language", function() use($app){
	
	try{
		
		require 'connect.php';

		$query = "SELECT *
        FROM `language`";
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