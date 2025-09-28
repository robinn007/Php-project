<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// $route['default_controller'] = 'welcome';
// $route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;



// Auth routes
// $route['auth/test'] = 'auth/test_endpoint'; // ADD THIS FOR TESTING
// $route['login'] = 'auth/login';
// $route['signup'] = 'auth/signup';
// $route['logout'] = 'auth/logout';
// $route['auth/get_csrf'] = 'auth/get_csrf';
// $route['auth/check_auth'] = 'auth/check_auth';
// $route['auth/login'] = 'auth/login'; // EXPLICIT ROUTE
// $route['auth/signup'] = 'auth/signup'; // EXPLICIT ROUTE FOR SIGNUP
// $route['auth/check_session'] = 'auth/check_session';

// Student routes
// $route['students'] = 'students/index';
// $route['students/dashboard'] = 'students/dashboard';
// $route['students/add'] = 'students/index';
// $route['students/edit/(:num)'] = 'students/index';
// $route['students/deleted'] = 'students/deleted';
// $route['students/restore/(:num)'] = 'students/restore/$1';
// $route['students/permanent_delete/(:num)'] = 'students/permanent_delete/$1';
// $route['students/get/(:num)'] = 'students/get/$1';
// $route['students/manage'] = 'students/manage';
// $route['students/manage/(:any)'] = 'students/manage/$1';
// $route['students/manage/(:any)/(:num)'] = 'students/manage/$1/$2';
// $route['students/test_db'] = 'students/test_db';
// $route['students/setup_database'] = 'students/setup_database';

// Clicks routes
// $route['clicks'] = 'students/clicks';
// $route['clicks/export'] = 'students/export'; 

// Other routes
// $route['test'] = 'test/index';
// $route['test/(:any)'] = 'test/$1';
// $route['setup'] = 'setup/index';
// $route['setup/database'] = 'setup/database';
// $route['migrate'] = 'migrate/index';
// $route['migrate/rollback/(:num)'] = 'migrate/rollback/$1';

// View routes
// $route['view/(:any)'] = 'view/index/$1';
// $route['partials/(:any)'] = 'view/partial/$1';
// $route['layout/(:any)'] = 'view/layout/$1';
// $route['students/export'] = 'students/export';
// $route['auth/test_logout'] = 'auth/test_logout';

// chat route
//$route['chat'] = 'view/index/chat';

// Chat API route
//$route['auth/get_messages'] = 'auth/get_messages';

// $route['default_controller'] = 'welcome';
// $route['404_override'] = '';
// $route['translate_uri_dashes'] = FALSE;

// Auth routes
// $route['auth/test'] = 'auth/test_endpoint';
// $route['login'] = 'auth/login';
// $route['signup'] = 'auth/signup';
// $route['logout'] = 'auth/logout';
// $route['auth/get_csrf'] = 'auth/get_csrf';
// $route['auth/check_auth'] = 'auth/check_auth';
// $route['auth/login'] = 'auth/login';
// $route['auth/signup'] = 'auth/signup';
// $route['auth/check_session'] = 'auth/check_session';
// $route['auth/get_messages'] = 'auth/get_messages';
// $route['auth/get_last_messages_summary'] = 'auth/get_last_messages_summary';

// Student API routes (these should come before view routes to avoid conflicts)
// $route['students/manage'] = 'students/manage';
// $route['students/manage/(:any)'] = 'students/manage/$1';
// $route['students/manage/(:any)/(:num)'] = 'students/manage/$1/$2';
// $route['students/get/(:num)'] = 'students/get/$1';
// $route['students/restore/(:num)'] = 'students/restore/$1';
// $route['students/permanent_delete/(:num)'] = 'students/permanent_delete/$1';
// $route['students/test_db'] = 'students/test_db';
// $route['students/setup_database'] = 'students/setup_database';
// $route['students/export'] = 'students/export';
// $route['students/deleted'] = 'students/deleted';

// Clicks routes
// $route['clicks'] = 'students/clicks';
// $route['clicks/export'] = 'students/export'; 

// Other routes
// $route['test'] = 'test/index';
// $route['test/(:any)'] = 'test/$1';
// $route['setup'] = 'setup/index';
// $route['setup/database'] = 'setup/database';
// $route['migrate'] = 'migrate/index';
// $route['migrate/rollback/(:num)'] = 'migrate/rollback/$1';
// $route['auth/test_logout'] = 'auth/test_logout';

// Special route for students API endpoint (to handle AJAX requests)
//$route['students$'] = 'students/index';

// View routes (these should come after API routes)
// $route['view/(:any)'] = 'view/index/$1';
// $route['partials/(:any)'] = 'view/partial/$1';
// $route['layout/(:any)'] = 'view/layout/$1';

// SPA routes (these should be last)
// $route['students/dashboard'] = 'students/index';
// $route['students/add'] = 'students/index';
// $route['students/edit/(:num)'] = 'students/index';
// $route['chat'] = 'view/index/chat';


$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

// Debug route to test create_group
$route['debug/test_create_group'] = 'auth/create_group';

// Auth routes
$route['auth/test'] = 'auth/test_endpoint';
$route['login'] = 'auth/login';
$route['signup'] = 'auth/signup';
$route['logout'] = 'auth/logout';
$route['auth/get_csrf'] = 'auth/get_csrf';
$route['auth/check_auth'] = 'auth/check_auth';
$route['auth/login'] = 'auth/login';
$route['auth/signup'] = 'auth/signup';
$route['auth/check_session'] = 'auth/check_session';
$route['auth/get_messages'] = 'auth/get_messages';
$route['auth/get_last_messages_summary'] = 'auth/get_last_messages_summary';

// Group chat routes (MAKE SURE THESE ARE PRESENT)
$route['auth/create_group'] = 'auth/create_group';
$route['auth/get_groups'] = 'auth/get_groups';
$route['auth/get_group_members/(:num)'] = 'auth/get_group_members/$1';

// Setup routes
$route['setup_group_chat'] = 'setup_group_chat/index';
$route['setup_group_chat/reset'] = 'setup_group_chat/reset';

// Student API routes
$route['students/manage'] = 'students/manage';
$route['students/manage/(:any)'] = 'students/manage/$1';
$route['students/manage/(:any)/(:num)'] = 'students/manage/$1/$2';
$route['students/get/(:num)'] = 'students/get/$1';
$route['students/restore/(:num)'] = 'students/restore/$1';
$route['students/permanent_delete/(:num)'] = 'students/permanent_delete/$1';
$route['students/test_db'] = 'students/test_db';
$route['students/setup_database'] = 'students/setup_database';
$route['students/export'] = 'students/export';
$route['students/deleted'] = 'students/deleted';

// Clicks routes
$route['clicks'] = 'students/clicks';
$route['clicks/export'] = 'students/export'; 

// Other routes
$route['test'] = 'test/index';
$route['test/(:any)'] = 'test/$1';
$route['setup'] = 'setup/index';
$route['setup/database'] = 'setup/database';
$route['migrate'] = 'migrate/index';
$route['migrate/rollback/(:num)'] = 'migrate/rollback/$1';
$route['auth/test_logout'] = 'auth/test_logout';

// Special route for students API endpoint
$route['students$'] = 'students/index';

// View routes
$route['view/(:any)'] = 'view/index/$1';
$route['partials/(:any)'] = 'view/partial/$1';
$route['layout/(:any)'] = 'view/layout/$1';

// SPA routes
$route['students/dashboard'] = 'students/index';
$route['students/add'] = 'students/index';
$route['students/edit/(:num)'] = 'students/index';
$route['chat'] = 'view/index/chat';