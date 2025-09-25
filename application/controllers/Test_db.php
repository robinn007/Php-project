<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test_db extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('User_model');
    }
    
    public function index() {
        try {
            echo "<h2>Database Connection Test</h2>";
            
            // Test 1: Database connection
            if ($this->db->conn_id) {
                echo "✅ Database connection: SUCCESS<br>";
            } else {
                echo "❌ Database connection: FAILED<br>";
                return;
            }
            
            // Test 2: Check if users table exists
            if ($this->db->table_exists('users')) {
                echo "✅ Users table: EXISTS<br>";
                
                // Get table structure
                $fields = $this->db->field_data('users');
                echo "<h3>Users table structure:</h3>";
                echo "<pre>";
                foreach ($fields as $field) {
                    echo "- {$field->name} ({$field->type}, max_length: {$field->max_length})\n";
                }
                echo "</pre>";
                
                // Count users
                $count = $this->db->count_all('users');
                echo "👥 Total users in database: {$count}<br>";
                
                // Show first few users (without passwords)
                $this->db->select('id, username, email, created_at');
                $this->db->limit(5);
                $users = $this->db->get('users')->result_array();
                
                if (!empty($users)) {
                    echo "<h3>Sample users:</h3>";
                    echo "<pre>";
                    foreach ($users as $user) {
                        echo "ID: {$user['id']}, Username: {$user['username']}, Email: {$user['email']}\n";
                    }
                    echo "</pre>";
                }
                
            } else {
                echo "❌ Users table: NOT EXISTS<br>";
                echo "<br>Creating users table...<br>";
                
                if ($this->User_model->ensure_users_table()) {
                    echo "✅ Users table created successfully!<br>";
                } else {
                    echo "❌ Failed to create users table<br>";
                }
            }
            
            // Test 3: Test login function with dummy data
            echo "<h3>Testing login function:</h3>";
            
            // Create a test user if none exist
            if ($this->db->count_all('users') == 0) {
                echo "No users found. Creating test user...<br>";
                $test_user = array(
                    'username' => 'testuser',
                    'email' => 'test@example.com',
                    'password' => password_hash('testpass', PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s')
                );
                
                if ($this->db->insert('users', $test_user)) {
                    echo "✅ Test user created: test@example.com / testpass<br>";
                } else {
                    echo "❌ Failed to create test user<br>";
                    echo "Database error: " . json_encode($this->db->error()) . "<br>";
                }
            }
            
            // Test the login function
            try {
                $login_result = $this->User_model->login('test@example.com', 'testpass');
                if ($login_result) {
                    echo "✅ Login function works correctly<br>";
                    echo "User data: " . json_encode($login_result) . "<br>";
                } else {
                    echo "❌ Login function returned false<br>";
                }
            } catch (Exception $e) {
                echo "❌ Login function error: " . $e->getMessage() . "<br>";
            }
            
            // Test 4: Session table
            if ($this->db->table_exists('ci_sessions')) {
                echo "✅ Sessions table: EXISTS<br>";
                $session_count = $this->db->count_all('ci_sessions');
                echo "🔄 Active sessions: {$session_count}<br>";
            } else {
                echo "❌ Sessions table: NOT EXISTS<br>";
                echo "Creating sessions table...<br>";
                
                $session_sql = "CREATE TABLE `ci_sessions` (
                    `id` varchar(128) NOT NULL,
                    `ip_address` varchar(45) NOT NULL,
                    `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
                    `data` blob NOT NULL,
                    KEY `ci_sessions_timestamp` (`timestamp`)
                );";
                
                if ($this->db->query($session_sql)) {
                    echo "✅ Sessions table created successfully!<br>";
                } else {
                    echo "❌ Failed to create sessions table<br>";
                    echo "Database error: " . json_encode($this->db->error()) . "<br>";
                }
            }
            
            echo "<hr>";
            echo "<h3>Database Configuration:</h3>";
            echo "<pre>";
            echo "Database: " . $this->db->database . "\n";
            echo "Host: " . $this->db->hostname . "\n";
            echo "Username: " . $this->db->username . "\n";
            echo "Driver: " . $this->db->dbdriver . "\n";
            echo "</pre>";
            
        } catch (Exception $e) {
            echo "❌ Database test failed: " . $e->getMessage();
            echo "<br>Error in file: " . $e->getFile() . " on line " . $e->getLine();
        }
    }
    
    public function create_test_user() {
        try {
            // Create the test user you're trying to login with
            $user_data = array(
                'username' => 'robinsingh19',
                'email' => 'robinsingh19@gmail.com',
                'password' => password_hash('robin123', PASSWORD_DEFAULT),
                'created_at' => date('Y-m-d H:i:s')
            );
            
            // Check if user already exists
            $existing = $this->db->get_where('users', array('email' => $user_data['email']))->row();
            if ($existing) {
                echo "User already exists: " . $user_data['email'];
                return;
            }
            
            if ($this->db->insert('users', $user_data)) {
                echo "✅ Test user created successfully!<br>";
                echo "Email: robinsingh19@gmail.com<br>";
                echo "Password: robin123<br>";
                echo "You can now try logging in with these credentials.";
            } else {
                echo "❌ Failed to create user<br>";
                echo "Database error: " . json_encode($this->db->error());
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    
    public function fix_sessions_table() {
        try {
            echo "<h2>Fixing Sessions Table</h2>";
            
            // First, let's see the current structure
            if ($this->db->table_exists('ci_sessions')) {
                echo "Current ci_sessions table structure:<br>";
                $fields = $this->db->field_data('ci_sessions');
                echo "<pre>";
                foreach ($fields as $field) {
                    echo "- {$field->name} ({$field->type}, max_length: {$field->max_length})\n";
                }
                echo "</pre>";
                
                // Drop the existing table
                echo "Dropping existing ci_sessions table...<br>";
                $this->db->query("DROP TABLE ci_sessions");
                echo "✅ Old table dropped<br>";
            }
            
            // Create the correct sessions table structure for CodeIgniter 2
            echo "Creating new ci_sessions table with correct structure...<br>";
            $session_sql = "CREATE TABLE `ci_sessions` (
                `session_id` varchar(40) DEFAULT '0' NOT NULL,
                `ip_address` varchar(45) DEFAULT '0' NOT NULL,
                `user_agent` varchar(120) NOT NULL,
                `last_activity` int(10) unsigned DEFAULT 0 NOT NULL,
                `user_data` text NOT NULL,
                PRIMARY KEY `ci_sessions_session_id` (`session_id`),
                KEY `last_activity_idx` (`last_activity`)
            );";
            
            if ($this->db->query($session_sql)) {
                echo "✅ Sessions table recreated successfully!<br>";
                echo "<br>New table structure:<br>";
                $fields = $this->db->field_data('ci_sessions');
                echo "<pre>";
                foreach ($fields as $field) {
                    echo "- {$field->name} ({$field->type}, max_length: {$field->max_length})\n";
                }
                echo "</pre>";
                echo "<br><strong>🎉 Sessions table fixed! Try logging in now.</strong>";
            } else {
                echo "❌ Failed to create sessions table<br>";
                echo "Database error: " . json_encode($this->db->error());
            }
            
        } catch (Exception $e) {
            echo "❌ Error fixing sessions table: " . $e->getMessage();
        }
    }
    
    public function check_password() {
        try {
            echo "<h2>Password Verification Test</h2>";
            
            // Get the user from database
            $this->db->where('email', 'robinsingh19@gmail.com');
            $query = $this->db->get('users');
            $user = $query->row_array();
            
            if (!$user) {
                echo "❌ User not found in database<br>";
                return;
            }
            
            echo "User found:<br>";
            echo "- ID: " . $user['id'] . "<br>";
            echo "- Username: " . $user['username'] . "<br>";
            echo "- Email: " . $user['email'] . "<br>";
            echo "- Password hash: " . substr($user['password'], 0, 20) . "...<br>";
            echo "- Hash length: " . strlen($user['password']) . "<br>";
            
            // Test different passwords
            $test_passwords = ['robin123', 'Robin123', 'ROBIN123', '123'];
            
            echo "<h3>Testing passwords:</h3>";
            foreach ($test_passwords as $test_pass) {
                $verify_result = password_verify($test_pass, $user['password']);
                echo "- '$test_pass': " . ($verify_result ? "✅ CORRECT" : "❌ Wrong") . "<br>";
            }
            
            // Check if password is properly hashed
            if (strlen($user['password']) < 50) {
                echo "<br>⚠️ WARNING: Password appears to NOT be properly hashed!<br>";
                echo "Password should be ~60 characters for bcrypt hash.<br>";
                echo "Current length: " . strlen($user['password']) . "<br>";
                
                echo "<br>Fixing password hash...<br>";
                $new_hash = password_hash('robin123', PASSWORD_DEFAULT);
                $this->db->where('id', $user['id']);
                $this->db->update('users', array('password' => $new_hash));
                
                echo "✅ Password hash updated for user ID " . $user['id'] . "<br>";
                echo "You can now login with: robinsingh19@gmail.com / robin123<br>";
            }
            
        } catch (Exception $e) {
            echo "❌ Error: " . $e->getMessage();
        }
    }
    
    public function disable_sessions() {
        echo "<h2>Temporary Session Disable Test</h2>";
        echo "This will temporarily disable database sessions to test login.<br><br>";
        
        // Show current config
        echo "Current session config:<br>";
        echo "- Use database: " . ($this->config->item('sess_use_database') ? 'TRUE' : 'FALSE') . "<br>";
        echo "- Table name: " . $this->config->item('sess_table_name') . "<br>";
        
        echo "<br>To temporarily disable database sessions:<br>";
        echo "1. Edit your config/config.php file<br>";
        echo "2. Change: <code>\$config['sess_use_database'] = FALSE;</code><br>";
        echo "3. Try logging in<br>";
        echo "4. If login works, then it's a session table issue<br>";
        echo "5. If login still fails, it's a password/user issue<br>";
    }
}