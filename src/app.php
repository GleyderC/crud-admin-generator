<?php

/*
 * This file is part of the CRUD Admin Generator project.
 *
 * Author: Jon Segador <jonseg@gmail.com>
 * Web: http://crud-admin-generator.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class queryData {
	public $start;
	public $recordsTotal;
	public $recordsFiltered;
	public $data;

	function queryData() {
	}
}

use Doctrine\DBAL\Schema\Table;
use Silex\Application;

$app = new Application();

$app->register(new Silex\Provider\TwigServiceProvider(), array(
	'twig.path' => __DIR__.'/../web/views',
));
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
	'translator.messages' => array(),
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(

		'dbs.options' => array(
			'db' => array(
				'driver'   => 'pdo_mysql',
				'dbname'   => 'DATABASE_NAME',
				'host'     => '127.0.0.1',
				'user'     => 'DATABASE_USER',
				'password' => 'DATABASE_PASS',
				'charset'  => 'utf8',
			),
		)
));

//Adding authentication
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
			'security.firewalls' => 
				array(	
						'login' => array(
								'pattern' => '^/login$',
						),
						'admin' => array(
						'pattern' => '/',
						'form' =>
							array('login_path' => '/login', 'check_path' => '/admin/login_check'),
						'logout' => array('logout_path' => '/admin/logout', 'invalidate_session' => true),
						'users' => $app->share(function () use ($app) {
							return new UserProvider($app['db']);
						})
					)
				)
			)
);

//  Create users table
$schema = $app['db']->getSchemaManager();
if (!$schema->tablesExist('users')) {
		$users = new Table('users');
		$users->addColumn('id', 'integer', array('unsigned' => true, 'autoincrement' => true));
		$users->setPrimaryKey(array('id'));
		$users->addColumn('username', 'string', array('length' => 32));
		$users->addUniqueIndex(array('username'));
		$users->addColumn('password', 'string', array('length' => 255));
		$users->addColumn('roles', 'string', array('length' => 255));
		$schema->createTable($users);
		
		
		$app['db']
			->insert('users', array(
				'username' => 'admin',
				//Password: foo 
				'password' =>
				'5FZ2Z8QIkA7UTZ4BYkoC+GsReLf569mSKDsfods6LYQ8t+a8EW9oaircfMpmaLbPBh4FOBiiFyLfuZmTSUwzZg==',
				'roles' => 'ROLE_ADMIN'
				)
			);
}


$app['asset_path'] = '/resources';
$app['debug'] = true;
	// array of REGEX column name to display for foreigner key insted of ID
	// default used :'name','title','e?mail','username'
$app['usr_search_names_foreigner_key'] = array();



return $app;
