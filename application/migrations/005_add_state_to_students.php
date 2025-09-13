<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_state_to_students extends CI_Migration {
    public function up() {
        $fields = array(
            'state' => array(
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => TRUE,
                'default' => 'Rajasthan'
            )
        );
        $this->dbforge->add_column('students', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('students', 'state');
    }
}