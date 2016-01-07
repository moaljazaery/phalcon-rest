<?php

/**
 * Collections let us define groups of routes that will all use the same controller.
 * We can also set the handler to be lazy loaded.  Collections can share a common prefix.
 * @var $exampleCollection
 */

// This is an Immeidately Invoked Function in php.  The return value of the
// anonymous function will be returned to any file that "includes" it.
// e.g. $collection = include('example.php');
return call_user_func(function(){

	$exampleCollection = new \Phalcon\Mvc\Micro\Collection();

	$exampleCollection
		// VERSION NUMBER SHOULD BE FIRST URL PARAMETER, ALWAYS
		->setPrefix('/v1/users')
		// Must be a string in order to support lazy loading
		->setHandler('\PhalconRest\Controllers\UsersController')
		->setLazy(true);

	// Set Access-Control-Allow headers.
	$exampleCollection->options('/', 'optionsBase');
	$exampleCollection->options('/{id}', 'optionsOne');

	// First paramter is the route, which with the collection prefix here would be GET /example/
	// Second paramter is the function name of the Controller.
	$exampleCollection->get('/', 'getList');
	// This is exactly the same execution as GET, but the Response has no body.
	$exampleCollection->head('/', 'getList');

	// $id will be passed as a parameter to the Controller's specified function
	$exampleCollection->get('/{id:[0-9]+}', 'get');
	$exampleCollection->head('/{id:[0-9]+}', 'get');
	$exampleCollection->post('/', 'create');
	$exampleCollection->post('/login', 'login');
	$exampleCollection->put('/{id:[0-9]+}', 'edit');

	$exampleCollection->delete('/{id:[0-9]+}', 'delete');
	$exampleCollection->patch('/{id:[0-9]+}', 'patch');

	return $exampleCollection;
});