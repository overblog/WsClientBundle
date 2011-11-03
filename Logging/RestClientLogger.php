<?php

namespace Overblog\RestClientBundle\Logging;

use Symfony\Component\HttpKernel\Log\LoggerInterface;

class RestClientLogger
{
    /** @var array $queries Executed REST queries. */
    public $queries = array();

    /** @var boolean $enabled If Debug Stack is enabled (log queries) or not. */
    public $enabled = true;

    public function logQuery($method, $params, $key, $stats)
    {
        $this->queries[$key] = array('method' => $method, 'param' => $params, 'stats' => $stats);
    }
}