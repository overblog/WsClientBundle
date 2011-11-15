<?php

namespace Overblog\WsClientBundle\Query;

use Overblog\WsClientBundle\Exception\ConfigurationException;

/**
 * cURL query Object
 *
 * @author Xavier HAUSHERR
 */

abstract class WsQueryBase
{
    /**
     * Timeout for Web Service call
     * @var int
     */
    const TIMEOUT = 1000;

    /**
     * Get Method
     * @param string
     */
    const GET = CURLOPT_HTTPGET;

    /**
     * POST Method
     * @param string
     */
    const POST = CURLOPT_POST;

    /**
     * Put Method
     * @param string
     */
    const PUT = CURLOPT_PUT;

    /**
     * Delete Method
     * @param string
     */
    const DELETE = 'DELETE';

    /**
     * Handle cURL
     * @var resource
     */
    protected $handle;

    /**
     * HTTP Method
     * @var string
     */
    protected $method;

    /**
     * HTTP Method Name
     * @var string
     */
    protected $methodName;

    /**
     * Request URL
     * @var string
     */
    protected $url;

    /**
     * Request Param
     * @var Array
     */
    protected $param = array();

    /**
     * Is the query is part of a multi-query
     * @var boolean
     */
    protected $isMulti = false;

    /**
     * Set var and init cURL instance
     *
     * @param string $method
     * @param string $host
     * @param string $url
     * @param int $type
     * @param array $param
     * @return resource
     */
    public function __construct($method, $host, $url, $id = null, Array $param = array())
    {
        $this->setMethod($method);
        $this->host = $host;
        $this->url = $url;
        $this->id = $id;
        $this->param = $param;

        return $this->init();
    }

    /**
     * Set HTTP Method
     * @param string $method
     */
    protected function setMethod($method)
    {
        $method = strtoupper($method);
        $this->methodName = $method;

        if(defined('self::' . $method))
        {
            $this->method = constant('self::' . $method);
        }
        else
        {
            throw new ConfigurationException('Unknow method');
        }
    }

    /**
     * Init cURL instance
     * @return resource
     */
    abstract protected function init();

    /**
     * Return cURL resource
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Exec cURL request
     * @return string
     */
    public function exec()
    {
        if($this->isMulti)
        {
            return curl_multi_getcontent($this->getHandle());
        }
        else
        {
            return curl_exec($this->handle);
        }
    }

    /**
     * Return query error
     * @return string
     */
    public function getError()
    {
        return curl_error($this->handle);
    }

    /**
     * Return request stats
     * @return array
     */
    public function getInfo()
    {
        return curl_getinfo($this->handle);
    }

    /**
     * Return HTTP method
     * @return string
     */
    public function getMethod()
    {
        return $this->methodName;
    }

    /**
     * Return query param
     * @return array
     */
    public function getParam()
    {
        return $this->param;
    }

    /**
     * Set the query as a part of a multi query
     */
    public function setMulti()
    {
        $this->isMulti = true;
    }

    /**
     * Set the query as a part of a single query
     */
    public function setSingle()
    {
        $this->isMulti = false;
    }

    /**
     * Close cURL connection
     * @return boolean
     */
    public function close()
    {
        return curl_close($this->handle);
    }

    /**
     * Decode body response
     *
     * @param type $body
     * @return mixed
     */
    abstract public function decodeBody($body);
}