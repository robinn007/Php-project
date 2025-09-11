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

        // Try to parse JSON input if POST data is empty
        $post_data = $this->input->post();
        if ($post_data === false || empty($post_data)) {
            $raw_input = file_get_contents('php://input');
            $post_data = json_decode($raw_input, true);
            log_message('debug', 'Parsed JSON POST data: ' . json_encode($post_data));
        } else {
            log_message('debug', 'POST data: ' . json_encode($post_data));
        }

        log_message('debug', 'Is AJAX request: ' . ($this->input->is_ajax_request() ? 'Yes' : 'No'));

        // Check CSRF token
        $csrf_token = isset($post_data[$this->security->get_csrf_token_name()]) ? $post_data[$this->security->get_csrf_token_name()] : null;
        log_message('debug', 'Received CSRF token: ' . ($csrf_token ?: 'null') . ', Expected: ' . $this->security->get_csrf_hash());

        if ($this->session->userdata('user_id')) {
            log_message('debug', 'User already logged in');
            echo json_encode(array(
                'success' => false,
                'message' => 'User already logged in',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if (!$this->input->is_ajax_request()) {
            log_message('error', 'Non-AJAX request to login');
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid request method',
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
                    'user' => array(
                        'id' => $user['id'],
                        'username' => $user['username']
                    ),
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            } else {
                log_message('error', 'Invalid email or password for email: ' . $email);
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Invalid email or password',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function signup() {
        log_message('debug', 'Auth::signup method called');
        log_message('debug', 'Raw input: ' . file_get_contents('php://input'));

        // Try to parse JSON input if POST data is empty
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
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if (!$this->input->is_ajax_request()) {
            log_message('error', 'Non-AJAX request to signup');
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid request method',
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
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            } else {
                echo json_encode(array(
                    'success' => false,
                    'message' => 'Registration failed: Email or username already exists',
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Signup error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            echo json_encode(array(
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function logout() {
        log_message('debug', 'Auth::logout method called');
           $this->output->set_content_type('application/json'); // add new line here 
        $this->session->sess_destroy();
        echo json_encode(array(
            'success' => true,
            'message' => 'Logout successful',
            'csrf_token' => $this->security->get_csrf_hash()
        ));
        exit();
    }
}