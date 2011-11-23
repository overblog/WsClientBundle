<?php
namespace Overblog\WsClientBundle\Client;

use Overblog\WsClientBundle\Query\WsQueryBase;

use Overblog\WsClientBundle\Manager\WsMultiQueryManager;
use Overblog\WsClientBundle\Exception\ConfigurationException;
use Overblog\WsClientBundle\Exception\QueryException;
use Overblog\WsClientBundle\Logging\WsLoggerInterface;

/**
 * WS request abastraction Layer
 *
 * @author Xavier HAUSHERR
 */

class WsClient
{
    /**
     * Handler for curl call
     * @var array
     */
    protected $handler = array();

    /**
     * OD for request
     * @var int
     */
    protected $count = 1;

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
     * @var Overblog\WsClientBundle\Logging\WsClientLogger
     */
    protected $logger;

    /**
     * Constructor - Save dependecies
     * @param array $urls
     * @param WsLoggerInterface $logger
     */
    public function __construct(Array $urls, WsLoggerInterface $logger = null)
    {
        $this->urls = $urls;
        $this->logger = $logger;
    }

    /**
     * Get connection and save it into object
     *
     * @param string $name
     * @return WsClient
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
        return $this->createRequest('GET', $uri, $param);;
	}

    /**
     * Post Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function post($uri, Array $param = array())
	{
       return $this->createRequest('POST', $uri, $param);
	}

    /**
     * Put Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function put($uri, Array $param = array())
	{
        return $this->createRequest('PUT', $uri, $param);
	}

    /**
     * Delete Request
     * @param string $uri
     * @param array $param
     * @return array
     */
	public function delete($uri, Array $param = array())
	{
        return $this->createRequest('DELETE', $uri, $param);
	}

    /**
     * Create CURL request
     *
     * @param string $method
     * @param string $uri
     * @param array $param
     * @return WsQueryBase
     */
    protected function createRequest($method, $uri, Array $param = array())
    {
        if (is_null($this->active_connection))
        {
            throw new ConfigurationException('No connection set.');
        }

        $class = 'Overblog\\WsClientBundle\\Query\\WsQuery' . ucfirst(strtolower($this->urls[$this->active_connection]['type']));

        //Generate od for request
        $id = $this->active_connection . '_' . $this->count;

        $this->handler[$this->active_connection][] = array(
            'object' => new $class($method, $this->urls[$this->active_connection]['url'], $uri, $id, $param),
            'id' => $id
        );

        $this->count++;

        return $this;
    }

    /**
     * Exec stocked requests
     * @return array
     */
    public function exec()
    {
        // Only one request
        if(2 === $this->count)
        {
            $query = current($this->handler);

            $return = $this->executeSingleRequest($query[0]['object'], $query[0]['id']);
        }
        else
        {
            $return = $this->executeMultiRequest();
        }

		$this->resetHandler();

		return $return;
    }

    /**
     * Reset handler
     */
    protected function resetHandler()
    {
		$this->handler = array();
		$this->count = 1;
    }

    /**
     * Execute single request
     * @param WsQueryBase $query
     * @param string $name
     * @return array
     */
    protected function executeSingleRequest(WsQueryBase $query, $id)
    {
        $body = $this->execQuery($query, $id);

        $query->close();

        $this->active_connection = null;

        return array($id => $body);
    }

    /**
     * Execute multi request
     * @return array
     */
    protected function executeMultiRequest()
    {
        $manager = new WsMultiQueryManager();

        // Add Handler
        foreach($this->handler as $handler)
        {
            foreach($handler as $query)
            {
                $manager->addQuery($query['object']);
            }
        }

        // Exec Request
        do
        {
            $manager->execQueries();
        }
        while ($manager->waitForExec());

        // Get Results
        $bodies = array();

        foreach($this->handler as $name => $handler)
        {
            foreach($handler as $key => $query)
            {
                $bodies[$query['id']] = $this->execQuery($query['object'], $query['id'], true);

                $manager->removeQuery($query['object']);
            }
        }

        $manager->close();

        return $bodies;
    }

    /**
     * Exec the query
     * @param WsQueryBase $query
     * @param string $id
     * @param boolean $isMulti
     * @return string
     */
    protected function execQuery(WsQueryBase $query, $id, $isMulti = false)
    {
        $return = $query->exec();

        if(null === $return || false === $return)
        {
            throw new QueryException('Curl Error : ' . $query->getError());
        }

        list($headers, $body) = explode("\r\n\r\n", $return, 2);

        $this->setLastHeaders($id, $headers);
        $this->setLastStats($id, $query->getInfo());

        if($this->logger)
        {
            $this->logger->logQuery($id, $query->getMethod() . ($isMulti ? ' (Multi)' : ''), $query->getParam(), $this->getLastStats($id));
        }

        return $query->decodeBody($body);
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
}
