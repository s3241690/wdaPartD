<?php
/**
* Checks the connection to the local install of MySQL
*/
require_once('db_pdo.inc');

function createPDO(){
	try{  
		$pdo = new PDO($dsn, DB_USER, DB_PW);
	  
		// all errors will throw exceptions
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	 
		return $pdo;
	}
	catch (PDOException $ePDO)
		echo $ePDO->getMessage();
		exit;
	}
}

function destroyPDO($pdo){
	$pdo = null;
}


?>





