<?php

namespace Overblog\WsClientBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Overblog\WsClientBundle\Logging\WsClientLogger;


/**
 * WS Collector
 *
 * @author Xavier HAUSHERR
 */

class WsClientDataCollector extends DataCollector
{
    protected $logger;

    public function __construct(WsClientLogger $logger = null)
    {
        $this->logger      = $logger;
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null)
    {
        $this->data = array(
            'queries' => null !== $this->logger ? $this->logger->queries : array(),
        );
    }

    public function getName()
    {
        return 'ws_client';
    }

    public function getQueryCount()
    {
        return count($this->data['queries']);
    }

    public function getTime()
    {
        $time = 0;
        foreach ($this->data['queries'] as $query)
        {
            $time += $query['stats']['total_time'];
        }

        return $time;
    }

    public function getQueries()
    {
        return $this->data['queries'];
    }
    
    public function reset()
    {
        $this->data = [];
    }
}
