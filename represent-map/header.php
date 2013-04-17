<?php
include "./include/db.php";

// connect to db
$connection_string = "host=$db_host";
if($db_name) {
  $connection_string .= " dbname=$db_name";
}
if($db_user) {
  $connection_string .= " user=$db_user";
}
if($db_pass) {
  $connection_string .= " pass=$db_pass";
}

$conn = pg_connect($connection_string);
if(!$conn) { die(pg_last_error()); }

// if map is in Startup Genome mode, check for new data
if($sg_enabled) {
  require_once("include/http.php");
  include_once("startupgenome_get.php");
}

// input parsing
function parseInput($value) {
  return pg_escape_string($value);
}



?>