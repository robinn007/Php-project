<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        
        // Enable error reporting for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        
       // Load User_model for database operations
        $this->load->model('User_model');
        // Load session library for user session management
        $this->load->library('session');
        // Load security library for CSRF protection
        $this->load->library('security');
        
          // Set JSON content type for all responses
        $this->output->set_content_type('application/json');
        
        log_message('debug', 'Auth controller constructor called');
    }

    // Retrieve CSRF token for frontend requests
    public function get_csrf() {
        try {
            log_message('debug', 'get_csrf method called');
            
             // Prepare CSRF response
            $response = array(
                'success' => true,
                'csrf_token_name' => $this->security->get_csrf_token_name(),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            log_message('debug', 'CSRF response: ' . json_encode($response));
            echo json_encode($response);
            
        } catch (Exception $e) {
            log_message('error', 'get_csrf error: ' . $e->getMessage());
             // Return error response with CSRF token
            echo json_encode(array(
                'success' => false,
                'message' => 'CSRF error: ' . $e->getMessage()
            ));
        }
    }

    public function login() {
        try {
            log_message('debug', 'Login method called');
            log_message('debug', 'Request method: ' . $this->input->server('REQUEST_METHOD'));
            
            // Temporarily disable CSRF for debugging
            $this->config->set_item('csrf_protection', FALSE);
            
              // Validate request method
            if ($this->input->server('REQUEST_METHOD') !== 'POST') {
                throw new Exception('Method not allowed');
            }

            // Get raw input for debugging (CodeIgniter 2 compatible)
            $raw_input = file_get_contents('php://input');
            log_message('debug', 'Raw input: ' . $raw_input);
            
            // Try to decode JSON
            $json_data = json_decode($raw_input, true);
            log_message('debug', 'JSON decoded: ' . json_encode($json_data));
            
            // Get data from either JSON or POST
            if ($json_data) {
                $email = isset($json_data['email']) ? trim($json_data['email']) : '';
                $password = isset($json_data['password']) ? $json_data['password'] : '';
            } else {
                $email = trim($this->input->post('email'));
                $password = $this->input->post('password');
            }
            
            log_message('debug', 'Login attempt for email: ' . $email);
            
            // Basic validation input
            if (empty($email) || empty($password)) {
                throw new Exception('Email and password are required');
            }
            
            // Check if user exists 
            $user = $this->User_model->get_user_by_email($email);
            log_message('debug', 'User found: ' . ($user ? 'yes' : 'no'));
            
            if (!$user) {
                throw new Exception('User not found');
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                log_message('debug', 'Password verification failed');
                throw new Exception('Invalid password');
            }
            
            // Set session data
            $session_data = array(
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'logged_in' => TRUE
            );
            
            $this->session->set_userdata($session_data);
            log_message('debug', 'Session data set: ' . json_encode($session_data));
            
            // Success response with user data and CSRF token
            $response = array(
                'success' => true,
                'message' => 'Login successful',
                'user' => array(
                    'id' => (int)$user['id'],
                    'username' => $user['username'],
                    'email' => $user['email']
                ),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            log_message('debug', 'Login successful, response: ' . json_encode($response));
            echo json_encode($response);
            
        } catch (Exception $e) {
            log_message('error', 'Login error: ' . $e->getMessage());
            
              // Error response with CSRF token
            $error_response = array(
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            echo json_encode($error_response);
        }
    }

    public function signup() {
        try {
            log_message('debug', 'Signup method called');
            
            // Temporarily disable CSRF for debugging
            $this->config->set_item('csrf_protection', FALSE);
            
            if ($this->input->server('REQUEST_METHOD') !== 'POST') {
                throw new Exception('Method not allowed');
            }

            // Get raw input (CodeIgniter 2 compatible)
            $raw_input = file_get_contents('php://input');
            log_message('debug', 'Signup raw input: ' . $raw_input);
            
             // Decode JSON input
            $json_data = json_decode($raw_input, true);
            
              // Extract data from JSON or POST
            if ($json_data) {
                $username = isset($json_data['username']) ? trim($json_data['username']) : '';
                $email = isset($json_data['email']) ? trim($json_data['email']) : '';
                $password = isset($json_data['password']) ? $json_data['password'] : '';
                $confirm_password = isset($json_data['confirm_password']) ? $json_data['confirm_password'] : '';
            } else {
                $username = trim($this->input->post('username'));
                $email = trim($this->input->post('email'));
                $password = $this->input->post('password');
                $confirm_password = $this->input->post('confirm_password');
            }
            
            log_message('debug', 'Signup data - username: ' . $username . ', email: ' . $email);
            
            // Validate input
            if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
                throw new Exception('All fields are required');
            }
            
            if ($password !== $confirm_password) {
                throw new Exception('Passwords do not match');
            }
            
            if (strlen($password) < 6) {
                throw new Exception('Password must be at least 6 characters');
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }
            
            // Check if user already exists
            if ($this->User_model->get_user_by_email($email)) {
                throw new Exception('Email already registered');
            }
            
            // Create user data
            $user_data = array(
                'username' => $username,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            log_message('debug', 'Creating user with data: ' . json_encode($user_data));
            
             // Create user
            if (!$this->User_model->signup($user_data)) {
                throw new Exception('Failed to create user account');
            }
            
            log_message('debug', 'User created successfully');
            
             // Success response with CSRF token
            $response = array(
                'success' => true,
                'message' => 'Account created successfully! Please log in.',
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            log_message('error', 'Signup error: ' . $e->getMessage());
            
             // Error response with CSRF token
            $error_response = array(
                'success' => false,
                'message' => $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            echo json_encode($error_response);
        }
    }

     // Handle user logout
    public function logout() {
        try {
            log_message('debug', 'Logout method called');
            
             // Destroy session
            $this->session->sess_destroy();
            
              // Success response with CSRF token
            $response = array(
                'success' => true,
                'message' => 'Logged out successfully',
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            echo json_encode($response);
            
        } catch (Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            
             // Error response
            echo json_encode(array(
                'success' => false,
                'message' => 'Logout failed: ' . $e->getMessage()
            ));
        }
    }
}