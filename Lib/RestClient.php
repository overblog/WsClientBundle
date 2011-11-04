<?php
namespace Overblog\RestClientBundle\Lib;

use Overblog\RestClientBundle\Lib\RestQuery;
use Overblog\RestClientBundle\Exception\ConfigurationException;
use Overblog\RestClientBundle\Exception\QueryException;

/**
 * REST request abastraction Layer
 *
 * @author Xavier HAUSHERR
 */

class RestClient
{
    /**
     * Handler for curl call
     * @var array
     */
    protected $handler = array();

    /**
     * Actual connection
     * @var string
     */
    protected $active_connection;

    /**
     * Last call stats
     * @var array
     */
    protected $last_stats = array();

    /**
     * Last call headers
     * @var array
     */
    protected $last_headers = array();

    /**
     * Contain multi handler curl session
     * @var cURL
     */
    protected $cURL_multi_handler;

    /**
     * Logger instance
     * @var Overblog\RestClientBundle\Logging\RestClientLogger
     */
    protected $logger;

    /**
     * Constructor - Save dependecies
     * @param array $urls
     * @param Object $logger
     */
    public function __construct(Array $urls, $logger = null)
    {
        $this->urls = $urls;
        $this->logger = $logger;
    }

    /**
     * Get connection and save it into object
     *
     * @param string $name
     * @return RestClient
     */
    public function getConnection($name)
    {
        if(!isset($this->urls[$name]))
        {
            throw new ConfigurationException('Unable to find configuration "' . $name . '"');
        }
        else
        {
            $this->active_connection = $name;
        }

        return $this;
    }

    /**
     * Get Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function get($uri, Array $param = array())
	{
        $this->handler[$this->active_connection][] = $this->createRequest('GET', $uri, $param);

        return $this;
	}

    /**
     * Post Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function post($uri, Array $param = array())
	{
        $this->handler[$this->active_connection][] = $this->createRequest('POST', $uri, $param);

        return $this;
	}

    /**
     * Put Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function put($uri, Array $param = array())
	{
        $this->handler[$this->active_connection][] = $this->createRequest('PUT', $uri, $param);

        return $this;
	}

    /**
     * Delete Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function delete($uri, Array $param = array())
	{
        $this->handler[$this->active_connection][] = $this->createRequest('DELETE', $uri, $param);

        return $this;
	}

    /**
     * Exec stocked requests
     * @return array
     */
    public function exec()
    {
        // Only one request
        if(2 === count($this->handler, true))
        {
            $name = key($this->handler) . '_' . key(current($this->handler));
            $ch = current($this->handler);

            return $this->executeSingleRequest($ch[0], $name);
        }
        else
        {
            return $this->executeMultiRequest();
        }
    }

    /**
     * Create CURL request
     *
     * @param string $method
     * @param string $uri
     * @param array $param
     * @return cURL
     */
    protected function createRequest($method, $uri, Array $param = array())
    {
        if (is_null($this->active_connection))
        {
            throw new ConfigurationException('No connection set.');
        }

        $url = preg_replace('#([^:])//#', '$1/', $this->urls[$this->active_connection] . $uri);

        return new RestQuery($method, $url, $param);
    }

    /**
     * Execute single request
     * @param RestQuery $ch
     * @param string $name
     * @return array
     */
    protected function executeSingleRequest(RestQuery $ch, $name)
    {
        //Exec
        $return = $ch->exec();

        if(false === $return)
        {
            throw new QueryException('Curl Error : ' . curl_error($ch->getHandle()));
        }

        list($headers, $body) = explode("\r\n\r\n", $return, 2);

        $this->setLastHeaders($name, $headers);
        $this->setLastStats($name, $ch->getInfo());

        if($this->logger)
        {
            $this->logger->logQuery($ch->getMethod(), $ch->getParam(), $name, $this->getLastStats($name));
        }

        $body = $this->decodeBody($body);

        $ch->close();

        $this->active_connection = null;

        return array($name => $body);
    }

    /**
     * Execute multi request
     * @return array
     */
    protected function executeMultiRequest()
    {
        $mh = curl_multi_init();

        // Add Handler
        foreach($this->handler as $handler)
        {
            foreach($handler as $ch)
            {
                curl_multi_add_handle($mh, $ch->getHandle());
            }
        }

        // Exec Request
        $active = null;

        do
        {
            $mrc = curl_multi_exec($mh, $active);
        }
        while ($active > 0);

        // Get Results
        $bodies = array();

        foreach($this->handler as $name => $handler)
        {
            foreach($handler as $key => $ch)
            {
                $cle = $name . '_' . $key;

                $return = curl_multi_getcontent($ch->getHandle());

                if(null === $return)
                {
                    throw new QueryException('Curl Error : ' . $ch->getError());
                }

                list($headers, $body) = explode("\r\n\r\n", $return, 2);

                $this->setLastHeaders($cle, $headers);
                $this->setLastStats($cle, $ch->getInfo());

                if($this->logger)
                {
                    $this->logger->logQuery($ch->getMethod() . ' (Multi)', $ch->getParam(), $cle, $this->getLastStats($cle));
                }

                $bodies[$cle] = $this->decodeBody($body);

                curl_multi_remove_handle($mh, $ch->getHandle());
            }
        }

        curl_multi_close($mh);

        return $bodies;
    }

    /**
     * Set last REST class stats
     * @param strin $key
     * @param array $stats
     */
    protected function setLastStats($key, $stats)
    {
        $this->last_stats[$key] = $stats;
    }

    /**
     * Return last REST call stat
     *
     * @param string $key
     * @param string $code
     * @return array
     */
    public function getLastStat($key, $code)
    {
        return $this->last_stats[$key][$code];
    }

    /**
     * Return last REST call stats
     *
     * @return array
     */
    public function getLastStats($key)
    {
        return $this->last_stats[$key];
    }

    /**
     * Set last REST call headers
     *
     * @param strin $key
     * @param string $headers
     */
    protected function setLastHeaders($key, $headers)
    {

        $this->last_headers[$key] = explode("\r\n", $headers);
    }

    /**
     * Return last REST call headers
     *
     * @param strin $key
     * @return array
     */
    public function getLastHeaders($key)
    {
        return $this->last_headers[$key];
    }

    /**
     * Decode body response
     *
     * @param type $body
     * @return mixed
     */
    protected function decodeBody($body)
    {
        return json_decode($body);
    }
}