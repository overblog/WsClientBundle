<?php
namespace Overblog\RestClientBundle\Lib;

class RestClient
{
    /**
     * Timeout for Web Service call
     * @var int
     */
    const TIMEOUT = 1000;

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
     * Constructor - Save dependecies
     * @param array $urls
     */
    public function __construct(Array $urls)
    {
        $this->urls = $urls;
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
            throw new \Exception('Unable to find configuration "' . $name . '"');
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
        $this->handler[$this->active_connection][] = $this->createRequest(CURLOPT_HTTPGET, $uri, $param);

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
        $this->handler[$this->active_connection][] = $this->createRequest(CURLOPT_HTTPPOST, $uri, $param);

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
        $this->handler[$this->active_connection][] = $this->createRequest(CURLOPT_HTTPPUT, $uri, $param);

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
            throw new \Exception('No connection set.');
        }

        $ch = curl_init();

        // Options
        $url = preg_replace('#([^:])//#', '$1/', $this->urls[$this->active_connection] . $uri);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OverBlog Rest Client');

        if ('DELETE' === $method)
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        } else
        {
            curl_setopt($ch, $method, true);
        }

        return $ch;
    }

    /**
     * Execute single request
     * @param cURL $ch
     * @param string $name
     * @return array
     */
    protected function executeSingleRequest($ch, $name)
    {
        //Exec
        $return = curl_exec($ch);

        if(false === $return)
        {
            throw new \Exception('Curl Error : ' . curl_error($ch));
        }

        list($headers, $body) = explode("\r\n\r\n", $return, 2);

        $this->setLastHeaders($name, $headers);
        $this->setLastStats($name, curl_getinfo($ch));

        $body = $this->decodeBody($body);

        curl_close($ch);

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
                curl_multi_add_handle($mh, $ch);
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

                $return = curl_multi_getcontent($ch);

                if(false === $return)
                {
                    throw new \Exception('Curl Error : ' . curl_error($ch));
                }

                list($headers, $body) = explode("\r\n\r\n", $return, 2);

                $this->setLastHeaders($cle, $headers);
                $this->setLastStats($cle, curl_getinfo($ch));

                $bodies[$cle] = $this->decodeBody($body);

                curl_multi_remove_handle($mh, $ch);
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