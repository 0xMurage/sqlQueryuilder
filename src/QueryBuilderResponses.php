<?php
/**
 * Created by PhpStorm.
 * User: mimidots
 * Date: 8/3/2019
 * Time: 8:28 PM
 */

namespace QueryBuilder;


class QueryBuilderResponses
{
    const ERROR_STATUS = 'Error';
    const SUCCESS_STATUS = 'Success';

    public static function dbConnectionLost(): Response
    {
        return new Response(self::ERROR_STATUS, 500, 'Lost connection with the database');
    }

    public static function incorrectDBCredentials(): Response
    {
        return new Response(self::ERROR_STATUS, 500, 'Incorrect database access credentials');
    }

    public static function genericDBConnectionError(): Response
    {
        return new Response(self::ERROR_STATUS, 500, 'Database access error');
    }

    public static function genericQueryExecError(): Response
    {
        return new Response(self::ERROR_STATUS, 422, 'Error');
    }

    public static function tableNameError(): Response
    {
        return new Response(self::ERROR_STATUS, 500, 'check the table name provided');

    }

    public static function invalidQueryCondition(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Invalid condition provided in where function');
    }

    public static function invalidQueryParams(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Invalid parameters provided in where function');
    }

    public static function invalidSortOrder(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'The sort order in order by clause is invalid');
    }

    public static function invalidSelectionLimit(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Parameter limit should be numeric at function get()');
    }

    public static function invalidSelectionOffset(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Parameter offset should be numeric at function get()');
    }

    public static function invalidColumnOnInsert(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Unrecognized characters. Please refer to documentation on how to insert a record');
    }

    public static function invalidValueOnInsert(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'unrecognized parameter options in the insert values');
    }

    public static function invalidInsertQuery(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Columns count does not equal the values count');
    }

    public static function invalidUpdateQuery(): Response
    {
        return new Response(self::ERROR_STATUS, 500,
            'Associative array expected in update function. Kindly check and try again');
    }


    public static function onRecordSetSelect(): Response
    {
        return new Response(self::SUCCESS_STATUS, 200, []);
    }

    public static function onEmptyRecordSetSelect(): Response
    {
        return new Response(self::SUCCESS_STATUS, 200, []);
    }


    public static function onRecordCreate(): Response
    {
        return new Response(self::SUCCESS_STATUS, 201, 'Row inserted successfully');
    }

    public static function onRecordUpdate(): Response
    {
        return new Response(self::SUCCESS_STATUS, 200, 'Updated successfully');
    }

    public static function onRecordDelete(): Response
    {
        return new Response(self::SUCCESS_STATUS, 204, 'Deleted successfully');
    }
    public static function onTableDelete(): Response
    {
        return new Response(self::SUCCESS_STATUS, 204, 'Table dropped successfully');
    }

    public static function onTableTruncate(): Response
    {
        return new Response(self::SUCCESS_STATUS, 204, 'Table truncated successfully');
    }

}