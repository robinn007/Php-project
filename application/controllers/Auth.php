<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Student_model');
        $this->load->library('session');
        log_message('debug', 'Auth controller initialized');
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
        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->output->set_status_header(405)->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Method not allowed'
            )));
            return;
        }

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
            
            $password_correct = password_verify($password, $user['password']);
            if (!$password_correct) {
                log_message('debug', 'Password verification failed for: ' . $email);
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Invalid credentials',
                    'flashMessage' => 'Invalid email or password',
                    'flashType' => 'error'
                )));
                return;
            }

            // Update student status to online
            $this->db->where('email', $email);
            $this->db->where('is_deleted', 0);
            $this->db->update('students', array('status' => 'online'));
            log_message('debug', 'Updated student status to online for email: ' . $email . ', Rows affected: ' . $this->db->affected_rows());

            $session_data = array(
                'user_id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'logged_in' => TRUE
            );
            
            $this->session->set_userdata($session_data);
            log_message('debug', 'Session data set: ' . json_encode($session_data));

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
        
        $email = $this->session->userdata('email');
        if ($email) {
            $this->db->where('email', $email);
            $this->db->where('is_deleted', 0);
            $this->db->update('students', array('status' => 'offline'));
            log_message('debug', 'Updated student status to offline for email: ' . $email . ', Rows affected: ' . $this->db->affected_rows());
        } else {
            log_message('debug', 'No email found in session during logout');
        }
        
        $this->session->sess_destroy();
        log_message('debug', 'Session destroyed');
        
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

    public function get_messages() {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            )));
            return;
        }

        $email = $this->session->userdata('email');
        $receiver_email = $this->input->get('receiver_email');
        $limit = $this->input->get('limit'); // Optional limit parameter

        if (!$receiver_email) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Receiver email is required.',
                'csrf_token' => $this->security->get_csrf_hash()
            )));
            return;
        }

        try {
            // Build the query
            $this->db->select('*');
            $this->db->from('messages');
            $this->db->where("(sender_email = '$email' AND receiver_email = '$receiver_email') OR (sender_email = '$receiver_email' AND receiver_email = '$email')");
            $this->db->order_by('created_at', 'ASC');
            
            // Apply limit if specified (useful for getting just the last message)
            if ($limit && is_numeric($limit) && $limit > 0) {
                $this->db->order_by('created_at', 'DESC'); // Get most recent first when limiting
                $this->db->limit((int)$limit);
            }
            
            $query = $this->db->get();
            $messages = $query->result_array();
            
            // If we applied a limit and got results, we need to reverse order for proper chronological display
            if ($limit && is_numeric($limit) && $limit > 0 && !empty($messages)) {
                $messages = array_reverse($messages);
            }

            log_message('debug', 'Messages query for ' . $email . ' <-> ' . $receiver_email . ': ' . $this->db->last_query());
            log_message('debug', 'Found ' . count($messages) . ' messages');

            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => true,
                'messages' => $messages,
                'total' => count($messages),
                'csrf_token' => $this->security->get_csrf_hash()
            )));

        } catch (Exception $e) {
            log_message('error', 'Error in get_messages: ' . $e->getMessage());
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error retrieving messages: ' . $e->getMessage(),
                'csrf_token' => $this->security->get_csrf_hash()
            )));
        }
    }

    // New method to get last messages for all conversations
    public function get_last_messages_summary() {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.'
            )));
            return;
        }

        $email = $this->session->userdata('email');

        try {
            // Get the last message for each conversation
            $sql = "
                SELECT 
                    CASE 
                        WHEN sender_email = ? THEN receiver_email 
                        ELSE sender_email 
                    END as other_person_email,
                    message,
                    created_at,
                    sender_email,
                    receiver_email
                FROM messages 
                WHERE sender_email = ? OR receiver_email = ?
                AND id IN (
                    SELECT MAX(id) 
                    FROM messages 
                    WHERE sender_email = ? OR receiver_email = ?
                    GROUP BY 
                        CASE 
                            WHEN sender_email = ? THEN receiver_email 
                            ELSE sender_email 
                        END
                )
                ORDER BY created_at DESC
            ";

            $query = $this->db->query($sql, array($email, $email, $email, $email, $email, $email));
            $results = $query->result_array();

            log_message('debug', 'Last messages summary query: ' . $this->db->last_query());
            log_message('debug', 'Found ' . count($results) . ' conversation summaries');

            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => true,
                'conversations' => $results,
                'total' => count($results)
            )));

        } catch (Exception $e) {
            log_message('error', 'Error in get_last_messages_summary: ' . $e->getMessage());
            $this->output->set_content_type('application/json')->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error retrieving conversation summary: ' . $e->getMessage()
            )));
        }
    }
}