<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_is_deleted_to_students extends CI_Migration {

    public function up() {
        $fields = array(
            'is_deleted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE
            )
        );
        $this->dbforge->add_column('students', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('students', 'is_deleted');
    }
}