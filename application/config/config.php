<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['base_url'] = 'http://localhost/';

// Session configuration - DISABLE DATABASE SESSIONS
$config['sess_driver'] = 'files';
$config['sess_cookie_name'] = 'ci_session';
$config['sess_expiration'] = 600;
$config['sess_encrypt_cookie'] = FALSE;
$config['sess_use_database'] = FALSE;  // **CHANGED: Disable database sessions**
$config['sess_table_name'] = 'ci_sessions';
$config['sess_match_ip'] = FALSE;
$config['sess_match_useragent'] = TRUE;
$config['sess_time_to_update'] = 300;
$config['sess_save_path'] = APPPATH . 'cache/sessions/';  // **IMPORTANT: File-based sessions**
$config['sess_expire_on_close'] = FALSE;

$config['index_page'] = '';
$config['uri_protocol'] = 'REQUEST_URI';
$config['url_suffix'] = '';
$config['language']	= 'english';
$config['charset'] = 'UTF-8';
$config['enable_hooks'] = FALSE;
$config['subclass_prefix'] = 'MY_';
$config['allow_get_array'] = TRUE;
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['directory_trigger'] = 'd';
$config['log_threshold'] = 4;
$config['log_path'] = '';
$config['log_date_format'] = 'Y-m-d H:i:s';
$config['cache_path'] = '';
$config['encryption_key'] = '5d34a8a29ac54f4d9cdc58e20521eb62';

// Cookie configuration
$config['cookie_prefix'] = "";
$config['cookie_domain'] = "";
$config['cookie_path'] = "/";
$config['cookie_secure'] = FALSE;

// CSRF configuration - TEMPORARILY DISABLE FOR DEBUGGING
$config['global_xss_filtering'] = FALSE;
$config['csrf_protection'] = FALSE;  // **CHANGED: Disable CSRF for debugging**
$config['csrf_token_name'] = 'ci_csrf_token';
$config['csrf_cookie_name'] = 'csrf_token';
$config['csrf_expire'] = 600;
$config['csrf_regenerate'] = FALSE;
$config['csrf_exclude_uris'] = array();

$config['allowed_origins'] = ['http://localhost'];
$config['compress_output'] = FALSE;
$config['time_reference'] = 'local';
$config['rewrite_short_tags'] = FALSE;
$config['proxy_ips'] = '';