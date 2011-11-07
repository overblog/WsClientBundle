<?php
namespace Overblog\RestClientBundle\Logging;

/**
 * RestLogger - Interface to log Rest queries
 * @author Xavier HAUSHERR
 */

interface RestLoggerInterface
{
    /**
     * Log Rest Queries
     * @param string $queryId
     * @param string $method
     * @param array $params
     * @param array $stats
     */
    public function logQuery($queryId, $method, Array $params, Array $stats);
}