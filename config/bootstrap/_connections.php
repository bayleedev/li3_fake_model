<?php

use lithium\data\Connections;

Connections::add('default', array(
	'type' => 'MongoDb',
	'adapter' => 'MongoDb',
	'host' => '127.0.0.1',
	'database' => 'test'
));