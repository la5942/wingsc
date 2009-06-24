<?php defined('SYSPATH') or die('No direct script access.');

//-- Environment setup --------------------------------------------------------

/**
 * Set the default time zone.
 *
 * @see  http://docs.kohanaphp.com/features/localization#time
 * @see  http://php.net/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Enable the Kohana auto-loader.
 *
 * @see  http://docs.kohanaphp.com/features/autoloading
 * @see  http://php.net/spl_autoload_register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Enable Kohana exception handling, adds stack traces and error source.
 *
 * @see  http://docs.kohanaphp.com/features/exceptions
 * @see  http://php.net/set_exception_handler
 */
set_exception_handler(array('Kohana', 'exception_handler'));

/**
 * Enable Kohana error handling, converts all PHP errors to exceptions.
 *
 * @see  http://docs.kohanaphp.com/features/exceptions
 * @see  http://php.net/set_error_handler
 */
set_error_handler(array('Kohana', 'error_handler'));

/**
 * Set the production status by the domain.
 */
define('IN_PRODUCTION', $_SERVER['SERVER_NAME'] !== 'localhost');

//-- Kohana configuration -----------------------------------------------------

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 * - base_url:   path, and optionally domain, of your application
 * - index_file: name of your index file, usually "index.php"
 * - charset:    internal character set used for input and output
 * - profile:    enable or disable internal profiling
 * - caching:    enable or disable internal caching
 */
Kohana::init(array(
	'charset'    => 'utf-8',
	'base_url'   => '/wingsc/',
	'index_file' => FALSE,
	'profing'    => ! IN_PRODUCTION,
	'caching'    => IN_PRODUCTION
));

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'database'   => MODPATH.'database',   // Database access
	));

/**
 * Attach the file write to logging. Any Kohana_Log object can be attached,
 * and multiple writers are supported.
 */
Kohana::$log->attach(new Kohana_Log_File(APPPATH.'logs'));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */
Route::set('work', 'did(/<project>(/<action>))', array(
		'project' => '.+?(?:/with/.+?)?',
	))
	->defaults(array(
		'controller' => 'portfolio',
	));

Route::set('calendar', 'will(/<action>)')
	->defaults(array(
		'controller' => 'calendar',
	));

Route::set('contact', 'for(/<action>)')
	->defaults(array(
		'controller' => 'contact',
	));

Route::set('admin', 'admin(/<action>(/<id>))')
	->defaults(array(
		'controller' => 'admin'
	));

Route::set('default', '(<page>)', array('page' => '.+'))
	->defaults(array(
		'controller' => 'static',
		'action'     => 'load',
		'page'       => 'is'
	));

/**
 * Execute the main request using PATH_INFO. If no URI source is specified,
 * the URI will be automatically detected.
 */
$request = Request::instance($_SERVER['PATH_INFO']);

try
{
	// Attempt to execute the response
	$request->execute();
}
catch (Foo_Exception $e)
{
	// Create a 404 response
	$request->status   = 404;
	$request->response = View::factory('template')
		->set('title', '404')
		->set('content', View::factory('errors/404'));
}

if ($request->send_headers()->response)
{
	// Get the total memory and execution time
	$total = array(
		'{memory_usage}'   => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2).'KB',
		'{execution_time}' => number_format(microtime(TRUE) - KOHANA_START_TIME, 5).' seconds');

	// Insert the totals into the response
	$request->response = str_replace(array_keys($total), $total, $request->response);
}


/**
 * Display the request response.
 */
echo $request->response;
