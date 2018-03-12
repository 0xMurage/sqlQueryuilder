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
        //TODO sanitize the table name
        self::$table = $table;

        return new static;
    }

    /**
     * @param string $columns
     *
     * @return $this
     */
    public function select($columns = "")
    {
        //TODO sanitize the columns

        //check if columns were passed as individual string parameters

        if (func_num_args() > 1) {
            $this->columns = $this->columnize(func_get_args());
        } else {
            //check if a simgle array of columns was passed(a hack)
            $this->columns = is_array($columns) ? $this->columnize($columns)
                : $columns;
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
        //TODO sanitize the columns
        return implode(",", array_values($columns));
    }


    /**
     * $column, $operator = "", $value
     * @return $this
     */
    public function where($params)
    {
        //TODO sanitize the parameters
        //TODO add functionality for (and ,or) multiple where clauses

        if (func_num_args() == 3) {

            $operator = func_get_arg(1);
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby = func_get_arg(0)
                    . $operator . '\''
                    . func_get_arg(2) . '\'';
            } else {
                static::$response["status"] = "error";
                static::$response["response"] = "Invalid condition provided in where function";
                static::$response["code"] = 7000;
            }
        } else if (func_num_args() == 2) {
            $this->whereby = func_get_arg(0) . ' = \''
                . func_get_arg(1) . '\'';
        } else {
            static::$response["status"] = "error";
            static::$response["response"] = "Invalid parameters provided in where function";
            static::$response["code"] = 7001;
        }

        return $this;
    }


    public function get($limit = 0, $offet = 0)
    {

        //check if there is an error
        if (static::$response['status'] == "error") {
           return static::terminate(static::$response);
        }

        //check if the limit is a number
        if (!is_numeric($limit)) {
            static::$response["status"] = "error";
            static::$response["response"] = "Parameter limit should be numeric function get()";
            static::$response["code"] = 6000;

            return static::terminate(static::$response);
        }

        //check if the offsel is a number
        if (!is_numeric($offet)) {
            static::$response["status"] = "error";
            static::$response["response"] = "Parameter offset should be numeric in function get()";
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
        //TODO sanitize the sql query

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
        // TODO sanitize the values
        try {
            if (func_num_args() > 0 && !is_array($values)) {
                $this->values = array_merge($this->values, func_get_args());
            } else if (is_array($values)) {
                $this->values = $values;
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
     * Perform the actusl database insert
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

    public function truncate()
    {
        //todo validate the table name

        $sql = "TRUNCATE TABLE " . self::$table;
        try {
            $this->exec($sql);

            static::$response["status"] = "error";
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
        //todo validate the table name

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
}