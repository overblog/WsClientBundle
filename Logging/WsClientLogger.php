<?php

namespace Overblog\WsClientBundle\Logging;

class WsClientLogger implements WsLoggerInterface
{
    /** @var array $queries Executed REST queries. */
    public $queries = array();

    public function logQuery($queryId, $method, $params, Array $stats)
    {
        $this->queries[$queryId] = array('method' => $method, 'param' => $params, 'stats' => $stats);
    }
}