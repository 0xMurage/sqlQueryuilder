<?php
/**
 * Created by PhpStorm.
 * User: mimidots
 * Date: 8/3/2019
 * Time: 8:36 PM
 */

namespace QueryBuilder;


/**
 * Class Response
 * @package QueryBuilder
 */
class Response
{


    /**
     * @var string
     */
    public $status;
    /**
     * @var integer
     */
    public $code;
    /**
     * @var string|array
     */
    public $response;
    /**
     * @var integer
     */
    public $systemErrorCode;
    /**
     * @var string
     */
    public $systemErrorMessage;

    /**
     * Response constructor.
     * @param string $status
     * @param int $code
     * @param array|string $response
     */
    public function __construct(string $status, int $code, $response)
    {
        $this->status = $status;
        $this->code = $code;
        $this->response = $response;
    }


    /**
     * @return string
     */
    public function getSystemErrorMessage(): string
    {
        return $this->systemErrorMessage;
    }

    /**
     * @param string $systemErrorMessage
     */
    public function setSystemErrorMessage(string $systemErrorMessage): void
    {
        $this->systemErrorMessage = $systemErrorMessage;
    }

    /**
     * @return int
     */
    public function getSystemErrorCode(): int
    {
        return $this->systemErrorCode;
    }

    /**
     * @param int $systemErrorCode
     */
    public function setSystemErrorCode(int $systemErrorCode): void
    {
        $this->systemErrorCode = $systemErrorCode;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    /**
     * @return array|string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param array|string $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }




}