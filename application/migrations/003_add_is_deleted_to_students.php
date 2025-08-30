<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_is_deleted_to_students extends CI_Migration {

    // A archieve/is_deleted field is added to the table for students table
    public function up() {
        $fields = array(
            'is_deleted' => array(
                'type' => 'TINYINT',  // Create a new column 'address' of type small integer value.
                'constraint' => 1, //   1 - true
                'default' => 0, // 0 - false
                'null' => FALSE
            )
        );
          // Add the new 'is_deleted' column to the 'students' table
        $this->dbforge->add_column('students', $fields);
    }

     // The 'down' method is used to roll back/reverse the migration.
    public function down() {
        $this->dbforge->drop_column('students', 'is_deleted'); // Remove the 'is_deleted' column from the 'students' table
    }
}