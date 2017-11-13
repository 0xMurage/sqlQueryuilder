<?php
require "vendor/autoload.php";


$d=Builder::table("oc_country")
//	->select("name","iso_code_3")
	->where("iso_code_2","=","'KE'")
	->get();

echo $d;

Builder::table("oc_country")
	->insert("yhb","grg","gg")
	->into("rff","frf");