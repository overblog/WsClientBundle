<?php
/**
 * UNIT TEST
 *
 * @author Xavier HAUSHERR
 */
namespace Overblog\WsClientBundle\Test\Client;

use Overblog\WsClientBundle\Client\WsClient;
use Overblog\WsClientBundle\Query\WsQueryRest;
use Overblog\WsClientBundle\Logging\WsClientLogger;

class WsClientCreateTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var WsClient
     */
    protected $client;

    protected function setUp()
    {
        $this->client = new WsClient(array(), new WsClientLogger());
    }

    public function testGet()
    {
        $response = $this->client
                         ->createConnection('tumblr', 'http://api.tumblr.com/v2/', 'rest', 2000)
                         ->get('/blog/david.tumblr.com/avatar/64')
                         ->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_1'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_1', 'url'));
    }
}

