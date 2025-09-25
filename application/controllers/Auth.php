<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $this->load->model('User_model');
        $this->load->library('session');
        
        log_message('debug', 'Auth controller constructor loaded');
    }

    public function get_csrf() {
        // Return a dummy CSRF token since CSRF is disabled
        $response = array(
            'success' => true,
            'csrf_token_name' => 'ci_csrf_token',
            'csrf_token' => 'dummy_token_' . time(),
            'message' => 'CSRF disabled - dummy token generated'
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function login() {
        log_message('debug', '=== LOGIN METHOD STARTED ===');

        // Check request method
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Method not allowed'
            )));
            return;
        }

        // Get credentials
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        log_message('debug', 'Login attempt with email: ' . $email);

        if (!$email || !$password) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Email and password are required',
                'flashMessage' => 'Please enter both email and password',
                'flashType' => 'error'
            )));
            return;
        }

        try {
            // Get user from database
            $this->db->where('email', $email);
            $query = $this->db->get('users');
            $user = $query->row_array();
            
            if (!$user) {
                log_message('debug', 'User not found: ' . $email);
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'flashMessage' => 'Invalid email or password',
                    'flashType' => 'error'
                )));
                return;
            }
            
            log_message('debug', 'User found: ' . $user['username']);
            
            // Check password
            $password_correct = false;
            
            if (strlen($user['password']) > 50) {
                // Hashed password
                $password_correct = password_verify($password, $user['password']);
                log_message('debug', 'Hashed password check: ' . ($password_correct ? 'PASS' : 'FAIL'));
            } else {
                // Plain text password
                $password_correct = ($password === $user['password']);
                log_message('debug', 'Plain text password check: ' . ($password_correct ? 'PASS' : 'FAIL'));
                
                if ($password_correct) {
                    // Upgrade to hashed password
                    $new_hash = password_hash($password, PASSWORD_DEFAULT);
                    $this->db->where('id', $user['id']);
                    $this->db->update('users', array('password' => $new_hash));
                    log_message('debug', 'Password upgraded to hash');
                }
            }
            
            if (!$password_correct) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'flashMessage' => 'Invalid email or password',
                    'flashType' => 'error'
                )));
                return;
            }

            // Set session data (file-based sessions)
            $session_data = array(
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'logged_in' => TRUE
            );
            
            $this->session->set_userdata($session_data);
            
            log_message('debug', 'Login successful for user: ' . $user['username']);

            $response = array(
                'success' => true,
                'message' => 'Login successful',
                'flashMessage' => 'Welcome back, ' . $user['username'] . '!',
                'flashType' => 'success',
                'user' => array(
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                )
            );

            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            
        } catch (Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Login error: ' . $e->getMessage(),
                'flashMessage' => 'An error occurred during login',
                'flashType' => 'error'
            )));
        }
    }

    public function logout() {
        log_message('debug', 'Logout called');
        
        $this->session->sess_destroy();
        
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'message' => 'Logout successful',
            'flashMessage' => 'You have been logged out',
            'flashType' => 'success'
        )));
    }

    public function check_auth() {
        $user_id = $this->session->userdata('user_id');
        $is_logged_in = !empty($user_id);
        
        $response = array(
            'success' => true,
            'is_logged_in' => $is_logged_in
        );
        
        if ($is_logged_in) {
            $response['user'] = array(
                'id' => $this->session->userdata('user_id'),
                'username' => $this->session->userdata('username'),
                'email' => $this->session->userdata('email')
            );
        }
        
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function signup() {
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Method not allowed'
            )));
            return;
        }

        $username = $this->input->post('username');
        $email = $this->input->post('email');
        $password = $this->input->post('password');
        
        if (!$username || !$email || !$password) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'All fields are required',
                'flashMessage' => 'Please fill in all fields',
                'flashType' => 'error'
            )));
            return;
        }

        try {
            $data = array(
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            if ($this->User_model->signup($data)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => true,
                    'message' => 'Registration successful',
                    'flashMessage' => 'Account created successfully! Please login.',
                    'flashType' => 'success'
                )));
            } else {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Registration failed',
                    'flashMessage' => 'Email or username already exists',
                    'flashType' => 'error'
                )));
            }
        } catch (Exception $e) {
            $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Registration error: ' . $e->getMessage(),
                'flashMessage' => 'An error occurred during registration',
                'flashType' => 'error'
            )));
        }
    }

    public function test_endpoint() {
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'message' => 'Auth controller is working',
            'method' => $_SERVER['REQUEST_METHOD'],
            'timestamp' => date('Y-m-d H:i:s')
        )));
    }
}