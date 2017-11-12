<?php
require "vendor/autoload.php";

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST'); 
header('content-type: application/json; charset=utf-8');


$data=Builder::table("customers")
	->select("address as addd","name")
       ->get();


//echo $data;
$check=Builder::table("test")
	->insert("martin murage",'Hungary',"Texas","NY",7886)
->into("name","address","city","state","zip");
echo $check;
