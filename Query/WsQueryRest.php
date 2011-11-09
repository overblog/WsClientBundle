<?php

namespace Overblog\WsClientBundle\Query;

use Overblog\WsClientBundle\Query\WsQueryBase;
use Overblog\WsClientBundle\Exception\ConfigurationException;

/**
 * cURL Rest query Object
 *
 * @author Xavier HAUSHERR
 */

class WsQueryRest extends WsQueryBase
{
    /**
     * Init cURL instance
     * @return resource
     */
    protected function init()
    {
        $this->handle = curl_init();

        $url = preg_replace('#([^:])//#', '$1/', $this->host . $this->url);

        // Options
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $this->param);
        curl_setopt($this->handle, CURLOPT_CONNECTTIMEOUT_MS, self::TIMEOUT);
        curl_setopt($this->handle, CURLOPT_HEADER, true);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_USERAGENT, 'OverBlog Rest Client');

        if (self::DELETE === $this->method)
        {
            curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $this->method);
        } else
        {
            curl_setopt($this->handle, $this->method, true);
        }

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
        return $body;
    }
}