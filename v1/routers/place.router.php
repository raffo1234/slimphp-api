<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

// define("ADMIN_URL", 'http://igospa.dhdinc.info/admin/');


$app->get("/places/:lang", function($lang) use($app){

	try{

		// CONNECTION
		// h.id = 1
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT p.*, GROUP_CONCAT(pi.image) as images, pt.title, pt.content, pt.lastModified
        FROM `place` p
        INNER JOIN `place_translation` pt ON p.id = pt.place_id
        INNER JOIN `place_image` pi ON p.id = pi.place_id
        WHERE  pt.language_code = '". $language_code ."' GROUP BY pi.place_id";
		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

		foreach($result as $item){
			$images = explode(',', $item->images);
			foreach($images as $n){
				$item->images_arr[]['image'] = constant("ADMIN_URL") .'images/places/'. $n;
			}
		};

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

$app->get("/place/:lang/:id", function($lang, $id) use($app){

	try{

		// CONNECTION
		// h.id = 1
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT p.*, GROUP_CONCAT(pi.image) as images, pt.title, pt.content
        FROM `place` p
        INNER JOIN `place_translation` pt ON p.id = pt.place_id
        INNER JOIN `place_image` pi ON p.id = pi.place_id
        WHERE p.id = '". $id ."' AND pt.language_code = '". $language_code ."'";
		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

		foreach($result as $item){
			$images = explode(',', $item->images);
			foreach($images as $n){
				$item->images_arr[]['image'] = constant("ADMIN_URL") .'images/places/'. $n;
			}
		};

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



$app->post("/place/:lang/:id", function($lang, $id) use($app){
	
	try{
		
		require 'connect.php';

		$params = $app->request()->params();
		
		$query = "UPDATE `place_translation`   
		    SET `title` = :title,
		       `content` = :content
		       
		 WHERE place_id = '". $id ."' AND language_code = '". $lang ."'"; 

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':title', $params['title'], PDO::PARAM_STR);       
		$dbh->bindParam(':content', $params['content'], PDO::PARAM_STR);
		$dbh->execute();
		

		// RESPONSE
	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode(array('status' => 'ok', 'mensaje' => 'Actualizado correctamente')));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getMessage();
	}

});