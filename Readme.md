#Instructions

Every query starts with 

    Builder::table( "provide the table name here")

Every query returns a json encoded response in format

    { "error": boolean,
    "response : "the response from the server"
    }

If the response at error is true, the response message  includes the error message. Whereas, if the response is false,
the message in the response depends on the query executed
e.g in a successful select query; if there is data, an array of 
records is returned such that:

    {"error":false;
    "response":
        {"id":5,"colum1":"valueX"},
        {"id":6,"colum1":"valueK"},
    }

To perform a basic select from table test

    Builder::table('test')
        ->get();

or to just select everything in the table     
    
    Builder::table('test')
          ->all();
      
 
To select only fifty columns
    
     Builder::table('test')
            ->get(50);
            
To select only 3 columns
    
        Builder::table('test)
            ->select('column1','column2','column3')
            ->get()
            
To select *column1* but alias as *c1*
    
    Builder::table('test)
                ->select('column1 as c1','column2','column3')
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
   
    < , > , <> , != , <= , >= , = 
      
To perform an insert 
   
    Builder::table('test)
            ->insert('data1','data2','data3')
            ->into('column1','column2','column3')
     
##### insert will return a response message of *inserted successful* on a successful insert

To truncate table test
    
    Builder::table('test')
    ->truncate();
    
To drop table test
    
    Builder::table('test')
    ->drop();
