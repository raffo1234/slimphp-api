<?php

// if(!defined("SPECIALCONSTANT")) die("Acceso denegado");

$app->get("/news/:lang", function($lang) use($app){

	try{

		// CONNECTION
		// h.id = 1
		require 'connect.php';
		$language_code = $lang;
		$params = $app->request()->params();

		// is admin
		$published = 1;
		$to_admin = $params['to_admin'];
		// var_dump($to_admin);
		// die();
		$published = ($to_admin === 1 ) ? '' : ' n.published = 1 AND ' ;

		$rows = " n.id, n.image, n.date_created, n.date as date_original, DATE_FORMAT(n.date, '%d-%m-%Y') as date, n.lastModified, n.deleted, n.published, nt.title, nt.excerpt  ";

		$sort = '';
		$limit = '';
		$offset = '';
		$filter_by_year = '';

		if(count($params) != 0){
			if($params['limit'] != NULL){
				$limit = $params['limit'];
				$limit = $limit != '' && $limit > 0 ? ' LIMIT ' . $limit . ' ' : '';
			}
			if($params['offset'] != NULL){
				$offset = $params['offset'];
				$offset = $offset != '' && $offset > 0 ? ' OFFSET ' . $offset . ' ' : '';
			}
			// var_dump(isset($params['fields']));
			// return;
			if(isset($params['fields']) && $params['fields'] != NULL){
                $rows = $params['fields'];
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
		FROM `new` n
        INNER JOIN `new_translation` nt ON n.id = nt.new_id
        WHERE $published nt.language_code = '". $language_code ."' " . $filter_by_year . " " . $sort . $limit . $offset;

		$dbh = $connection->prepare($query);
		$dbh->execute();
		$result = $dbh->fetchAll(PDO::FETCH_OBJ);

			// set format date
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

		// RESPONSE

	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode($result));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getnew();
	}

});

$app->get("/new/:lang/:id", function($lang, $id) use($app){

	try{

		require 'connect.php';

		$language_code = $lang;
		$query = "SELECT n.*, n.date as date_original, DATE_FORMAT(n.date, '%d-%m-%Y') as date, DATE_FORMAT(n.date, '%Y-%m-%d') as date_formatted, nt.*
        FROM `new` n
        INNER JOIN `new_translation` nt ON n.id = nt.new_id
        WHERE n.id = '". $id ."' AND nt.language_code = '". $language_code ."'";
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
		echo "Error: " . $e->getnew();
	}
});




// admin
// edit
$app->post("/new/:lang/:id", function($lang, $id) use($app){

	try{

		require 'connect.php';

		$params = $app->request()->params();

		$target_dir = constant('UPLOADS_DIR');
		$dir_section = 'news/';
		$image_fullpath = '';

		if(isset($_FILES['image'])){
			$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
			$image_name = uniqid('img-'.date('Ymd').'-') . '.' . $ext;
			$target_file = $target_dir . $dir_section . $image_name;
			// print_r($_FILES);
			// return;
			if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
				$image = $image_name;
			} else {
			    if(isset($params['image'])){
			    	$image = $params['image'];
				}
			}
		}else{
			if(isset($params['image'])){
				$image = $params['image'];
			}
		}

		// var_dump($params['date_formatted']);
		// return;
		$query = "UPDATE `new` JOIN `new_translation`
			ON 	`new`.`id` = `new_translation`.`new_id`
		    SET `title` = :title,
					 `urlweb` = :urlweb,
		       `image` = :image,
		       `excerpt` = :excerpt,
		       `date` = :date_formatted,
		       `date_created` = :date_created,
		       `content` = :content,
		       `published` = :published

		 WHERE new_id = '". $id ."' AND language_code = '". $lang ."'";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':title', $params['title'], PDO::PARAM_STR);
		$dbh->bindParam(':urlweb', $params['urlweb'], PDO::PARAM_STR);
		$dbh->bindParam(':image', $image, PDO::PARAM_STR);
		$dbh->bindParam(':excerpt', $params['excerpt'], PDO::PARAM_STR);
		$dbh->bindParam(':date_created', $params['date_created'], PDO::PARAM_STR);
		$dbh->bindParam(':date_formatted', $params['date_formatted'], PDO::PARAM_STR);
		$dbh->bindParam(':content', $params['content'], PDO::PARAM_STR);
		$dbh->bindParam(':published', $params['published'], PDO::PARAM_STR);
		$dbh->execute();


		// RESPONSE
	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode(array('status' => 'ok', 'mensaje' => 'Actualizado correctamente')));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getnew();
	}

});


$app->get("/new-id/:id", function($id) use($app){

   try{

      require 'connect.php';

      $query = "SELECT *, date as date_original, DATE_FORMAT(date, '%d-%m-%Y') as date
      FROM `new`
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

$app->get("/new-traslation-id/:new_id", function($new_id) use($app){

   try{

      require 'connect.php';

      $query = "SELECT *
      FROM `new_translation`
        WHERE new_id = '". $new_id ."' ";

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




// agregar - insert

$app->post("/new", function() use($app){

	try{

		require 'connect.php';

		$now = date('Y-m-d H:i:s');


		// insert new
		$query = "INSERT INTO new(date_created) VALUES (
		            :date_created)";

		$dbh = $connection->prepare($query);
		$dbh->bindParam(':date_created', $now, PDO::PARAM_STR);
		$dbh->execute();
		$lastId = $connection->lastInsertId();

		// insert new_translation es
		$query1 = "INSERT INTO new_translation(new_id, language_code) VALUES (
		            :new_id, 'es')";

		$dbh = $connection->prepare($query1);
		$dbh->bindParam(':new_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert new_translation en
		$query2 = "INSERT INTO new_translation(new_id, language_code) VALUES (
		            :new_id, 'en')";

		$dbh = $connection->prepare($query2);
		$dbh->bindParam(':new_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();

		// insert new_translation it
		$query3 = "INSERT INTO new_translation(new_id, language_code) VALUES (
		            :new_id, 'it')";

		$dbh = $connection->prepare($query3);
		$dbh->bindParam(':new_id', $lastId, PDO::PARAM_STR);

		$dbh->execute();



		// RESPONSE
	    $response = $app->response();
		$app->response->headers->set("Content-type", "application/json");
		$app->response->status(200);
		$app->response->body(json_encode(array('status' => 'ok', 'mensaje' => 'Actualizado correctamente')));

	}
	catch(PDOException $e){
		echo "Error: " . $e->getnew();
	}

});



// admin
// eliminar

$app->post("/new_item/delete/:id", function($id) use($app){

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
		echo "Error: " . $e->getnew();
	}

});
