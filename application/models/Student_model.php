<?php
class Student_model extends CI_Model {
    public function get_students() {
        $this->db->where('is_deleted', 0);
        $query = $this->db->get('students');
        return $query->result_array();
    }

    public function get_student($id) {
        $this->db->where('id', $id);
        $query = $this->db->get('students');
        return $query->row_array();
    }

    public function get_deleted_students() {
        $this->db->where('is_deleted', 1);
        $query = $this->db->get('students');
        return $query->result_array();
    }

    public function manage_student($action, $id = null, $data = array()) {
        switch ($action) {
            case 'add':
                return $this->db->insert('students', $data);
            case 'edit':
                $this->db->where('id', $id);
                return $this->db->update('students', $data);
            case 'delete':
                $this->db->where('id', $id);
                return $this->db->update('students', array('is_deleted' => 1));
            default:
                return false;
        }
    }

    public function restore_student($id) {
        $this->db->where('id', $id);
        return $this->db->update('students', array('is_deleted' => 0));
    }

    public function permanent_delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete('students');
    }

    public function test_database() {
        $output = '';
        $output .= '<p>Database connection successful!</p>';
        $output .= '<p>Database: ' . $this->db->database . '</p>';

        $query = $this->db->query("SHOW TABLES LIKE 'students'");
        $output .= '<p>Students table exists: ' . ($query->num_rows() > 0 ? 'Yes' : 'No') . '</p>';

        $this->db->from('students');
        $total_students = $this->db->count_all_results();
        $output .= '<p>Number of students (including deleted): ' . $total_students . '</p>';

        $query = $this->db->query("SHOW COLUMNS FROM students LIKE 'address'");
        $output .= '<p>Address field exists: ' . ($query->num_rows() > 0 ? 'Yes' : 'No') . '</p>';

        $query = $this->db->query("SHOW COLUMNS FROM students LIKE 'is_deleted'");
        $output .= '<p>Is Deleted field exists: ' . ($query->num_rows() > 0 ? 'Yes' : 'No') . '</p>';

        $this->db->where('is_deleted', 0);
        $this->db->from('students');
        $active_students = $this->db->count_all_results();
        $output .= '<p>Number of active students: ' . $active_students . '</p>';

        return $output;
    }
}