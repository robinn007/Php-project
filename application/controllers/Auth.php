<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        $this->load->model('User_model');
        $this->load->library('session');
        $this->load->library('security');
        $this->output->set_content_type('application/json');
        log_message('debug', 'Auth controller constructor called');
        log_message('debug', 'Request headers: ' . json_encode(getallheaders()));
        log_message('debug', 'CSRF token expected: ' . $this->security->get_csrf_token_name() . ' = ' . $this->security->get_csrf_hash());
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
        log_message('debug', 'Auth::logout method called');
        log_message('debug', 'Request method: ' . $this->input->method());
        log_message('debug', 'Raw input: ' . file_get_contents('php://input'));

        try {
            if ($this->input->method() === 'post') {
                // Clear session data
                $this->session->unset_userdata('user_id');
                $this->session->unset_userdata('username');
                $this->session->unset_userdata('last_activity');
                $this->session->sess_destroy();
                log_message('debug', 'Session destroyed successfully');
                echo json_encode(array(
                    'success' => true,
                    'message' => 'Logout successful',
                    'flashMessage' => 'Logout successful',
                    'flashType' => 'success',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            } else {
                log_message('error', 'Invalid logout request method: ' . $this->input->method());
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid request method, POST required',
                    'flashMessage' => 'Invalid request method, POST required',
                    'flashType' => 'error',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error during logout: ' . $e->getMessage(),
                'flashMessage' => 'Server error during logout',
                'flashType' => 'error',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
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