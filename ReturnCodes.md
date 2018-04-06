Here are the of defined return codes:

    200: //no error occurred
    300 //no data returned but the query executed successfully, e.g selecting from empty table

	1044: //The provided credentials (username) cannot be used to connnect to the database
	1045: //The provided credentials (password or username) cannot be used to connnect to the database
	
    2002 ://cannot reach the database server
     
    4001: //Unrecognized characters. Please refer to documentation on how to insert a record
    4004: //unrecognized parameter options in the insert values
    4005: //operation unsuccessful.Columns count does not equal the values count
     	
    5000://Table name was not provided

	6050: //Invalid sort method in orderby function
	
    6000: //Parameter limit should be numeric function get()
    6001: //Parameter offset should be numeric in function get()
    
    6500: //Associative array expected in update function but another param data type passed
    6501: //Associative array expected in update function but sequential array passed
    	
    7000 ://Invalid condition provided in where function
    7001://Invalid parameters provided in where function
    

Also, PDO error codes and respective messages can also be returned.
 For documentation on such, check the official documentation [here]('')