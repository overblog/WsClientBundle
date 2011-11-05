<?php

namespace Overblog\RestClientBundle\Logging;

use Overblog\CommonBundle\Interfaces\RestLogger;

class RestClientLogger implements RestLogger
{
    /** @var array $queries Executed REST queries. */
    public $queries = array();

    public function logQuery($queryId, $method, Array $params, Array $stats)
    {
        $this->queries[$queryId] = array('method' => $method, 'param' => $params, 'stats' => $stats);
    }
}