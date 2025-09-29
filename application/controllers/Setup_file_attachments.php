<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Setup controller for file attachments feature
 * Access at: http://localhost/setup_file_attachments
 */
class Setup_file_attachments extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->dbforge();
    }
    
    public function index() {
        echo "<h1>File Attachments Setup</h1>";
        echo "<p>Setting up database tables for file attachments...</p>";
        
        try {
            // Create file_attachments table
            $this->create_file_attachments_table();
            
            // Modify messages table
            $this->modify_messages_table();
            
            // Create uploads directory
            $this->create_uploads_directory();
            
            echo "<div style='color: green; font-weight: bold; margin: 20px 0;'>✅ File attachments setup completed successfully!</div>";
            echo "<p><a href='/chat'>Go to Chat</a></p>";
            
        } catch (Exception $e) {
            echo "<div style='color: red; font-weight: bold; margin: 20px 0;'>❌ Error: " . $e->getMessage() . "</div>";
        }
    }
    
    private function create_file_attachments_table() {
        if (!$this->db->table_exists('file_attachments')) {
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'auto_increment' => TRUE
                ),
                'message_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE
                ),
                'sender_email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => FALSE
                ),
                'receiver_email' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => TRUE
                ),
                'group_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'original_filename' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => FALSE
                ),
                'stored_filename' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '255',
                    'null' => FALSE
                ),
                'file_path' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '500',
                    'null' => FALSE
                ),
                'file_size' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE,
                    'comment' => 'Size in bytes'
                ),
                'file_type' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '100',
                    'null' => FALSE
                ),
                'message_type' => array(
                    'type' => 'ENUM',
                    'constraint' => array('direct', 'group'),
                    'default' => 'direct'
                ),
                'created_at' => array(
                    'type' => 'TIMESTAMP',
                    'null' => FALSE,
                    'default' => 'CURRENT_TIMESTAMP'
                )
            );
            
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('sender_email');
            $this->dbforge->add_key('receiver_email');
            $this->dbforge->add_key('group_id');
            $this->dbforge->add_key('created_at');
            $this->dbforge->create_table('file_attachments');
            
            // Add foreign key for group_id
            $this->db->query('ALTER TABLE file_attachments ADD CONSTRAINT fk_file_group_id FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE');
            
            echo "<p>✅ File attachments table created</p>";
        } else {
            echo "<p>ℹ️ File attachments table already exists</p>";
        }
    }
    
    private function modify_messages_table() {
        // Check if file_attachment_id column exists
        $query = $this->db->query("SHOW COLUMNS FROM messages LIKE 'file_attachment_id'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE messages ADD COLUMN file_attachment_id INT(11) UNSIGNED DEFAULT NULL AFTER message_type");
            echo "<p>✅ Added file_attachment_id column to messages table</p>";
        } else {
            echo "<p>ℹ️ file_attachment_id column already exists in messages table</p>";
        }
        
        // Check if has_attachment column exists
        $query = $this->db->query("SHOW COLUMNS FROM messages LIKE 'has_attachment'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE messages ADD COLUMN has_attachment TINYINT(1) NOT NULL DEFAULT 0 AFTER file_attachment_id");
            echo "<p>✅ Added has_attachment column to messages table</p>";
        } else {
            echo "<p>ℹ️ has_attachment column already exists in messages table</p>";
        }
        
        // Add index
        $query = $this->db->query("SHOW INDEX FROM messages WHERE Key_name = 'idx_attachment'");
        if ($query->num_rows() == 0) {
            $this->db->query("ALTER TABLE messages ADD KEY idx_attachment (file_attachment_id)");
            echo "<p>✅ Added index for file_attachment_id in messages table</p>";
        } else {
            echo "<p>ℹ️ Index for file_attachment_id already exists</p>";
        }
    }
    
    private function create_uploads_directory() {
        $upload_path = './uploads/chat_attachments/';
        
        if (!is_dir($upload_path)) {
            if (mkdir($upload_path, 0755, true)) {
                echo "<p> Created uploads directory: {$upload_path}</p>";
                
                // Create .htaccess for security
                $htaccess_content = "Options -Indexes\n";
                $htaccess_content .= "php_flag engine off\n";
                file_put_contents($upload_path . '.htaccess', $htaccess_content);
                echo "<p> Created .htaccess for security</p>";
                
                // Create index.html
                file_put_contents($upload_path . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden.</h1></body></html>');
                echo "<p> Created index.html</p>";
            } else {
                throw new Exception('Failed to create uploads directory. Please create it manually with write permissions.');
            }
        } else {
            echo "<p>ℹ️ Uploads directory already exists</p>";
            
            if (!is_writable($upload_path)) {
                throw new Exception('Uploads directory is not writable. Please set permissions to 0755.');
            } else {
                echo "<p> Uploads directory is writable</p>";
            }
        }
    }
}