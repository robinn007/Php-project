<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup_group_chat extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->dbforge();
        $this->load->model('Student_model');
        $this->load->model('Group_model');
    }
    
    public function index() {
        echo "<h1>Group Chat Setup</h1>";
        echo "<p>Setting up database tables for group chat functionality...</p>";
        
        try {
            // Create groups table
            $this->create_groups_table();
            
            // Create group_members table
            $this->create_group_members_table();
            
            // Modify messages table
            $this->modify_messages_table();
            
            // Add status field to students table if not exists
            $this->add_status_field();
            
            // Insert sample data
            $this->insert_sample_data();
            
            echo "<div style='color: green; font-weight: bold; margin: 20px 0;'>✅ Group chat setup completed successfully!</div>";
            echo "<p><a href='/chat'>Go to Chat</a></p>";
            
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
    
    private function create_groups_table() {
        if (!$this->db->table_exists('groups')) {
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => FALSE
                ),
                'description' => array(
                    'type' => 'TEXT',
                    'null' => TRUE
                ),
                'created_by' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => FALSE
                ),
                'created_at' => array(
                    'type' => 'TIMESTAMP',
                    'null' => FALSE,
                    'default' => 'CURRENT_TIMESTAMP'
                ),
                'updated_at' => array(
                    'type' => 'TIMESTAMP',
                    'null' => FALSE,
                    'default' => 'CURRENT_TIMESTAMP'
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 1
                )
            );
            
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('groups');
            echo "<p>✅ Groups table created</p>";
        } else {
            echo "<p>ℹ️ Groups table already exists</p>";
        }
    }
    
    private function create_group_members_table() {
        if (!$this->db->table_exists('group_members')) {
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'group_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => FALSE
                ),
                'member_email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => FALSE
                ),
                'role' => array(
                    'type' => 'ENUM',
                    'constraint' => array('admin', 'member'),
                    'default' => 'member'
                ),
                'joined_at' => array(
                    'type' => 'TIMESTAMP',
                    'null' => FALSE,
                    'default' => 'CURRENT_TIMESTAMP'
                ),
                'is_active' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 1
                )
            );
            
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('group_id');
            $this->dbforge->create_table('group_members');
            
            // Add foreign key constraint
            $this->db->query('ALTER TABLE group_members ADD CONSTRAINT fk_group_members_group_id FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE');
            
            echo "<p>✅ Group members table created</p>";
        } else {
            echo "<p>ℹ️ Group members table already exists</p>";
        }
    }
    
    private function modify_messages_table() {
        // Check if group_id column exists
        $query = $this->db->query("SHOW COLUMNS FROM messages LIKE 'group_id'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE messages ADD COLUMN group_id INT(11) DEFAULT NULL AFTER receiver_email");
            echo "<p>✅ Added group_id column to messages table</p>";
        } else {
            echo "<p>ℹ️ group_id column already exists in messages table</p>";
        }
        
        // Check if message_type column exists
        $query = $this->db->query("SHOW COLUMNS FROM messages LIKE 'message_type'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE messages ADD COLUMN message_type ENUM('direct','group') NOT NULL DEFAULT 'direct' AFTER message");
            echo "<p>✅ Added message_type column to messages table</p>";
        } else {
            echo "<p>ℹ️ message_type column already exists in messages table</p>";
        }
        
        // Add foreign key constraint for group_id if it doesn't exist
        $query = $this->db->query("SELECT COUNT(*) as count FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_NAME = 'messages' AND CONSTRAINT_NAME = 'fk_messages_group_id'");
        $result = $query->row_array();
        if ($result['count'] == 0) {
            $this->db->query('ALTER TABLE messages ADD CONSTRAINT fk_messages_group_id FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE');
            echo "<p>✅ Added foreign key constraint for group_id in messages table</p>";
        } else {
            echo "<p>ℹ️ Foreign key constraint already exists for group_id in messages table</p>";
        }
    }
    
    private function add_status_field() {
        $this->Student_model->add_status_field();
        echo "<p>✅ Status field added to students table</p>";
    }
    
    private function insert_sample_data() {
        // Check if we have any groups
        $this->db->from('groups');
        $group_count = $this->db->count_all_results();
        
        if ($group_count == 0) {
            // Get some users to create sample groups
            $this->db->select('email');
            $this->db->from('students');
            $this->db->where('is_deleted', 0);
            $this->db->limit(5);
            $query = $this->db->get();
            $students = $query->result_array();
            
            if (count($students) >= 3) {
                // Create a sample group
                $creator_email = $students[0]['email'];
                $member_emails = array_slice(array_column($students, 'email'), 1, 2);
                
                $group_id = $this->Group_model->create_group(
                    'General Discussion',
                    'A place for general conversations and updates',
                    $creator_email,
                    $member_emails
                );
                
                if ($group_id) {
                    echo "<p>✅ Sample group 'General Discussion' created with ID: $group_id</p>";
                    
                    // Add a sample message
                    $this->Group_model->store_group_message($creator_email, $group_id, 'Welcome to the group! 👋');
                    echo "<p>✅ Sample welcome message added to group</p>";
                } else {
                    echo "<p>⚠️ Could not create sample group</p>";
                }
            } else {
                echo "<p>⚠️ Not enough students to create sample group (need at least 3)</p>";
            }
        } else {
            echo "<p>ℹ️ Groups already exist, skipping sample data creation</p>";
        }
    }
    
    public function reset() {
        echo "<h1>Reset Group Chat Tables</h1>";
        echo "<p style='color: red;'>⚠️ This will delete all group chat data!</p>";
        
        try {
            // Drop tables in correct order (due to foreign keys)
            $this->dbforge->drop_table('group_members', TRUE);
            $this->dbforge->drop_table('groups', TRUE);
            
            // Remove group-related columns from messages table
            $this->db->query("ALTER TABLE messages DROP FOREIGN KEY IF EXISTS fk_messages_group_id");
            $this->db->query("ALTER TABLE messages DROP COLUMN IF EXISTS group_id");
            $this->db->query("ALTER TABLE messages DROP COLUMN IF EXISTS message_type");
            
            echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>✅ Group chat tables reset successfully!</div>";
            echo "<p><a href='/setup_group_chat'>Run setup again</a></p>";
            
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
}
