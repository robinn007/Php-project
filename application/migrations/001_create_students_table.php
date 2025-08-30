<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_students_table extends CI_Migration {

    public function up() {
        // Define the students table schema
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'name' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'email' => array(
                'type' => 'VARCHAR',
                'constraint' => '100'
            ),
            'phone' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
                'null' => TRUE
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'default' => 'CURRENT_TIMESTAMP'
            )
        ));

        $this->dbforge->add_key('id', TRUE); // Set id as primary key
        $this->dbforge->create_table('students'); // Create the table
    }

    public function down() {
        // Drop the students table if rolling back
        $this->dbforge->drop_table('students');
    }
}