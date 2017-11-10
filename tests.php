<?php

require "vendor/autoload.php";




$data=Builder::table("customers")
	->select("address as addd","name")
       ->get();


//$check=Builder::table("customers")
//	->insert()
//->into();
//echo $check;

$s="jacob,juma,lo";

$ys="'".join("','",
		explode(',',
			$s)
	)."'";
echo $ys;