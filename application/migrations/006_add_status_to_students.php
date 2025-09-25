<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_status_to_students extends CI_Migration {
    public function up() {
        $fields = array(
            'status' => array(
                'type' => 'ENUM("online","offline")',
                'default' => 'offline',
                'null' => FALSE
            )
        );
        $this->dbforge->add_column('students', $fields);
        
        // Set all existing students to offline by default
        $this->db->update('students', array('status' => 'offline'));
    }

    public function down() {
        $this->dbforge->drop_column('students', 'status');
    }
}