<?php

try {
 $db_host = 'localhost';

 // prod
 $db_name = 'igospa_igospa';
 $db_user = 'igospa_igospa';
 $db_pass = 'Igosp@1234';

 // local
	// $db_name = 'igospa_igospa';
 // 	$db_user = 'root';
 // 	$db_pass = '';

 $connection = new PDO("mysql:host=" . $db_host . ";dbname=" . $db_name, $db_user, $db_pass);
 $connection->exec("SET CHARACTER SET utf8");
 $connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT );
 $connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
 $connection->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch (PDOException $e) {
 print "ERROR: " . $e->getMessage();
 die();
}
