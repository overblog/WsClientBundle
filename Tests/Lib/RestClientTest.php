<?php
/**
 * UNIT TEST
 *
 * @author Xavier HAUSHERR
 */
namespace Overblog\RestClientBundle\Test\Lib;

use Overblog\RestClientBundle\Lib\RestClient;
use Overblog\RestClientBundle\Lib\RestQuery;
use Overblog\RestClientBundle\Logging\RestClientLogger;
use Symfony\Bridge\Monolog\Logger;

class RestClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var RestClient
     */
    protected $client;

    protected function setUp()
    {
        $this->client = new RestClient(array(
            'tumblr' => 'http://api.tumblr.com/v2/'
        ), new RestClientLogger(new Logger('rest_client')));
    }

    public function testGetConnection()
    {
        $this->assertInstanceOf('Overblog\RestClientBundle\Lib\RestClient', $this->client->getConnection('tumblr'));

        $this->setExpectedException('Overblog\RestClientBundle\Exception\ConfigurationException',
			'Unable to find configuration "NexistePas"'
		);

        $this->client->getConnection('NexistePas');
    }

    public function testGetError()
    {
        $this->setExpectedException('Overblog\RestClientBundle\Exception\ConfigurationException',
			'No connection set.'
		);

        $response = $this->client->get('/blog/david.tumblr.com/avatar/64')->exec();
    }

    public function testGet()
    {
        $response = $this->client->getConnection('tumblr')->get('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_0', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_0'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_0', 'url'));
        $this->assertEquals('Connection: close', end($this->client->getLastHeaders('tumblr_0')));
    }

    public function testPost()
    {
        $response = $this->client->getConnection('tumblr')->post('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_0', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_0'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_0', 'url'));
    }

    public function testPut()
    {
        $response = $this->client->getConnection('tumblr')->put('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_0', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_0'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_0', 'url'));
    }

    public function testDelete()
    {
        $response = $this->client->getConnection('tumblr')->delete('/blog/david.tumblr.com/avatar/64')->exec();

        $this->assertArrayHasKey('tumblr_0', $response);
        $this->assertArrayHasKey('http_code', $this->client->getLastStats('tumblr_0'));
        $this->assertEquals('http://api.tumblr.com/v2/blog/david.tumblr.com/avatar/64', $this->client->getLastStat('tumblr_0', 'url'));
    }

    public function testGetMulti()
    {
        $response = $this->client->getConnection('tumblr')
                ->get('/blog/david.tumblr.com/avatar/64')
                ->get('/blog/david.tumblr.com/avatar/512')->exec();

        $this->assertArrayHasKey('tumblr_0', $response);
        $this->assertArrayHasKey('tumblr_1', $response);

    }

    public function testCurlError()
    {
        $this->setExpectedException('Overblog\RestClientBundle\Exception\QueryException',
			"Couldn't resolve host 'xxx'"
		);

        $client = new RestClient(array('tumblr' => 'http://xxx/'));

        $response = $client->getConnection('tumblr')->get('/blog/david.tumblr.com/avatar/64')->exec();
    }

    public function testMultiCurlError()
    {
        $this->setExpectedException('Overblog\RestClientBundle\Exception\QueryException',
			"Couldn't resolve host 'xxx'"
		);

        $client = new RestClient(array('tumblr' => 'http://xxx/'));

        $response = $client->getConnection('tumblr')
                ->get('/blog/david.tumblr.com/avatar/64')
                ->get('/blog/david.tumblr.com/avatar/512')->exec();
    }

    public function testException()
    {
        $e = new \Overblog\RestClientBundle\Exception\QueryException('TEST');
        $this->assertEquals('TEST', $e);

        $e = new \Overblog\RestClientBundle\Exception\ConfigurationException('TEST');
        $this->assertEquals('TEST', $e);
    }

    public function testUnknowMethod()
    {
        $this->setExpectedException('Overblog\RestClientBundle\Exception\ConfigurationException',
			"Unknow method"
		);

        $ch = new RestQuery('XXX', 'http://');
    }
}