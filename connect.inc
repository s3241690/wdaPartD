<?php
/**
* Checks the connection to the local install of MySQL
*/
require_once('db.inc');

if (!$dbconn = mysql_connect(DB_HOST, DB_USER, DB_PW)) {
  echo 'Could not connect to the mysql on ' . DB_HOST . "\n";
  exit;
}

if (!mysql_select_db(DB_NAME, $dbconn)) {
 echo 'Could not use database ' . DB_NAME . "\n";
 echo mysql_error() . "\n";
 exit;
}

?>





