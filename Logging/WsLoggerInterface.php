<?php
namespace Overblog\WsClientBundle\Logging;

/**
 * WsLogger - Interface to log Ws queries
 * @author Xavier HAUSHERR
 */

interface WsLoggerInterface
{
    /**
     * Log Ws Queries
     * @param string $queryId
     * @param string $method
     * @param mixed $params
     * @param array $stats
     */
    public function logQuery($queryId, $method, $params, Array $stats);
}