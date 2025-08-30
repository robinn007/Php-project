<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_column_to_students extends CI_Migration {

    // A address field is added to the table for students table
    public function up() {
        $fields = array(
            'address' => array(
                'type' => 'TEXT',  // Create a new column 'address' of type TEXT.
                'null' => TRUE  // Allow the column to have NULL values.
            )
        );
         // Add the new 'address' column to the 'students' table
        $this->dbforge->add_column('students', $fields); 
    }

     // The 'down' method is used to roll back/reverse the migration.
    public function down() {
        $this->dbforge->drop_column('students', 'address'); // Remove the 'address' column from 'students' table
    }
}