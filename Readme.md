[![Latest Stable Version](https://poser.pugx.org/murage/sqlddl/v/stable)](https://packagist.org/packages/murage/sqlddl)
[![Total Downloads](https://poser.pugx.org/murage/sqlddl/downloads)](https://packagist.org/packages/murage/sqlddl)
[![License](https://poser.pugx.org/murage/sqlddl/license)](https://packagist.org/packages/murage/sqlddl)
[![Coverage Status](https://coveralls.io/repos/github/mimidotsuser/sqlQueryuilder/badge.svg?branch=master)](https://coveralls.io/github/mimidotsuser/sqlQueryuilder?branch=master)

# **Installation**
First ensure the PHP version is greater or equal to 7

To include the library in an existing project using [composer](https://getcomposer.org/)
    
    composer require murage/sqlddl
  or in composer.json add  as dependency
 
"murage/sqlddl" : "^2.1.0"

This library is dependent on [PHP dotenv](https://github.com/vlucas/phpdotenv) and requires .env file at root of the project (env file does not have file name only extension .env; an example is provided).
To get started include the following ENV variables to be able to get started (change as per your database connection) 
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1 
DB_PORT=3306
DB_NAME=mydatabase
DB_USERNAME=secret
DB_PASSWORD=secret
```
  where  
  `DB_NAME`=your database name
  
  `DB_USERNAME`=your database username 
  
  `DB_PASSWORD`=your database password
# [Usages](manual.md)

The library utilizes nesting of functions, and does not require instantiating the Builder class. Start each query using :-

    Builder::table( "provide the table name here")

Every query returns a json encoded response in format

    { "status": "either error or success",
      "response : "the response from the server",
      "code":"response code"
    }

The code depends on the query being executed but on successful query, a code of 200 is returned. Where data is being fetched from the database,
 an array of records is returned in the response body e.g. **:**

    {"status":success;
    "response":
       [
        {"id":5, "colum1":"valueX"},
        {"id":6, "colum1":"valueK"}
        ],
     "code":200
    }
Other response codes are [here](ReturnCodes.md).
All queries that normally do not fetch any value from the database on successful execution will return

    
    {"status":success;
    "response": "success",
     "code":200
    }

To perform a basic select from table test

    Builder::table('test')
        ->get();

or to just select everything in the table     
    
    Builder::table('test')
          ->all();
      
 
To select only fifty records
    
     Builder::table('test')
            ->get(50);
            
To select only 3 columns
    
        Builder::table('test)
            ->select('column1','column2','column3')
            ->get()
            
To select *column1* but alias as *name*
    
    Builder::table('test)
                ->select('column1 as name','column2','column3')
                ->get()
                
To select using where condition
   * where column1 equals numbers
    
    Builder::table ('test')
        ->where('column1','=','numbers')
        ->get()
 * or this can be simplified as 
 
   
    Builder::table ('test')
    ->where('column1','numbers')
     ->get()
  
  allowed conditions include for where clause include
   
    < , > , <> , != , <= , >= , =, IS, IS NOT, <=> 
      
To perform an insert 
   
    Builder::table('test)
            ->insert('data1','data2','data3')
            ->into('column1','column2','column3')
     


To truncate table test
    
    Builder::table('test')
    ->truncate();
    
To drop table test
    
    Builder::table('test')
    ->drop();
    
##### An up-to date documentation is cooking  🍴
