<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        // parent::__construct();
        // error_reporting(E_ALL);
        // ini_set('display_errors', 0);
        // $this->load->model('User_model');
        // $this->load->library('session');
        // $this->load->library('security');
        // $this->output->set_content_type('application/json');
        // log_message('debug', 'Auth controller constructor called');
        // log_message('debug', 'Request headers: ' . json_encode(getallheaders()));
        // log_message('debug', 'CSRF token expected: ' . $this->security->get_csrf_token_name() . ' = ' . $this->security->get_csrf_hash());

        parent::__construct();
        error_reporting(E_ALL);
        ini_set('display_errors', 1); // Enable for debugging
        
        try {
            $this->load->model('User_model');
            $this->load->library('session');
            
            // Only load security if CSRF is enabled
            if ($this->config->item('csrf_protection')) {
                $this->load->library('security');
            }
            
            log_message('debug', 'Auth controller constructor completed successfully');
        } catch (Exception $e) {
            log_message('error', 'Auth constructor error: ' . $e->getMessage());
            // Don't set JSON here in constructor
        }
    }

    public function get_csrf() {
        try {
            log_message('debug', 'get_csrf method called');
            $response = array(
                'success' => true,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            log_message('debug', 'CSRF response: ' . json_encode($response));
            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'get_csrf error: ' . $e->getMessage());
            echo json_encode(array(
                'success' => false,
                'message' => 'CSRF error: ' . $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
    }

    public function login() {
        log_message('debug', 'Auth::login method called');
        log_message('debug', 'Raw input: ' . file_get_contents('php://input'));

        $post_data = $this->input->post();
        if ($post_data === false || empty($post_data)) {
            $raw_input = file_get_contents('php://input');
            $post_data = json_decode($raw_input, true);
            log_message('debug', 'Parsed JSON POST data: ' . json_encode($post_data));
        } else {
            log_message('debug', 'POST data: ' . json_encode($post_data));
        }

        log_message('debug', 'Is AJAX request: ' . ($this->input->is_ajax_request() ? 'Yes' : 'No'));

        $csrf_token = isset($post_data[$this->security->get_csrf_token_name()]) ? $post_data[$this->security->get_csrf_token_name()] : null;
        log_message('debug', 'Received CSRF token: ' . ($csrf_token ?: 'null') . ', Expected: ' . $this->security->get_csrf_hash());

        if ($this->session->userdata('user_id')) {
            log_message('debug', 'User already logged in');
            echo json_encode(array(
                'success' => false,
                'message' => 'User already logged in',
                'flashMessage' => 'User already logged in',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if (!$this->input->is_ajax_request()) {
            log_message('error', 'Non-AJAX request to login');
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid request method',
                'flashMessage' => 'Invalid request method',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $email = isset($post_data['email']) ? $post_data['email'] : null;
        $password = isset($post_data['password']) ? $post_data['password'] : null;
        log_message('debug', 'Login attempt with email: ' . ($email ?: 'null'));

        if (!$email || !$password) {
            log_message('error', 'Missing email or password');
            echo json_encode(array(
                'success' => false,
                'message' => 'Email and password are required',
                'flashMessage' => 'Email and password are required',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        try {
            if (!method_exists($this->User_model, 'login')) {
                log_message('error', 'User_model::login method does not exist');
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Server error: Login functionality unavailable',
                    'flashMessage' => 'Server error: Login functionality unavailable',
                    'flashType' => 'error',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
                exit();
            }

            $user = $this->User_model->login($email, $password);
            if ($user) {
                $this->session->set_userdata('user_id', $user['id']);
                $this->session->set_userdata('username', $user['username']);
                log_message('debug', 'Login successful for user: ' . $user['username']);
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Login successful',
                    'flashMessage' => 'Login successful',
                    'flashType' => 'success',
                    'user' => array(
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ),
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            } else {
                log_message('error', 'Invalid email or password for email: ' . $email);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid email or password',
                    'flashMessage' => 'Invalid email or password',
                    'flashType' => 'error',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'flashMessage' => 'Server error: ' . $e->getMessage(),
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function signup() {
        log_message('debug', 'Auth::signup method called');
        log_message('debug', 'Raw input: ' . file_get_contents('php://input'));

        $post_data = $this->input->post();
        if ($post_data === false || empty($post_data)) {
            $raw_input = file_get_contents('php://input');
            $post_data = json_decode($raw_input, true);
            log_message('debug', 'Parsed JSON POST data: ' . json_encode($post_data));
        } else {
            log_message('debug', 'POST data: ' . json_encode($post_data));
        }

        if ($this->session->userdata('user_id')) {
            echo json_encode(array(
                'success' => false,
                'message' => 'User already logged in',
                'flashMessage' => 'User already logged in',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if (!$this->input->is_ajax_request()) {
            log_message('error', 'Non-AJAX request to signup');
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid request method',
                'flashMessage' => 'Invalid request method',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        try {
            $data = array(
                'username' => isset($post_data['username']) ? $post_data['username'] : null,
                'email' => isset($post_data['email']) ? $post_data['email'] : null,
                'password' => isset($post_data['password']) ? password_hash($post_data['password'], PASSWORD_DEFAULT) : null
            );
            if ($this->User_model->signup($data)) {
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Registration successful',
                    'flashMessage' => 'Registration successful',
                    'flashType' => 'success',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Registration failed: Email or username already exists',
                    'flashMessage' => 'Registration failed: Email or username already exists',
                    'flashType' => 'error',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Signup error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'flashMessage' => 'Server error: ' . $e->getMessage(),
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function logout() {
        // Set JSON content type first
        $this->output->set_content_type('application/json');
        
        log_message('debug', '=== LOGOUT METHOD STARTED ===');
        log_message('debug', 'Request method: ' . $this->input->method());
        log_message('debug', 'Request URI: ' . $this->input->server('REQUEST_URI'));
        log_message('debug', 'User Agent: ' . $this->input->user_agent());
        
        // Log raw input
        $raw_input = file_get_contents('php://input');
        log_message('debug', 'Raw POST data: ' . $raw_input);
        log_message('debug', 'POST array: ' . json_encode($_POST));
        log_message('debug', 'GET array: ' . json_encode($_GET));
        
        try {
            // Check if user is logged in before logout
            $current_user_id = $this->session->userdata('user_id');
            $current_username = $this->session->userdata('username');
            
            log_message('debug', 'Current session user_id: ' . ($current_user_id ?: 'null'));
            log_message('debug', 'Current session username: ' . ($current_username ?: 'null'));
            log_message('debug', 'Session ID: ' . session_id());
            
            // Validate request method
            if (strtolower($this->input->method()) !== 'post') {
                log_message('error', 'Invalid request method: ' . $this->input->method());
                $response = array(
                    'success' => false,
                    'message' => 'Invalid request method, POST required',
                    'flashMessage' => 'Invalid request method',
                    'flashType' => 'error',
                    'debug_info' => array(
                        'method' => $this->input->method(),
                        'expected' => 'POST'
                    )
                );
                echo json_encode($response);
                return;
            }
            
            // Clear session data step by step
            log_message('debug', 'Starting session cleanup...');
            
            // Method 1: Unset specific userdata
            $session_keys_to_clear = array('user_id', 'username', 'last_activity');
            foreach ($session_keys_to_clear as $key) {
                if ($this->session->userdata($key)) {
                    log_message('debug', 'Clearing session key: ' . $key);
                    $this->session->unset_userdata($key);
                }
            }
            
            // Method 2: Destroy session
            log_message('debug', 'Destroying session...');
            $this->session->sess_destroy();
            
            log_message('debug', 'Session cleanup completed');
            
            // Prepare success response
            $response = array(
                'success' => true,
                'message' => 'Logout successful',
                'flashMessage' => 'You have been logged out successfully',
                'flashType' => 'success',
                'debug_info' => array(
                    'previous_user' => $current_username,
                    'session_destroyed' => true
                )
            );
            
            // Add CSRF token if protection is enabled
            if ($this->config->item('csrf_protection') && method_exists($this->security, 'get_csrf_hash')) {
                $response['csrf_token'] = $this->security->get_csrf_hash();
                log_message('debug', 'CSRF token added to response');
            }
            
            log_message('debug', 'Logout successful for user: ' . $current_username);
            echo json_encode($response);
            
        } catch (Exception $e) {
            log_message('error', 'Logout exception: ' . $e->getMessage());
            log_message('error', 'Exception trace: ' . $e->getTraceAsString());
            
            // Even if there's an error, try to clear the session
            try {
                $this->session->sess_destroy();
            } catch (Exception $nested_e) {
                log_message('error', 'Failed to destroy session: ' . $nested_e->getMessage());
            }
            
            $error_response = array(
                'success' => false,
                'message' => 'Logout error: ' . $e->getMessage(),
                'flashMessage' => 'Logout completed with errors',
                'flashType' => 'warning',
                'debug_info' => array(
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                )
            );
            
            echo json_encode($error_response);
        }
        
        log_message('debug', '=== LOGOUT METHOD ENDED ===');
        exit();
    }

    // Add this method for testing
    public function test_logout() {
        $this->output->set_content_type('application/json');
        
        echo json_encode(array(
            'success' => true,
            'message' => 'Test endpoint working',
            'session_id' => session_id(),
            'user_id' => $this->session->userdata('user_id'),
            'username' => $this->session->userdata('username'),
            'request_method' => $this->input->method(),
            'timestamp' => date('Y-m-d H:i:s')
        ));
        exit();
    }


    public function check_auth() {
        log_message('debug', 'Auth::check_auth method called');
        $is_logged_in = $this->session->userdata('user_id') ? true : false;
        
        $response = array(
            'success' => true,
            'is_logged_in' => $is_logged_in,
            'csrf_token' => $this->security->get_csrf_hash()
        );
        
        // Add user data if logged in
        if ($is_logged_in) {
            $user_id = $this->session->userdata('user_id');
            $this->load->model('User_model');
            $user = $this->User_model->get_user_by_id($user_id);
            
            if ($user) {
                $response['user'] = array(
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                );
            }
        }
        
        echo json_encode($response);
        exit();
    }

    public function check_session() {
        log_message('debug', 'Auth::check_session method called');
        try {
            $is_logged_in = $this->session->userdata('user_id') ? true : false;
            $response = array(
                'success' => true,
                'logged_in' => $is_logged_in,
                'csrf_token' => $this->security->get_csrf_hash()
            );

            if ($is_logged_in) {
                $user_id = $this->session->userdata('user_id');
                $user = $this->User_model->get_user_by_id($user_id);
                
                if ($user) {
                    $response['user'] = array(
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    );
                } else {
                    // If user_id exists but user not found, clear session
                    $this->session->unset_userdata('user_id');
                    $this->session->unset_userdata('username');
                    $this->session->sess_destroy();
                    $response['logged_in'] = false;
                    log_message('error', 'User not found for ID: ' . $user_id . ', session cleared');
                }
            }

            echo json_encode($response);
        } catch (Exception $e) {
            log_message('error', 'check_session error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'flashMessage' => 'Server error during session check',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function check_session_expiry() {
        $last_activity = $this->session->userdata('last_activity');
        if ($last_activity && (time() - $last_activity > 3600)) { // 1 hour
            $this->session->sess_destroy();
            return false;
        }
        $this->session->set_userdata('last_activity', time());
        return true;
    }
}