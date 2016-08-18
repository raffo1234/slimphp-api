<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/messages-all/:id", function($lang) use($app){
	
	try{
		
		require 'connect.php';

		$language_code = $lang;
		$params = $app->request()->params();
		
		if(count($params) != 0){
			if($params['id'] != NULL){
				$id = $params['id'];
			}
			
		}
			
		$query = "SELECT *
		FROM `message` m
        INNER JOIN `message_translation` mt ON m.id = mt.message_id
        WHERE m.id = '". $id ."' ";
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



$app->get("/messages/:lang", function($lang) use($app){
	
	try{
		
		require 'connect.php';

		$language_code = $lang;
		$params = $app->request()->params();
		
		$rows = " m.*, mt.title, mt.excerpt ";
		$sort = '';
		$filter_by_year = '';

		if(count($params) != 0){
			if($params['fields'] != NULL){
				$rows = $params['fields'];
			}
			if($params['year'] != NULL){
				$year = $params['year'];
				$filter_by_year = " AND date_created LIKE '%". $year ."%' ";
			}
			if($params['sort'] != NULL){
				$sort_arr = explode(",", $params['sort']);
				$sort_by_arr = array();
				foreach ($sort_arr as $key => $value) {
					$direction = substr($value, 0, 1) == "-" ? "DESC" : "ASC";
					$row = substr($value, 1, strlen($value) - 1);
					$sort_by_arr[] = $row . " " . $direction;
					$sort_by = implode(",", $sort_by_arr);
				}
				$sort = ' ORDER BY ' . $sort_by . ' ';
			}
		}
			
		$query = "SELECT $rows
		FROM `message` m
        INNER JOIN `message_translation` mt ON m.id = mt.message_id
        WHERE mt.language_code = '". $language_code ."' " . $filter_by_year . " " . $sort;
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

$app->get("/message/:lang/:id", function($lang, $id) use($app){
	
	try{
		
		// CONNECTION
		// h.id = 1	
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT m.*, mt.title, mt.content
        FROM `message` m
        INNER JOIN `message_translation` mt ON m.id = mt.message_id
        WHERE m.id = '". $id ."' AND mt.language_code = '". $language_code ."'";
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

