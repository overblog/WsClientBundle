<?php

namespace Overblog\WsClientBundle\Query;

use Overblog\WsClientBundle\Query\WsQueryBase;
use Overblog\WsClientBundle\Exception\ConfigurationException;

/**
 * cURL JSON-RPC query Object
 *
 * @author Xavier HAUSHERR
 */

class WsQueryJson extends WsQueryBase
{
    /**
     * Request Headers
     * @var Array
     */
    protected $headers = array(
        'Content-type' => 'application/jsonrequest'
    );

    /**
     * Init cURL instance
     * @return resource
     */
    protected function init()
    {
        $this->handle = curl_init();

        // Options
        curl_setopt($this->handle, CURLOPT_URL, $this->host);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT);
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_USERAGENT, 'OverBlog JSON-RPC Client');
        curl_setopt($this->handle, CURLOPT_POST, true); //Only POST
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->getHeaders());

        curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode(array(
            'id' => $this->id,
            'method' => $this->url,
            'params' => $this->param
        )));

        return $this->handle;
    }

    /**
     * Decode body response
     *
     * @param type $body
     * @return mixed
     */
    public function decodeBody($body)
    {
        return json_decode($body);
    }
}