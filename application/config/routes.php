<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['login'] = 'auth/login';
$route['signup'] = 'auth/signup';
$route['logout'] = 'auth/logout';
$route['auth/get_csrf'] = 'auth/get_csrf';
$route['auth/check_auth'] = 'auth/check_auth';
$route['auth/check_session'] = 'auth/check_session'; // Added new route

$route['students'] = 'students/index';
$route['students/dashboard'] = 'students/dashboard';
$route['students/add'] = 'students/index';
$route['students/edit/(:num)'] = 'students/index';
$route['students/deleted'] = 'students/deleted';
$route['students/restore/(:num)'] = 'students/restore/$1';
$route['students/permanent_delete/(:num)'] = 'students/permanent_delete/$1';
$route['students/get/(:num)'] = 'students/get/$1';
$route['students/manage'] = 'students/manage';
$route['students/manage/(:any)'] = 'students/manage/$1';
$route['students/manage/(:any)/(:num)'] = 'students/manage/$1/$2';
$route['students/test_db'] = 'students/test_db';
$route['students/setup_database'] = 'students/setup_database';

// Clicks routes
$route['clicks'] = 'students/clicks';
$route['clicks/export'] = 'students/export'; 

$route['test'] = 'test/index';
$route['test/(:any)'] = 'test/$1';

$route['setup'] = 'setup/index';
$route['setup/database'] = 'setup/database';

$route['migrate'] = 'migrate/index';
$route['migrate/rollback/(:num)'] = 'migrate/rollback/$1';

$route['view/(:any)'] = 'view/index/$1';
$route['partials/(:any)'] = 'view/partial/$1';
$route['layout/(:any)'] = 'view/layout/$1';