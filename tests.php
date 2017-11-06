<?php

use QueryBuilder\db\Builder;

require "src/Builder.php";

Builder::table("customers")

	->get();

