<?php

/**
 * All rights reserved.
 * User: hello@mimidots.com
 * Date: 29-Oct-17
 * Time: 09:42
 */

use QueryBuilder\Connect;

class Builder extends Connect
{

    private static $table;
    private $columns;
    private $values = [];
    private $whereby;
    private $order;
    private $condition = ['<', '>', '<>', '!=', '<=', '>=', '='];


    public static function table($table)
    {
        //as all calls will start with this function, first check if database connection has being established
        if(Connect::getConn()==null){
            self::terminate(self::$response);
        }
        self::$table = self::sanitize($table);

        return new static;
    }

    /**
     * @param string $columns
     *
     * @return $this
     */
    public function select($columns = "")
    {
        //check if columns were passed as individual string parameters

        if (func_num_args() > 1) {
            $this->columns = $this->columnize(func_get_args());
        } else {
            //check if a simgle array of columns was passed(a hack)
            $this->columns = is_array($columns) ? $this->columnize($columns)
                : self::sanitize($columns);
        }

        return $this;
    }

    /**
     * Convert an array of column names into a comma delimited string.
     *
     * @param  array $columns
     *
     * @return string
     */
    protected function columnize(array $columns)
    {
        $columns=self::sanitizeAV($columns);//sanitize the values
        return implode(",", array_values($columns));
    }


    /**
     * $column, $operator = "", $value
     * @return $this
     */
    public function where($params)
    {
        if (func_num_args() == 3) {

            $operator = self::sanitize(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby = self::sanitize(func_get_arg(0))
                    . $operator . '\''
                    . self::sanitize(func_get_arg(2)). '\'';
            } else {
                static::$response["status"] = "error";
                static::$response["response"] = "Invalid condition provided in where function";
                static::$response["code"] = 7000;
            }
        } else if (func_num_args() == 2) {
            $this->whereby =self::sanitize(func_get_arg(0)) . ' = \''
                . self::sanitize(func_get_arg(1)). '\'';
        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Invalid parameters provided in where function";
            static::$response["code"] = 7001;
        }

        return $this;
    }

    public function andWhere($param){
        if (func_num_args() == 3) {

            $operator = self::sanitize(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby .=' and '.self::sanitize(func_get_arg(0))
                    . $operator . '\''
                    . self::sanitize(func_get_arg(2)) . '\'';
            } else {
                static::$response["status"] = "error";
                static::$response["response"] = "Invalid condition provided in where function";
                static::$response["code"] = 7000;
            }
        } else if (func_num_args() == 2) {
            $this->whereby .= ' and '.self::sanitize(func_get_arg(0)). ' = \''
                .self::sanitize(func_get_arg(1) ). '\'';
        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Invalid parameters provided in where function";
            static::$response["code"] = 7001;
        }

        return $this;
    }

    public function orWhere($param){
        if (func_num_args() == 3) {

            $operator =self::sanitize(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby .=' or '.self::sanitize(func_get_arg(0))
                    . $operator . '\''
                    . self::sanitize(func_get_arg(2)) . '\'';
            } else {
                static::$response["status"] = "error";
                static::$response["response"] = "Invalid condition provided in where function";
                static::$response["code"] = 7000;
            }
        } else if (func_num_args() == 2) {
            $this->whereby .= ' or '.self::sanitize(func_get_arg(0)) . ' = \''
                . self::sanitize(func_get_arg(1)) . '\'';
        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Invalid parameters provided in where function";
            static::$response["code"] = 7001;
        }

        return $this;
    }

    public function get($limit = 0, $offset = 0)
    {

        //check if there is an error
        if (static::$response['status'] == "error") {
           return static::terminate(static::$response);
        }

        //check if the limit is a number
        if (!is_numeric($limit)) {
            static::$response["status"] = "error";
            static::$response["response"] = "Parameter limit should be numeric at function get()";
            static::$response["code"] = 6000;

            return static::terminate(static::$response);
        }

        //check if the offset is a number
        if (!is_numeric($offset)) {
            static::$response["status"] = "error";
            static::$response["response"] = "Parameter offset should be numeric at function get()";
            static::$response["code"] = 6001;

            return static::terminate(static::$response);
        }

        $table_name = self::$table;

        /* if no column passed as param, select all	 */
        $columns = empty($this->columns) ? "*" : $this->columns;

        $query = /** @lang text */
            "SELECT {$columns} FROM {$table_name}";

        if (!empty($this->whereby)) {

            $query = $query . ' WHERE ' . $this->whereby;
        }


        if (!empty($limit)) {
            $query = $query . ' LIMIT ' . $limit;
        }
        if (!empty($offset)) {
            $query = $query . ' OFFSET ' . $offset;
        }

          return  $this->fetch($query);
    }


    /**
     * Executes a query that returns data
     *
     * @param $sql
     * @return array|string
     */
    protected function fetch($sql)
    {
        $sql=self::sanitize($sql); //sanitize the query
        try {
            try {
                $stm = Connect::getConn()->prepare($sql);
            } catch (Exception $e) {
                static::$response["status"] = "error";
                static::$response["response"] = $e->getMessage();
                static::$response['code'] = $e->getCode();
               return static::terminate(static::$response);

            }
            try {
                $stm->execute();
            } catch (Exception $e) {
                static::$response["status"] = "error";
                static::$response["response"] = $e->getMessage();
                static::$response['code'] = $e->getCode();
               return static::terminate(static::$response);
            }
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response['code'] = $e->getCode();
           return static::terminate(static::$response);

        }
        try {
            $data = null;
            // set the resulting array to associative
            $stm->setFetchMode(PDO::FETCH_ASSOC);
            foreach (new RecursiveArrayIterator($stm->fetchAll()) as $k => $v) {
                $data[] = $v;
            }

            if ($data == null) {
                static::$response["status"] = "success";
                static::$response["response"] = Null;
                static::$response['code'] = 300;
               return static::terminate(static::$response);
            }
            static::$response["status"] = "success";
            static::$response["response"] = $data;
            static::$response['code'] = 200;
            return static::terminate(static::$response);


        } catch (PDOException $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();
           return static::terminate(static::$response);
        }


    }

    /**
     * Fetch all data without limits or offset
     */
    public function all()
    {
        $table = trim(self::$table);
        if (!empty($table)) {
            $query = /** @lang text */
                "SELECT * FROM {$table}";

            //execute the query and return the data or error message
              return  $this->fetch($query);


        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Table name cannot be empty";
            static::$response["code"] = 5000;
            return static::terminate(static::$response);
        }
    }

    public function insert($values)
    {
        try {
            if (func_num_args() > 0 && !is_array($values)) {
                $this->values = array_merge($this->values, self::sanitizeAV(func_get_args()));
            } else if (is_array($values)) {
                $this->values = self::sanitize($values);
            } else {
                static::$response["status"] = "error";
                static::$response["response"] = "unrecognized parameter options in the insert values";
                static::$response["code"] = 7004;
                return static::terminate(static::$response);
            }
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();
           return static::terminate(static::$response);
        }

        return $this;
    }


    public function into($columns)
    {
        //if columns count does not match values count, throw an error.

        $valuesCount = count($this->values);
        $colStringCount = 0;
        if (is_string($columns)) {
            try {
                $colStringCount = count(
                    explode(',', $columns)
                );
            } catch (Exception $e) {

                static::$response["status"] = "error";
                static::$response["response"] = "Unrecognized characters. Please refer to documentation on how to insert a record";
                static::$response["code"] = 4001;
               return static::terminate(static::$response);

            }
        }

        if (func_num_args() > 1 && func_num_args() == $valuesCount) {
            $this->columns = $this->columnize(func_get_args());
        } else if (is_array($columns) && count($columns) == $valuesCount) {
            $this->columns = $this->columnize($columns);
        } else if ($colStringCount == $valuesCount) {
            $this->columns = $columns;
        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Columns count does not equal the values count";
            static::$response['code'] = 4005;
           return static::terminate(static::$response);
        }


        return $this->doInsert();
    }

    /**
     * Perform the actual database insert
     * @return string
     */
    protected function doInsert()
    {
        //check if there is an error from previous function execution
        if (static::$response["status"] == "error") {
           return static::terminate(static::$response);
        }
        //convert each columns to ? parameter
        $columnParam = array_map(function () {
            return '?';
        }, $this->values);


        $sql = /** @lang sql */
            'INSERT INTO ' . self::$table .
            ' (' . $this->columns .
            ') VALUES(' . implode(',', $columnParam) . ')';

        try {
            $stm = Connect::getConn()->prepare($sql);
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();
           return static::terminate(static::$response);
        }

        try {
            $stm->execute($this->values);

            static::$response["status"] = "success";
            static::$response["response"] = "success";
            static::$response["code"] = 200;

            return static::terminate(static::$response);
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();
            return static::terminate(static::$response);
        }
    }

    /**
     * Warning: call the where clause first or all table data will be updated!
     * @param $data :associative array of column to value to be updated
     * @return string
     */
    public function update($data)
    {

        if (is_array($data)) {
            if ($this->isAssocStr($data)) {

                $query = "UPDATE " . self::$table . ' SET ';

                $this->values = array_values(array_map(function ($c) {
                    return self::sanitize($c);
                }, $data));

                $this->columns = array_keys($data);

                $columnParam = array_map(function ($column) {
                    return self::sanitize($column) . '=?';
                }, $this->columns);

                $query .= $this->columnize($columnParam);

                if (!empty($this->whereby)) {

                    $query = $query . ' WHERE ' . $this->whereby;
                }

                try {
                    $stm = Connect::getConn()->prepare($query);
                } catch (Exception $e) {

                    static::$response["status"] = "error";
                    static::$response["response"] = $e->getMessage();
                    static::$response["code"]=$e->getCode();
                    return self::terminate(static::$response);
                }

                try {

                    $stm->execute($this->values);

                    static::$response["status"] = "success";
                    static::$response["response"] = "success";
                    static::$response["code"] = 200;

                    return static::terminate(static::$response);

                } catch (Exception $e) {
                    static::$response["status"] = "error";
                    static::$response["response"] = $e->getMessage();
                    static::$response["code"]=$e->getCode();
                    return self::terminate(static::$response);
                }
            }

            static::$response["status"] = "error";
            static::$response["response"] = "Associative array expected in update function but sequential array passed";
            static::$response["code"]=6501;
            return self::terminate(static::$response);

        }
        static::$response["status"] = "error";
        static::$response["response"] = "Unrecognized data. Associative array expected in update function";
        static::$response["code"]=6500;
        return self::terminate(static::$response);
    }

    /**
     * Function to check if an array is association or sequential
     * @param $array
     * @return bool
     */
    private function isAssocStr($array)
    {
        if(!is_array($array)){
            return false;
        }
        for (reset($array); is_int(key($array));
             next($array)) {
            if (is_null(key($array)))
                return false;
        }
        return true;
    }

    /**
     * Warning: call this function after where clause or all data will be deleted
     * Function to delete record(s)
     * @return mixed
     */
    public function delete(){

        $query= /** @lang text */
            'DELETE FROM '.self::sanitize(static::$table);


        if (!empty($this->whereby)) {

            $query = $query . ' WHERE ' . $this->whereby;
        }

        try {
            $this->exec($query);

            static::$response["status"] = "success";
            static::$response["response"] = 'success';
            static::$response['code'] = 200;
            return static::terminate(static::$response);
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response['code'] = $e->getCode();
            return static::terminate(static::$response);
        }
    }


    public function truncate()
    {
        self::valTable();
        $sql = "TRUNCATE TABLE " . self::$table;
        try {
            $this->exec($sql);

            static::$response["status"] = "success";
            static::$response["response"] = "success";
            static::$response["code"] = 200;

            return static::terminate(static::$response);

        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();

            return static::terminate(static::$response);
        }
    }

    /**
     * Executes a query that does not return any results
     *
     * @param $query
     * @return null|string
     */
    protected function exec($query)
    {
        try {
            Connect::getConn()->exec($query);
        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();

            return static::terminate(static::$response);
        }
        return null;
    }

    public function drop()
    {

        static::valTable();
        $sql = /** @lang text */
            "DROP TABLE " . self::$table;
        try {
            $this->exec($sql);
            static::$response["status"] = "success";
            static::$response["response"] = "success";
            return self::terminate(static::$response);

        } catch (Exception $e) {
            static::$response["status"] = "error";
            static::$response["response"] = $e->getMessage();
            static::$response["code"] = $e->getCode();
            return static::terminate(static::$response);
        }

    }
    private static function sanitize($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Sanitizes values in an array
     * @param array $arry: the array to sanitize
     * @return array :sanitized array output
     */
    private static function sanitizeAV(array $arry){
       return array_map(function($value){
            return self::sanitize($value);
        },$arry);
    }


    /**
     *Validate that the table name has been provided and is a string
     */
    private static function valTable(){
        if(static::$table==null || ! is_string(static::$table)){
            static::$response["status"] = "error";
            static::$response["response"] = "check the table name provided";
            static::$response["code"]=5000;
            return self::terminate(static::$response);

        }else{
            static::$table=self::sanitize(static::$table);
        }
        return static::$table; //no effect
    }
}