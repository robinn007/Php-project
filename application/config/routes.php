<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/


$route['default_controller'] = 'students';
$route['404_override'] = '';

// Student routes
// Student routes
$route['students'] = 'students/index';
$route['dashboard'] = 'students/dashboard';
$route['students/manage/(:any)'] = 'students/manage/$1'; // For add
$route['students/manage/(:any)/(:num)'] = 'students/manage/$1/$2'; // For edit/delete with ID
$route['students/manage'] = 'students/manage/add'; // Default to add

// Clean URL aliases
$route['students/add'] = 'students/manage/add';
$route['students/edit/(:num)'] = 'students/manage/edit/$1';
$route['students/delete/(:num)'] = 'students/manage/delete/$1';

// Other routes
$route['test-db'] = 'students/test_db';
$route['setup-database'] = 'students/setup_database';
$route['auth/signup'] = 'auth/signup';
$route['auth/login'] = 'auth/login';


// // Default routes
// $route['default_controller'] = 'students';
// $route['404_override'] = '';

// // Custom routes for cleaner URLs
// $route['dashboard'] = 'students/dashboard';
// $route['students'] = 'students/index';
// $route['students/add'] = 'students/add';
// $route['students/create'] = 'students/create';
// $route['students/edit/(:num)'] = 'students/edit/$1';
// $route['students/update'] = 'students/update';
// $route['students/delete/(:num)'] = 'students/delete/$1';
// $route['test-db'] = 'students/test_db';

// // Alternative clean routes (optional)
// $route['add-student'] = 'students/add';
// $route['edit-student/(:num)'] = 'students/edit/$1';
// $route['delete-student/(:num)'] = 'students/delete/$1';

// $route['auth/signup'] = 'auth/signup';
// $route['auth/login'] = 'auth/login';


// $route['students/create'] = 'Students/create';
// $route['students/update'] = 'Students/update'; // Add this missing route
// $route['students/add'] = 'Students/add';
// $route['students/edit/(:num)'] = 'Students/edit/$1';
// $route['students/delete/(:num)'] = 'Students/delete/$1';
// $route['students'] = 'Students/index';
// $route['students/test_db'] = 'students/test_db'; // to be remove for production

//$route['default_controller'] = 'welcome';
// $route['default_controller'] = 'students';
// $route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */