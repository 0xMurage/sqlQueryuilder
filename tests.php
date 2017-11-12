<?php

require "vendor/autoload.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); 
header('content-type: application/json; charset=utf-8');


$data=Builder::table("customers")
	->select("address as addd","name")
       ->get();

//echo $data;
$check=Builder::table("customers")
	->insert("martin",0)
->into("firstname","");
echo $check;
echo empty("");


	