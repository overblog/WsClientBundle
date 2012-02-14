<?php

namespace Overblog\WsClientBundle\Client;

interface WsClientInterface
{
    /**
     * Get Request
     * @param string $uri
     * @param array $param
     * @return array
     */
    public function get($uri, Array $param = array());

    /**
     * Exec stocked requests
     * @return array
     */
    public function exec();

    /**
     * Get connection and save it into object
     *
     * @param string $name
     * @return WsClient
     */
    public function getConnection($name);
}
