<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/histories/:lang", function($lang) use($app){
	
	try{
		
		// CONNECTION
		// h.id = 1	
		require 'connect.php';

		

		$language_code = $lang;
		$query = "SELECT h.*, ht.title, ht.excerpt, ht.lastModified 
        FROM `history` h
        INNER JOIN `history_translation` ht ON h.id = ht.history_id
        WHERE  ht.language_code = '". $language_code ."'";
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

$app->get("/history/:lang/:id", function($lang, $id) use($app){
	
	try{
		
		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT h.*, ht.title,  ht.excerpt, ht.content
        FROM `history` h
        INNER JOIN `history_translation` ht ON h.id = ht.history_id
        WHERE h.id = '". $id ."' AND ht.language_code = '". $language_code ."'";
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






$app->post("/history/:lang/:id", function($lang, $id) use($app){
	
	try{
		
		require 'connect.php';

		$params = $app->request()->params();
		
		$query = "UPDATE `history_translation`   
		    SET `title` = :title,
		       `excerpt` = :excerpt,
		       `content` = :content
		       
		 WHERE history_id = '". $id ."' AND language_code = '". $lang ."'"; 

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':title', $params['title'], PDO::PARAM_STR);       
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

$app->post("/history", function() use($app){
	
	try{
		
		require 'connect.php';
		
		$now = date('Y-m-d H:i:s');


		// insert history
		$query = "INSERT INTO history(date_created) VALUES (
		            :date_created)";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':date_created', $now, PDO::PARAM_STR);       
		$dbh->execute();
		$lastId = $connection->lastInsertId();
			
		// insert history_translation es
		$query1 = "INSERT INTO history_translation(history_id, language_code) VALUES (
		            :history_id, 'es')";

		$dbh = $connection->prepare($query1);
		$dbh->bindParam(':history_id', $lastId, PDO::PARAM_STR);       
		
		$dbh->execute();            

		// insert history_translation en
		$query2 = "INSERT INTO history_translation(history_id, language_code) VALUES (
		            :history_id, 'en')";

		$dbh = $connection->prepare($query2);
		$dbh->bindParam(':history_id', $lastId, PDO::PARAM_STR);       
		
		$dbh->execute();            

		// insert history_translation it
		$query3 = "INSERT INTO history_translation(history_id, language_code) VALUES (
		            :history_id, 'it')";

		$dbh = $connection->prepare($query3);
		$dbh->bindParam(':history_id', $lastId, PDO::PARAM_STR);       
		
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

$app->post("/history_item/delete/:id", function($id) use($app){
	
	try{
		
		require 'connect.php';

		
		$query = "UPDATE `history`   
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