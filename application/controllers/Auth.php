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

   public function create_group() {
        $this->output->set_content_type('application/json');

        if (!$this->session->userdata('user_id')) {
            $response = array(
                'success' => false,
                'message' => 'Please log in to perform this action',
                'flashMessage' => 'Please log in to perform this action',
                'flashType' => 'error'
            );
            log_message('error', 'create_group: User not logged in');
            $this->output->set_status_header(401)->set_output(json_encode($response));
            return;
        }

        try {
            // Get raw JSON input
            $raw_input = file_get_contents('php://input');
            log_message('debug', 'create_group: Raw input received: ' . $raw_input);
            
            if (empty($raw_input)) {
                throw new Exception('No input data received');
            }

            $data = json_decode($raw_input, true);

            // Check for JSON decode errors
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON payload: ' . json_last_error_msg());
            }

            log_message('debug', 'create_group: Decoded data: ' . print_r($data, true));

            // Extract and validate input data
            $group_name = isset($data['name']) ? trim($data['name']) : '';
            $description = isset($data['description']) ? trim($data['description']) : '';
            $members = isset($data['members']) && is_array($data['members']) ? $data['members'] : array();

            log_message('debug', 'create_group: Extracted values - Name: "' . $group_name . '", Description: "' . $description . '", Members count: ' . count($members));

            // Manual validation
            if (empty($group_name)) {
                throw new Exception('Group name is required');
            }

            if (strlen($group_name) < 3) {
                throw new Exception('Group name must be at least 3 characters long');
            }

            if (strlen($group_name) > 100) {
                throw new Exception('Group name must not exceed 100 characters');
            }

            if (!empty($description) && strlen($description) > 500) {
                throw new Exception('Description must not exceed 500 characters');
            }

            if (empty($members)) {
                throw new Exception('At least one member must be selected');
            }

            // Get current user info
            $current_user_email = $this->session->userdata('email');
            if (empty($current_user_email)) {
                throw new Exception('User email not found in session');
            }

            log_message('debug', 'create_group: Current user email: ' . $current_user_email);

            // Validate members exist in database
            $valid_members = array();
            foreach ($members as $email) {
                if (!empty($email) && $email !== $current_user_email) {
                    $this->db->select('email');
                    $this->db->from('students');
                    $this->db->where('email', $email);
                    $this->db->where('is_deleted', 0);
                    $query = $this->db->get();
                    log_message('debug', 'create_group: Member validation query: ' . $this->db->last_query());
                    
                    if ($query->num_rows() > 0) {
                        $valid_members[] = $email;
                        log_message('debug', 'create_group: Valid member found: ' . $email);
                    } else {
                        log_message('debug', 'create_group: Invalid member: ' . $email);
                    }
                }
            }

            if (empty($valid_members)) {
                throw new Exception('No valid members found. Please ensure all member emails exist in the system.');
            }

            log_message('debug', 'create_group: Valid members: ' . print_r($valid_members, true));

            // Check if tables exist
            if (!$this->db->table_exists('groups') || !$this->db->table_exists('group_members')) {
                throw new Exception('Required database tables (groups or group_members) not found');
            }

            // Create the group
            log_message('debug', 'create_group: Calling Group_model->create_group');
            $group_id = $this->Group_model->create_group($group_name, $description, $current_user_email, $valid_members);

            if ($group_id) {
                log_message('debug', 'create_group: Group created with ID: ' . $group_id);
                
                // Emit Socket.IO event for group creation
                try {
                    $this->emit_socket_group_created($group_id, $group_name, $description, $current_user_email, $valid_members);
                } catch (Exception $socket_error) {
                    log_message('error', 'create_group: Socket emission failed: ' . $socket_error->getMessage());
                }
                
                // Return success response
                $response = array(
                    'success' => true,
                    'group_id' => $group_id,
                    'message' => 'Group created successfully',
                    'flashMessage' => 'Group "' . $group_name . '" created successfully',
                    'flashType' => 'success'
                );
                
                log_message('debug', 'create_group: Success response: ' . json_encode($response));
                $this->output->set_status_header(200)->set_output(json_encode($response));
            } else {
                throw new Exception('Failed to create group - Group_model returned false');
            }

        } catch (Exception $e) {
            log_message('error', 'create_group: Exception caught: ' . $e->getMessage());
            log_message('error', 'create_group: Stack trace: ' . $e->getTraceAsString());
            log_message('error', 'create_group: Last DB query: ' . $this->db->last_query());
            
            $response = array(
                'success' => false,
                'message' => $e->getMessage(),
                'flashMessage' => $e->getMessage(),
                'flashType' => 'error'
            );
            
            $this->output->set_status_header(400)->set_output(json_encode($response));
        }
    }

    private function emit_socket_group_created($group_id, $name, $description, $created_by, $members) {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:3000/emit_group_created');
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array(
                'group_id' => $group_id,
                'name' => $name,
                'description' => $description,
                'created_by' => $created_by,
                'members' => $members,
                'member_count' => count($members) + 1
            )));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code !== 200) {
                throw new Exception('Socket.IO server returned status ' . $http_code . ': ' . $response);
            }
            
            log_message('debug', 'Socket.IO group_created event emitted: ' . $response);
        } catch (Exception $e) {
            log_message('error', 'Error emitting group_created event: ' . $e->getMessage());
            throw $e;
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
