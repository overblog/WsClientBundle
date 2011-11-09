<?php
/**
 * UNIT TEST
 *
 * @author Xavier HAUSHERR
 */
namespace Overblog\WsClientBundle\Test\Lib;

use Overblog\WsClientBundle\Client\WsClient;
use Overblog\WsClientBundle\Query\WsQueryRest;
use Overblog\WsClientBundle\Logging\WsClientLogger;
use Symfony\Bridge\Monolog\Logger;

class WsClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var WsClient
     */
    protected $client;

    protected function setUp()
    {
        $this->client = new WsClient(array(
            'tumblr' => array(
                'url' => 'http://api.tumblr.com/v2/',
                'type' => 'rest'
            ),
            'tumblr2' => array(
                'url' => 'http://api.tumblr.com/v2/',
                'type' => 'json'
            )
        ), new WsClientLogger());
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf('Overblog\WsClientBundle\Client\WsClient', $this->client->getConnection('tumblr'));

        $this->setExpectedException('Overblog\WsClientBundle\Exception\ConfigurationException',
			'Unable to find configuration "NexistePas"'
		);

        $this->client->getConnection('NexistePas');
    }

    public function testGetError()
    {
        $this->setExpectedException('Overblog\WsClientBundle\Exception\ConfigurationException',
			'No connection set.'
		);

        $response = $this->client->get('/blog/david.tumblr.com/avatar/64')->exec();
    }

    public function testGet()
    {
        $response = $this->client->getConnection('tumblr')->get('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_1'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_1', 'url'));
        $this->assertEquals('Connection: close', end($this->client->getLastHeaders('tumblr_1')));
    }

    public function testPost()
    {
        $response = $this->client->getConnection('tumblr')->post('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_1'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_1', 'url'));
    }

    public function testPostJson()
    {
        $response = $this->client->getConnection('tumblr2')->post('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr2_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr2_1'));
        $this->assertEquals('http://api.tumblr.com/v2/', $this->client->getLastStat('tumblr2_1', 'url'), 'only host is used');
    }

    public function testPut()
    {
        $response = $this->client->getConnection('tumblr')->put('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_1'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_1', 'url'));
    }

    public function testDelete()
    {
        $response = $this->client->getConnection('tumblr')->delete('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_1'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_1', 'url'));
    }

    public function testGetMulti()
    {
        $response = $this->client->getConnection('tumblr')
                ->get('/blog/david.tumblr.com/avatar/64')
                ->get('/blog/david.tumblr.com/avatar/512')->exec();

        $this->assertArrayHasKey('tumblr_1', $response);
        $this->assertArrayHasKey('tumblr_2', $response);

    }

    public function testCurlError()
    {
        $this->setExpectedException('Overblog\WsClientBundle\Exception\QueryException',
			"Couldn't resolve host 'xxx'"
		);

        $client = new WsClient(array('tumblr' => array(
            'url' => 'http://xxx/',
            'type' => 'rest'
        )));

        $response = $client->getConnection('tumblr')->get('/blog/david.tumblr.com/avatar/64')->exec();
    }

    public function testMultiCurlError()
    {
        $this->setExpectedException('Overblog\WsClientBundle\Exception\QueryException',
			"Couldn't resolve host 'xxx'"
		);

        $client = new WsClient(array('tumblr' => array(
            'url' => 'http://xxx/',
            'type' => 'rest'
        )));

        $response = $client->getConnection('tumblr')
                ->get('/blog/david.tumblr.com/avatar/64')
                ->get('/blog/david.tumblr.com/avatar/512')->exec();
    }

    public function testException()
    {
        $e = new \Overblog\WsClientBundle\Exception\QueryException('TEST');
        $this->assertEquals('TEST', $e);

        $e = new \Overblog\WsClientBundle\Exception\ConfigurationException('TEST');
        $this->assertEquals('TEST', $e);
    }

    public function testUnknowMethodRest()
    {
        $this->setExpectedException('Overblog\WsClientBundle\Exception\ConfigurationException',
			"Unknow method"
		);

        $ch = new WsQueryRest('XXX', 'http://', '/v2');
    }
}