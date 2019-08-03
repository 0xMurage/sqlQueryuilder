<?php

/**
 * All rights reserved.
 * User: holla@mimidots.com
 * Date: 29-Oct-17
 * Time: 09:42
 */
namespace mysqlBuilder;
use Exception;
use PDO;
use PDOException;
use QueryBuilder\Connect;
use QueryBuilder\QueryBuilderResponses;
use RecursiveArrayIterator;

/**
 * Class Builder
 * @package QueryBuilder
 */
class Builder extends Connect
{

    /**
     * @var string
     */
    private static $table;

    private $columns;
    /**
     * @var array
     */
    private $values = [];
    /**
     * @var string
     */
    private $whereby;
    /**
     * @var string
     */
    private $order;
    /**
     * @var string
     */
    private $groupby;
    /**
     * @var array
     */
    private $condition = ['<', '>', '<>', '!=', '<=', '>=', '=','is'];
    /**
     * @var bool
     */
    private $updateOrInsert=false;


    /**
     * Sets the table on to which the various statements are executed.
     * @param $table
     * @return string | $this
     */
    public static function table($table)
    {
        //as all calls will start with this function, first check if database connection has being established
        if(Connect::getConn()==null){

            exit(self::terminate());
            //lets just return the object of the class in-case of connection error (developer will handle the rest)
        }
        self::$table = self::sanitize($table);

        return new static;
    }

    /**
     * Sanitizes the data input values
     * @param $data
     * @return string
     */
    private static function sanitize($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    /**
     * Sets which columns to select
     * @param  $columns
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
     * Add conditional evaluation of where clause
     * $column, $operator = "", $value
     * @param $params
     * @return $this
     */
    public function where($params)
    {
        if (func_num_args() == 3) {

            $operator = strtolower(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby = self::sanitize(func_get_arg(0))
                    .' '.$operator .' \''
                    . self::sanitize(func_get_arg(2)). '\'';
            } else {
                static::$response=QueryBuilderResponses::invalidQueryCondition();
            }
        } else if (func_num_args() == 2) {
            $this->whereby =self::sanitize(func_get_arg(0)) . ' = \''
                . self::sanitize(func_get_arg(1)). '\'';
        } else {
            static::$response=QueryBuilderResponses::invalidQueryParams();
        }

        return $this;
    }

    /**
     * Add condition for and in where clause
     * @see where
     * @param $param
     * @return $this
     */
    public function andWhere($param){
        if (func_num_args() == 3) {

            $operator = strtolower(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby .=' and '.self::sanitize(func_get_arg(0))
                    .' '. $operator. ' \''
                    . self::sanitize(func_get_arg(2)) . '\'';
            } else {
                static::$response=QueryBuilderResponses::invalidQueryCondition();
            }
        } else if (func_num_args() == 2) {
            $this->whereby .= ' and '.self::sanitize(func_get_arg(0)). ' = \''
                .self::sanitize(func_get_arg(1) ). '\'';
        } else {
            static::$response=QueryBuilderResponses::invalidQueryParams();
        }

        return $this;
    }

    /**
     * Adds condition for or in where clause
     * @see where
     * @param $param
     * @return $this
     */
    public function orWhere($param){
        if (func_num_args() == 3) {

            $operator =strtolower(func_get_arg(1));
            if (is_numeric(array_search($operator, $this->condition))) {
                $this->whereby .=' or '.self::sanitize(func_get_arg(0))
                    .' '. $operator . ' \''
                    . self::sanitize(func_get_arg(2)) . '\'';
            } else {
                static::$response=QueryBuilderResponses::invalidQueryCondition();
            }
        } else if (func_num_args() == 2) {
            $this->whereby .= ' or '.self::sanitize(func_get_arg(0)) . ' = \''
                . self::sanitize(func_get_arg(1)) . '\'';
        } else {
            static::$response=QueryBuilderResponses::invalidQueryParams();
        }

        return $this;
    }

    /**
     * Set order in which the return results will be return
     * @param string $column :column to base the order on
     * @param string $sort :asc or desc
     * @return $this|mixed
     */
    public function orderBy($column = '', $sort = 'desc')
    {
        $column=self::sanitize($column);
        $sort=strtoupper(self::sanitize($sort));

        /*check if the sort method passed is valid */
        if (!(hash_equals('DESC',$sort) || hash_equals('ASC',$sort))){
            static::$response=QueryBuilderResponses::invalidSortOrder();
            return static::terminate(static::$response);
        }

        if ($this->order == null) {
            $this->order = " ORDER BY $column $sort";
        } else {
            $this->order .= ", $column $sort";
        }
        return $this;
    }

    /**
     * @param $columns string
     * @return $this
     */
    public function groupBy($columns)
    {
        //check if columns were passed as individual string parameters

        if (func_num_args() > 1) {
            $this->groupby = $this->columnize(func_get_args());
        } else {
            //check if a single array of columns was passed(a hack)
            $this->groupby = is_array($columns) ? $this->columnize($columns)
                : self::sanitize($columns);
        }

        return $this;
    }

    /**
     * Fetch records form database
     * @param int $limit :optional limit of records to be retrieved
     * @param int $offset :the index which the record should be retrieved from
     * @return array|string
     */
    public function get($limit = 0, $offset = 0)
    {

        //check if there is an error
        if (static::$response->getStatus() === QueryBuilderResponses::ERROR_STATUS) {
           return static::terminate(static::$response);
        }

        //check if the limit is a number
        if (!is_numeric($limit)) {
            static::$response=QueryBuilderResponses::invalidSelectionLimit();

            return static::terminate(static::$response);
        }

        //check if the offset is a number
        if (!is_numeric($offset)) {
            static::$response=QueryBuilderResponses::invalidSelectionOffset();

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
        if(!empty($this->groupby)){
            $query.=' GROUP BY '.$this->groupby;
        }

        if (!empty($this->order)) {
            $query .= $this->order;
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
        try {
            try {
                $stm = Connect::getConn()->prepare($sql);
            } catch (Exception $e) {
                static::$response=QueryBuilderResponses::genericQueryExecError();
                self::$response->setSystemErrorCode($e->getCode());
                self::$response->setSystemErrorMessage($e->getMessage());

               return static::terminate(static::$response);

            }
            try {
                $stm->execute();
            } catch (Exception $e) {
                static::$response=QueryBuilderResponses::genericQueryExecError();
                self::$response->setSystemErrorCode($e->getCode());
                self::$response->setSystemErrorMessage($e->getMessage());

               return static::terminate(static::$response);
            }
        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

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
                static::$response=QueryBuilderResponses::onEmptyRecordSetSelect();
               return static::terminate(static::$response);
            }
            static::$response=QueryBuilderResponses::onRecordSetSelect();
            static::$response->setResponse( $data);

            return static::terminate(static::$response);


        } catch (PDOException $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

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


            if(!empty($this->groupby)){
                $query.=' GROUP BY '.$this->groupby;
            }

            if (!empty($this->order)) {
                $query .= $this->order;
            }


            //execute the query and return the data or error message
              return  $this->fetch($query);


        } else {
            static::$response=QueryBuilderResponses::tableNameError();

            return static::terminate(static::$response);
        }
    }

    /**
     *Sets update if duplicate is found to true when inserting a record
     * @return $this
     */
    public  function insertOrUpdate()
    {
        $this->updateOrInsert=true;
        return $this;
    }
    /**
     * Sets the values to be inserted
     * @param $values
     * @return $this|string
     */
    public function insert($values)
    {
        try {
            if (func_num_args() > 0 && !is_array($values)) {
                $this->values = array_merge($this->values, self::sanitizeAV(func_get_args()));
            } else if (is_array($values) && count($values)>0) {
                $this->values = self::sanitizeAV($values);
            } else {
                static::$response=QueryBuilderResponses::invalidValueOnInsert();

                exit(static::terminate(static::$response));
            }
        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

           return static::terminate(static::$response);
        }

        return $this;
    }

    /**
     * Sets the column to which the values will be inserted
     * @param string|array $columns
     * @return string
     */
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

                static::$response=QueryBuilderResponses::invalidColumnOnInsert();
                self::$response->setSystemErrorCode($e->getCode());
                self::$response->setSystemErrorMessage($e->getMessage());

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
            static::$response=QueryBuilderResponses::invalidInsertQuery();

           return static::terminate(static::$response);
        }


        return $this->doInsert();
    }

    /**
     * Performs the actual database insert
     * @return string
     */
    protected function doInsert()
    {
        //check if there is an error from previous function execution
        if (static::$response->getStatus() == QueryBuilderResponses::ERROR_STATUS) {
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

            $ext=array_map(function ($column){
                return $column.'=VALUES('.$column.')';
            },explode(',',$this->columns));
            if($this->updateOrInsert){ //if update when duplicate is found is set to true
                $sql.=' ON DUPLICATE KEY UPDATE '.implode(',',$ext);
            }

        try {
            $stm = Connect::getConn()->prepare($sql);
        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

            return static::terminate(static::$response);
        }

        try {
            $stm->execute($this->values);

            return static::terminate(QueryBuilderResponses::onRecordCreate());
        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

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

                    static::$response=QueryBuilderResponses::genericQueryExecError();
                    self::$response->setSystemErrorCode($e->getCode());
                    self::$response->setSystemErrorMessage($e->getMessage());

                    return self::terminate(static::$response);
                }

                try {

                    $stm->execute($this->values);

                    static::$response=QueryBuilderResponses::onRecordUpdate();

                    return static::terminate(static::$response);

                } catch (Exception $e) {
                    static::$response=QueryBuilderResponses::genericQueryExecError();
                    self::$response->setSystemErrorCode($e->getCode());
                    self::$response->setSystemErrorMessage($e->getMessage());

                    return self::terminate(static::$response);
                }
            }

            static::$response=QueryBuilderResponses::invalidUpdateQuery();

            return self::terminate(static::$response);

        }
        static::$response=QueryBuilderResponses::invalidUpdateQuery();;
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
     * Deletes all or defined record(s) in the where clause
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

            static::$response=QueryBuilderResponses::onRecordDelete();

            return static::terminate(static::$response);
        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

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
            static::$response=QueryBuilderResponses::genericQueryExecError();
            static::$response->setSystemErrorMessage($e->getMessage());
            static::$response->setSystemErrorCode($e->getCode());

            return static::terminate(static::$response);
        }
        return null;
    }

    /**
     * Truncates a given table
     * @return string
     */
    public function truncate()
    {
        self::valTable();
        $sql = "TRUNCATE TABLE " . self::$table;
        try {
            $this->exec($sql);

            static::$response=QueryBuilderResponses::onTableTruncate();

            return static::terminate(static::$response);

        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

            return static::terminate(static::$response);
        }
    }

    /**
     *Validate that the table name has been provided and is a string
     */
    private static function valTable(){
        if(static::$table==null || ! is_string(static::$table)){
            static::$response=QueryBuilderResponses::tableNameError();
            return self::terminate(static::$response);

        }else{
            static::$table=self::sanitize(static::$table);
        }
        return static::$table; //no effect
    }

    /**
     * Function to drop a table
     * @return string
     */
    public function drop()
    {

        static::valTable();
        $sql = /** @lang text */
            "DROP TABLE " . self::$table;
        try {
            $this->exec($sql);

            self::$response=QueryBuilderResponses::onTableDelete();

            return self::terminate(static::$response);

        } catch (Exception $e) {
            static::$response=QueryBuilderResponses::genericQueryExecError();
            self::$response->setSystemErrorCode($e->getCode());
            self::$response->setSystemErrorMessage($e->getMessage());

            return static::terminate(static::$response);
        }

    }

    /**
     * Raw select using query passed as parameter
     * @param $query : the query to select record(s)
     * @return array|string
     */
    public static function rawSelect($query)
    {
        $b=new Builder();
        return $b->fetch($query);
    }
}