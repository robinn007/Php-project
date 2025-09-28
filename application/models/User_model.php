<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        
        // Ensure database is loaded
        if (!$this->db) {
            $this->load->database();
        }
        
        log_message('debug', 'User_model constructor loaded');
        
        // Test database connection
        if (!$this->db->conn_id) {
            log_message('error', 'Database connection failed in User_model');
            throw new Exception('Database connection failed');
        }
        
        log_message('debug', 'Database connection verified in User_model');
    }

    public function login($email, $password) {
        try {
            log_message('debug', 'User_model::login called with email: ' . $email);

            // Validate inputs
            if (empty($email) || empty($password)) {
                log_message('error', 'Empty email or password provided');
                return false;
            }

            // Check if users table exists
            if (!$this->db->table_exists('users')) {
                log_message('error', 'Users table does not exist');
                throw new Exception('Users table not found');
            }

            // Query the users table with better error handling
            $this->db->select('id, username, email, password');
            $this->db->from('users');
            $this->db->where('email', $email);
            $this->db->limit(1); // Add limit for safety
            
            $query = $this->db->get();

            // Check for database errors
            if (!$query) {
                $db_error = $this->db->error();
                log_message('error', 'Database query failed: ' . json_encode($db_error));
                throw new Exception('Database query failed: ' . $db_error['message']);
            }

            log_message('debug', 'User query executed: ' . $this->db->last_query());
            log_message('debug', 'Query returned ' . $query->num_rows() . ' rows');

            if ($query->num_rows() === 0) {
                log_message('debug', 'No user found with email: ' . $email);
                return false;
            }

            $user = $query->row_array();
            
            if (!$user || !isset($user['password'])) {
                log_message('error', 'Invalid user data returned from database');
                return false;
            }

            log_message('debug', 'User found: ' . $user['username']);

            // Verify password
            if (password_verify($password, $user['password'])) {
                log_message('debug', 'Password verified for user: ' . $user['username']);
                
                // Remove password from return data
                unset($user['password']);
                
                return $user;
            } else {
                log_message('debug', 'Password verification failed for email: ' . $email);
                return false;
            }
            
        } catch (Exception $e) {
            log_message('error', 'User_model::login exception: ' . $e->getMessage() . 
                ' in ' . $e->getFile() . ':' . $e->getLine());
            
            // Re-throw with more context
            throw new Exception('Login failed: ' . $e->getMessage());
        }
    }

    public function signup($data) {
        try {
            log_message('debug', 'User_model::signup called with data: ' . json_encode(array(
                'username' => isset($data['username']) ? $data['username'] : 'not set',
                'email' => isset($data['email']) ? $data['email'] : 'not set',
                'password' => isset($data['password']) ? 'provided' : 'not set'
            )));
            
            // Validate required fields
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                log_message('error', 'Missing required signup data');
                return false;
            }

            // Check if users table exists
            if (!$this->db->table_exists('users')) {
                log_message('error', 'Users table does not exist');
                throw new Exception('Users table not found');
            }

            // Check for existing user
            $this->db->where('email', $data['email']);
            $this->db->or_where('username', $data['username']);
            $query = $this->db->get('users');

            if (!$query) {
                $db_error = $this->db->error();
                log_message('error', 'Database query failed during signup check: ' . json_encode($db_error));
                throw new Exception('Database query failed: ' . $db_error['message']);
            }

            if ($query->num_rows() > 0) {
                log_message('debug', 'Email or username already exists: ' . $data['email'] . '/' . $data['username']);
                return false;
            }

            // Insert new user
            $insert_data = array(
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'created_at' => date('Y-m-d H:i:s')
            );

            $result = $this->db->insert('users', $insert_data);
            
            if (!$result) {
                $db_error = $this->db->error();
                log_message('error', 'Failed to insert user: ' . json_encode($db_error));
                throw new Exception('Failed to create user: ' . $db_error['message']);
            }

            log_message('debug', 'User registered successfully: ' . $data['username']);
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'User_model::signup error: ' . $e->getMessage() . 
                ' in ' . $e->getFile() . ':' . $e->getLine());
            throw new Exception('Signup failed: ' . $e->getMessage());
        }
    }

    public function get_user_by_id($user_id) {
        try {
            log_message('debug', 'User_model::get_user_by_id called with ID: ' . $user_id);
            
            // Validate input
            if (empty($user_id) || !is_numeric($user_id)) {
                log_message('error', 'Invalid user ID provided: ' . $user_id);
                return false;
            }

            // Check if users table exists
            if (!$this->db->table_exists('users')) {
                log_message('error', 'Users table does not exist');
                throw new Exception('Users table not found');
            }

            $this->db->select('id, username, email');
            $this->db->where('id', $user_id);
            $this->db->limit(1);
            $query = $this->db->get('users');

            if (!$query) {
                $db_error = $this->db->error();
                log_message('error', 'Database query failed in get_user_by_id: ' . json_encode($db_error));
                throw new Exception('Database query failed: ' . $db_error['message']);
            }

            if ($query->num_rows() === 0) {
                log_message('debug', 'User not found for ID: ' . $user_id);
                return false;
            }

            $user = $query->row_array();
            log_message('debug', 'User found by ID: ' . json_encode($user));
            
            return $user;
            
        } catch (Exception $e) {
            log_message('error', 'User_model::get_user_by_id error: ' . $e->getMessage() . 
                ' in ' . $e->getFile() . ':' . $e->getLine());
            throw new Exception('Failed to get user: ' . $e->getMessage());
        }
    }

    // Helper method to check if users table exists and create it if needed
    public function ensure_users_table() {
        if (!$this->db->table_exists('users')) {
            log_message('info', 'Creating users table');
            
            $sql = "CREATE TABLE `users` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `username` varchar(50) NOT NULL,
                `email` varchar(100) NOT NULL,
                `password` varchar(255) NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `email` (`email`),
                UNIQUE KEY `username` (`username`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
            
            if ($this->db->query($sql)) {
                log_message('info', 'Users table created successfully');
                return true;
            } else {
                $db_error = $this->db->error();
                log_message('error', 'Failed to create users table: ' . json_encode($db_error));
                return false;
            }
        }
        return true;
    }

    public function user_exists($email) {
    try {
        if (empty($email)) {
            return false;
        }
        
        $this->db->select('1');
        $this->db->from('students'); // Assuming students table contains user data
        $this->db->where('email', $email);
        $this->db->where('is_deleted', 0);
        $this->db->limit(1);
        
        $query = $this->db->get();
        $exists = $query->num_rows() > 0;
        
        log_message('debug', 'User_model::user_exists - Email: ' . $email . ' exists: ' . ($exists ? 'yes' : 'no'));
        
        return $exists;
        
    } catch (Exception $e) {
        log_message('error', 'User_model::user_exists - Error checking user: ' . $e->getMessage());
        return false;
    }
}
}