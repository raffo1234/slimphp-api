<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/messages/:lang", function($lang) use($app){

	try{

		require 'connect.php';

		$language_code = $lang;
		$params = $app->request()->params();

		$rows = " m.id, m.date_created, m.year, m.date as date_original, DATE_FORMAT(m.date, '%d-%m-%Y') as date, m.lastModified, m.deleted, mt.title, mt.excerpt ";
		$sort = '';
		$offset = '';
		$limit = '';
		$filter_by_year = '';

		if(count($params) !== 0){
			if(isset($params['limit']) && $params['limit'] != NULL){
				$limit = $params['limit'];
				$limit = $limit != '' && $limit > 0 ? ' LIMIT ' . $limit . ' ' : '';
			}
			if(isset($params['offset']) && $params['offset'] != NULL){
				$offset = $params['offset'];
				$offset = $offset != '' && $offset > 0 ? ' OFFSET ' . $offset . ' ' : '';
			}
			if(isset($params['fields']) && $params['fields'] != NULL){
                $rows = $params['fields'];
			}
			if(isset($params['year']) && $params['year'] != NULL){
                $year = $params['year'];

				$filter_by_year = " AND year LIKE '%". $year ."%' ";
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
        WHERE mt.language_code = '". $language_code ."' " . $filter_by_year . " " . $sort . $limit . $offset;

        $dbh = $connection->prepare($query);
        $dbh->execute();

        $result = $dbh->fetchAll(PDO::FETCH_OBJ);


        // set format date



        // var_dump($params);
        // return;
		// for year field
		if(isset($params['fields']) && count($params) != 0 && $params['fields'] != NULL && $params['fields'] == 'year'){

			foreach($result as $key => $value){
				$result[$key] = $value->year;
			}

			$result = array_values(array_unique($result));
			$result_year = [];
			foreach($result as $key => $value){
				$result_year[]['year'] = $value;
			}
			$result = $result_year;
		}else {
			foreach($result as $key => $value){

            $year_arr = explode('-', $value->date);
            $result[$key]->date_day = $year_arr[0];
            switch ($year_arr[1]) {
                case '01':
                    $year_arr[1] = 'Jan';
                    break;
                case '02':
                    $year_arr[1] = 'Feb';
                    break;
                case '03':
                    $year_arr[1] = 'Mar';
                    break;
                case '04':
                    $year_arr[1] = 'Apr';
                    break;
                case '05':
                    $year_arr[1] = 'May';
                    break;
                case '06':
                    $year_arr[1] = 'Jun';
                    break;
                case '07':
                    $year_arr[1] = 'Jul';
                    break;
                case '08':
                    $year_arr[1] = 'Aug';
                    break;
                case '09':
                    $year_arr[1] = 'Sep';
                    break;
                case '10':
                    $year_arr[1] = 'Oct';
                    break;
                case '11':
                    $year_arr[1] = 'Nov';
                    break;
                case '12':
                    $year_arr[1] = 'Dec';
                    break;
            }
            $result[$key]->date_mon = $year_arr[1];
            $result[$key]->date_yea = $year_arr[2];
        }
		}


		// var_dump($result_index);
		// die();
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

		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT m.*, DATE_FORMAT(m.date, '%d-%m-%Y') as date, DATE_FORMAT(m.date, '%Y-%m-%d') as date_formatted, mt.*
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


$app->get("/message-id/:id", function($id) use($app){

	try{

		require 'connect.php';

		$query = "SELECT *, date as date_original, DATE_FORMAT(date, '%d-%m-%Y') as date
		FROM `message`
        WHERE id = '". $id ."' ";

		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetch(PDO::FETCH_OBJ);

        // set format date
        $year_arr = explode('-', $result->date);
        $result->date_day = $year_arr[0];
        switch ($year_arr[1]) {
            case '01':
                $year_arr[1] = 'Jan';
                break;
            case '02':
                $year_arr[1] = 'Feb';
                break;
            case '03':
                $year_arr[1] = 'Mar';
                break;
            case '04':
                $year_arr[1] = 'Apr';
                break;
            case '05':
                $year_arr[1] = 'May';
                break;
            case '06':
                $year_arr[1] = 'Jun';
                break;
            case '07':
                $year_arr[1] = 'Jul';
                break;
            case '08':
                $year_arr[1] = 'Aug';
                break;
            case '09':
                $year_arr[1] = 'Sep';
                break;
            case '10':
                $year_arr[1] = 'Oct';
                break;
            case '11':
                $year_arr[1] = 'Nov';
                break;
            case '12':
                $year_arr[1] = 'Dec';
                break;
        }
        $result->date_mon = $year_arr[1];
        $result->date_yea = $year_arr[2];

        // var_dump($result->date_mon);
        // return;
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

$app->get("/message-traslation-id/:message_id", function($message_id) use($app){

	try{

		require 'connect.php';

		$query = "SELECT *
		FROM `message_translation`
        WHERE message_id = '". $message_id ."' ";

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


// admin
// edit
$app->post("/message/:lang/:id", function($lang, $id) use($app){

	try{

		require 'connect.php';

		$params = $app->request()->params();

		$query = "UPDATE `message` JOIN `message_translation`
			ON 	`message`.`id` = `message_translation`.`message_id`
		    SET `title` = :title,
		       `excerpt` = :excerpt,
		       `year` = :year,
		       `date` = :date_formatted,
		       `content` = :content

		 WHERE message_id = '". $id ."' AND language_code = '". $lang ."'";

		 $year_arr = '';
		 $year_arr = explode('-', $params['date_formatted']);
		 $year = $year_arr[0];

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':title', $params['title'], PDO::PARAM_STR);
		$dbh->bindParam(':excerpt', $params['excerpt'], PDO::PARAM_STR);
		$dbh->bindParam(':year', $year, PDO::PARAM_STR);
		$dbh->bindParam(':date_formatted', $params['date_formatted'], PDO::PARAM_STR);
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

$app->post("/message", function() use($app){

	try{

		require 'connect.php';

		$now = date('Y-m-d H:i:s');


		// insert message
		$query = "INSERT INTO message(date_created) VALUES (
		            :date_created)";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':date_created', $now, PDO::PARAM_STR);
		$dbh->execute();
		$lastId = $connection->lastInsertId();

		// insert message_translation es
		$query1 = "INSERT INTO message_translation(message_id, language_code) VALUES (
		            :message_id, 'es')";

		$dbh = $connection->prepare($query1);
		$dbh->bindParam(':message_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert message_translation en
		$query2 = "INSERT INTO message_translation(message_id, language_code) VALUES (
		            :message_id, 'en')";

		$dbh = $connection->prepare($query2);
		$dbh->bindParam(':message_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert message_translation it
		$query3 = "INSERT INTO message_translation(message_id, language_code) VALUES (
		            :message_id, 'it')";

		$dbh = $connection->prepare($query3);
		$dbh->bindParam(':message_id', $lastId, PDO::PARAM_STR);

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

$app->post("/message_item/delete/:id", function($id) use($app){

	try{

		require 'connect.php';

		$query = "DELETE FROM `new`
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

