# OverBlog NG Ws Client #

What is this repository ?
----------------------

It's the place for the Web Service client library. Essentially to use with External Rest service and JSON-RCP (Internal API).

Installation and setup
----------------------

Juste add the following lines is your deps file:

    [OverblogWsClientBundle]
        git=git@github.com:ebuzzing/OverblogWsClientBundle.git
        target=/bundles/Overblog/WsClientBundle

You have now to tell Symfony2 autoloader where to find the API and the files that will be generated. Fire up your text editor and add the following lines to the *app/autoload.php* file:

    #app/autoload.php

        'Overblog'         => __DIR__.'/../vendor/overblog/src',

Let's register the WsClientBundle in the application kernel:

    #app/AppKernel.php
        // register your bundles
        new Overblog\WsClientBundle\OverblogWsClientBundle(),

You can now define your service settings in your main configuration file. The example below uses the yaml format:

    # app/config/config.yml
    overblog_ws_client:
        urls:
            *cnct_name*:
              url: http://api.domain.tld/
              type: rest
            *cnct_name_2*:
              url: http://api.domain.tld/json-rpc/user
              type: json

The *cnct_name* here is a name for your service. You can define several services using different names on different web service...

Usage
----------------------

Use the ws client is very simple. A simple Rest call look like this:

    $results = $this->get('ws_client')
                ->getConnection('*cnct_name*')
                    ->get('/ws/uri')
                ->exec();

You can call together several services by chaining call like this:

    $results = $this->get('ws_client')
                ->getConnection('*cnct_name_2*')
                    ->get('getMethod', array('param' => 'value'))
                    ->get('setMethod', array('param' => 'value'))
                ->getConnection('*cnct_name*')
                    ->get('/ws/uri')
                ->exec();

The execution time call will be the longuest call.