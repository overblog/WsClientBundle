<?php

namespace Overblog\WsClientBundle\Lib;

use Overblog\WsClientBundle\Exception\ConfigurationException;

/**
 * cURL query Object
 *
 * @author Xavier HAUSHERR
 */

class WsQuery
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
     * @param type $method
     * @param type $url
     * @param array $param
     * @return resource
     */
    public function __construct($method, $url, Array $param = array())
    {
        $this->setMethod($method);
        $this->url = $url;
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
    protected function init()
    {
        $this->handle = curl_init();

        // Options
        curl_setopt($this->handle, CURLOPT_URL, $this->url);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->param);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT);
        curl_setopt($this->handle, CURLOPT_HEADER, true);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_USERAGENT, 'OverBlog Rest Client');

        if (self::DELETE === $this->method)
        {
            curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $this->method);
        } else
        {
            curl_setopt($this->handle, $this->method, true);
        }

        return $this->handle;
    }

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
}