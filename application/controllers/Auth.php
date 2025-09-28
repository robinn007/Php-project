<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->model('Student_model');
        $this->load->model('Group_model'); // Load Group model
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
        $group_id = $this->input->get('group_id');
        $limit = $this->input->get('limit'); // Optional limit parameter

        // Handle group messages
        if ($group_id) {
            if (!$this->Group_model->is_group_member($group_id, $email)) {
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'You are not a member of this group.',
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
                return;
            }

            try {
                $messages = $this->Group_model->get_group_messages($group_id, $limit ?: 50);
                
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => true,
                    'messages' => $messages,
                    'total' => count($messages),
                    'type' => 'group',
                    'csrf_token' => $this->security->get_csrf_hash()
                )));

            } catch (Exception $e) {
                log_message('error', 'Error in get_group_messages: ' . $e->getMessage());
                $this->output->set_content_type('application/json')->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Error retrieving group messages: ' . $e->getMessage(),
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
            }
            return;
        }

        // Handle direct messages (existing code)
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
            $this->db->where('message_type', 'direct');
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
                'type' => 'direct',
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

    // Enhanced method to get last messages for all conversations (both direct and group)
    public function get_last_messages_summary() {
    if (!$this->session->userdata('user_id')) {
        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => false,
            'message' => 'Please log in to perform this action.'
        )));
        return;
    }

    $email = $this->session->userdata('email');
    log_message('debug', 'Processing get_last_messages_summary for user email: ' . $email);

    try {
        if (empty($email)) {
            throw new Exception('Session email is empty or invalid');
        }

        // Get direct message conversations
        $sql = "
            SELECT 
                CASE 
                    WHEN sender_email = ? THEN receiver_email 
                    ELSE sender_email 
                END as other_person_email,
                message,
                created_at,
                sender_email,
                receiver_email,
                'direct' as conversation_type,
                NULL as group_id,
                NULL as group_name,
                NULL as sender_name,
                NULL as member_count
            FROM messages 
            WHERE (sender_email = ? OR receiver_email = ?)
            AND message_type = 'direct'
            AND id IN (
                SELECT MAX(id) 
                FROM messages 
                WHERE (sender_email = ? OR receiver_email = ?)
                AND message_type = 'direct'
                GROUP BY 
                    CASE 
                        WHEN sender_email = ? THEN receiver_email 
                        ELSE sender_email 
                    END
            )
            ORDER BY created_at DESC
        ";

        $direct_query = $this->db->query($sql, array($email, $email, $email, $email, $email, $email));
        $direct_conversations = $direct_query->result_array();
        log_message('debug', 'Direct conversations query executed: ' . $this->db->last_query() . ' - Rows: ' . count($direct_conversations));

        // Get group conversations
        $group_conversations = $this->Group_model->get_group_last_messages($email);
        log_message('debug', 'Group conversations retrieved: ' . count($group_conversations) . ' records');

        // Transform group conversations to match the format
        $formatted_group_conversations = array();
        foreach ($group_conversations as $group) {
            $formatted_group_conversations[] = array(
                'other_person_email' => null,
                'message' => $group['last_message'] ?: 'No messages yet',
                'created_at' => $group['created_at'],
                'sender_email' => $group['sender_email'],
                'receiver_email' => null,
                'conversation_type' => 'group',
                'group_id' => $group['group_id'],
                'group_name' => $group['group_name'],
                'sender_name' => $group['sender_name'],
                'member_count' => $group['member_count']
            );
        }

        // Combine and sort all conversations
        $all_conversations = array_merge($direct_conversations, $formatted_group_conversations);
        usort($all_conversations, function($a, $b) {
            $a_time = strtotime($a['created_at'] ?: '1970-01-01');
            $b_time = strtotime($b['created_at'] ?: '1970-01-01');
            return $b_time - $a_time;
        });

        log_message('debug', 'Total conversations: ' . count($all_conversations));

        $this->output->set_content_type('application/json')->set_output(json_encode(array(
            'success' => true,
            'conversations' => $all_conversations,
            'direct_count' => count($direct_conversations),
            'group_count' => count($formatted_group_conversations),
            'total' => count($all_conversations)
        )));

    } catch (Exception $e) {
        log_message('error', 'Exception in get_last_messages_summary: ' . $e->getMessage() . ' at line ' . $e->getLine() . ' - Query: ' . $this->db->last_query());
        $this->output->set_status_header(500)->set_content_type('application/json')->set_output(json_encode(array(
            'success' => false,
            'message' => 'Error retrieving conversation summary: ' . $e->getMessage(),
            'flashMessage' => 'An error occurred while loading conversations',
            'flashType' => 'error'
        )));
    }
}

    // Group management methods
     public function create_group() {
        $this->output->set_content_type('application/json');
        
        if (!$this->session->userdata('user_id')) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.'
            )));
            return;
        }

        if (strtolower($_SERVER['REQUEST_METHOD']) !== 'post') {
            $this->output->set_status_header(405)->set_output(json_encode(array(
                'success' => false,
                'message' => 'Method not allowed'
            )));
            return;
        }

        try {
            // Get JSON input
            $json_input = json_decode(file_get_contents('php://input'), true);
            
            $name = isset($json_input['name']) ? $this->security->xss_clean($json_input['name']) : null;
            $description = isset($json_input['description']) ? $this->security->xss_clean($json_input['description']) : null;
            $members = isset($json_input['members']) ? $json_input['members'] : array();
            
            log_message('debug', 'Create group request - Name: ' . $name . ', Members: ' . json_encode($members));
            
            if (!$name) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Group name is required',
                    'flashMessage' => 'Group name is required',
                    'flashType' => 'error'
                )));
                return;
            }

            if (!is_array($members) || count($members) == 0) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'At least one member is required',
                    'flashMessage' => 'Please select at least one member',
                    'flashType' => 'error'
                )));
                return;
            }

            $created_by = $this->session->userdata('email');
            $group_id = $this->Group_model->create_group($name, $description, $created_by, $members);
            
            if ($group_id) {
                log_message('debug', 'Group created successfully with ID: ' . $group_id);
                
                // Emit Socket.IO event
                $this->emit_socket_group_created($group_id, $name, $description, $created_by, $members);
                
                $this->output->set_output(json_encode(array(
                    'success' => true,
                    'message' => 'Group created successfully',
                    'flashMessage' => 'Group "' . $name . '" created successfully!',
                    'flashType' => 'success',
                    'group_id' => $group_id
                )));
            } else {
                log_message('error', 'Failed to create group');
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Failed to create group',
                    'flashMessage' => 'Failed to create group. Please try again.',
                    'flashType' => 'error'
                )));
            }

        } catch (Exception $e) {
            log_message('error', 'Error creating group: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error creating group: ' . $e->getMessage(),
                'flashMessage' => 'An error occurred while creating the group',
                'flashType' => 'error'
            )));
        }
    }
    
     // Helper method to emit Socket.IO group created event
    private function emit_socket_group_created($group_id, $name, $description, $created_by, $members) {
        try {
            // Connect to Socket.IO server (assuming it's running on the same server)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/emit_group_created');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'group_id' => $group_id,
                'name' => $name,
                'description' => $description,
                'created_by' => $created_by,
                'members' => $members,
                'member_count' => count($members) + 1 // Include creator
            )));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            log_message('debug', 'Socket.IO group_created event emitted: ' . $response);
        } catch (Exception $e) {
            log_message('error', 'Error emitting group_created event: ' . $e->getMessage());
        }
    }

    public function get_groups() {
        $this->output->set_content_type('application/json');
        
        if (!$this->session->userdata('user_id')) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.'
            )));
            return;
        }

        try {
            $email = $this->session->userdata('email');
            $groups = $this->Group_model->get_user_groups($email);
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'groups' => $groups
            )));

        } catch (Exception $e) {
            log_message('error', 'Error getting groups: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error retrieving groups: ' . $e->getMessage()
            )));
        }
    }

    public function get_group_members($group_id) {
        $this->output->set_content_type('application/json');
        
        if (!$this->session->userdata('user_id')) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.'
            )));
            return;
        }

        if (!$group_id) {
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Group ID is required'
            )));
            return;
        }

        try {
            $email = $this->session->userdata('email');
            
            // Check if user is a member of the group
            if (!$this->Group_model->is_group_member($group_id, $email)) {
                $this->output->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'You are not a member of this group'
                )));
                return;
            }

            $members = $this->Group_model->get_group_members($group_id);
            
            $this->output->set_output(json_encode(array(
                'success' => true,
                'members' => $members
            )));

        } catch (Exception $e) {
            log_message('error', 'Error getting group members: ' . $e->getMessage());
            $this->output->set_output(json_encode(array(
                'success' => false,
                'message' => 'Error retrieving group members: ' . $e->getMessage()
            )));
        }
    }
}
