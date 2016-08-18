<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

define("ADMIN_URL", 'http://igospa.dhdinc.info/admin/');


$app->get("/people/:lang", function($lang) use($app){

	try{

		// CONNECTION
		// h.id = 1
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT p.*, GROUP_CONCAT(pi.image) as images, pt.firstname, pt.lastname, pt.excerpt
        FROM `people` p
        INNER JOIN `people_translation` pt ON p.id = pt.people_id
        INNER JOIN `people_image` pi ON p.id = pi.people_id
        WHERE pt.language_code = '". $language_code ."' GROUP BY pi.people_id";
		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

		foreach($result as $item){
			$images = explode(',', $item->images);
			foreach($images as $n){
				$item->images_arr[]['image'] = constant("ADMIN_URL") . 'images/people/' . $n;
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

$app->get("/people/:lang/:id", function($lang, $id) use($app){

	try{

		// CONNECTION
		// h.id = 1
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT p.*, GROUP_CONCAT(pi.image) as images, pt.firstname, pt.lastname, pt.excerpt, pt.content
        FROM `people` p
        INNER JOIN `people_translation` pt ON p.id = pt.people_id
        INNER JOIN `people_image` pi ON p.id = pi.people_id
        WHERE p.id = '". $id ."' AND pt.language_code = '". $language_code ."' GROUP BY pi.people_id";
		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

		foreach($result as $item){
			$images = explode(',', $item->images);
			foreach($images as $n){
				$item->images_arr[]['image'] = constant("ADMIN_URL") .'images/people/' . $n;
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




// edit
$app->post("/people/:lang/:id", function($lang, $id) use($app){

	try{

		require 'connect.php';

		$params = $app->request()->params();

		$query = "UPDATE `people_translation`
		    SET `firstname` = :firstname,
		    	`lastname` = :lastname,
		       `excerpt` = :excerpt,
		       `content` = :content

		 WHERE people_id = '". $id ."' AND language_code = '". $lang ."'";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':firstname', $params['firstname'], PDO::PARAM_STR);
		$dbh->bindParam(':lastname', $params['lastname'], PDO::PARAM_STR);
		$dbh->bindParam(':excerpt', $params['excerpt'], PDO::PARAM_STR);
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




// agregar - insert

$app->post("/people", function() use($app){

	try{

		require 'connect.php';

		$now = date('Y-m-d H:i:s');


		// insert people
		$query = "INSERT INTO people(date_created) VALUES (
		            :date_created)";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':date_created', $now, PDO::PARAM_STR);
		$dbh->execute();
		$lastId = $connection->lastInsertId();

		// insert people_translation es
		$query1 = "INSERT INTO people_translation(people_id, language_code) VALUES (
		            :people_id, 'es')";

		$dbh = $connection->prepare($query1);
		$dbh->bindParam(':people_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert people_translation en
		$query2 = "INSERT INTO people_translation(people_id, language_code) VALUES (
		            :people_id, 'en')";

		$dbh = $connection->prepare($query2);
		$dbh->bindParam(':people_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert people_translation it
		$query3 = "INSERT INTO people_translation(people_id, language_code) VALUES (
		            :people_id, 'it')";

		$dbh = $connection->prepare($query3);
		$dbh->bindParam(':people_id', $lastId, PDO::PARAM_STR);

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




// eliminar

$app->post("/people_item/delete/:id", function($id) use($app){

	try{

		require 'connect.php';


		$query = "UPDATE `people`
		    SET `deleted`='1'

		 WHERE `id` = " . $id;

		$dbh = $connection->prepare($query);
		$dbh->execute();


		// RESPONSE
	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode(array('status' => 'ok', 'mensaje' => 'Actualizado correctamente!')));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getMessage();
	}

});