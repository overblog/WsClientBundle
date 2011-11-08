<?php
namespace Overblog\RestClientBundle\Manager;

use Overblog\RestClientBundle\Lib\RestQuery;

/**
 * REST request multi query manager
 *
 * @author Xavier HAUSHERR
 */

class RestMultiQueryManager
{
    protected $manager;
    protected $active;

    /**
     * Init multi queries manager
     */
    public function __construct()
    {
        $this->manager = curl_multi_init();
    }

    /**
     * Add a query to manager
     * @param RestQuery $query
     */
    public function addQuery(RestQuery $query)
    {
        $query->setMulti();

        curl_multi_add_handle($this->manager, $query->getHandle());
    }

    /**
     * Remove a query from manager
     * @param RestQuery $query
     */
    public function removeQuery(RestQuery $query)
    {
        $query->setSingle();

        curl_multi_remove_handle($this->manager, $query->getHandle());
    }

    /**
     * Exec a query
     */
    public function execQueries()
    {
        curl_multi_exec($this->manager, $this->active);
    }

    /**
     * Wait for query exec
     * @return int
     */
    public function waitForExec()
    {
        return $this->active > 0;
    }

    /**
     * Close manager
     */
    public function close()
    {
        curl_multi_close($this->manager);
    }
}