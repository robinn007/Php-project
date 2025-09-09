<?php
class Student_model extends CI_Model {
    public function get_students() {
        $this->db->where('is_deleted', 0);
        $query = $this->db->get('students');
        return $query->result_array();
    }


    // when i try to edited student's data on UI step- 5
  public function get_student($id) {
    log_message('debug', 'Querying student with ID: ' . $id);
    $this->db->where('id', $id);
    $this->db->where('is_deleted', 0);
    $query = $this->db->get('students');
    
    if ($query === FALSE) {
        log_message('error', 'Database error in get_student for ID: ' . $id . ': ' . $this->db->error()['message']);
        return null;
    }

    $student = $query->row_array();
    if (!$student) {
        log_message('error', 'No student found for ID: ' . $id . ' or student is deleted.');
    } else {
        log_message('debug', 'Student retrieved for ID: ' . $id . ': ' . json_encode($student));
    }

    return $student;
}

    public function get_deleted_students() {
        $this->db->where('is_deleted', 1);
        $query = $this->db->get('students');
        return $query->result_array();
    }

  public function manage_student($action, $id = null, $data = array()) {
    switch ($action) {
        case 'add':
            // Check for duplicate email
            $this->db->where('email', $data['email']);
            $this->db->where('is_deleted', 0);
            $query = $this->db->get('students');
            if ($query->num_rows() > 0) {
                log_message('error', 'Duplicate email detected: ' . $data['email']);
                return false;
            }
            // Validate address length (plain text)
            if (isset($data['address']) && strlen(strip_tags($data['address'])) > 5000) {
                log_message('error', 'Address exceeds maximum length of 500 characters');
                return false;
            }
            log_message('debug', 'Attempting to insert student: ' . json_encode($data));
            $result = $this->db->insert('students', $data);
            if ($result) {
                log_message('debug', 'Student inserted successfully, ID: ' . $this->db->insert_id());
            } else {
                log_message('error', 'Failed to insert student: ' . $this->db->error()['message']);
            }
            return $result;
        case 'edit':
            // Check for duplicate email (excluding current student)
            $this->db->where('email', $data['email']);
            $this->db->where('id !=', $id);
            $this->db->where('is_deleted', 0);
            $query = $this->db->get('students');
            if ($query->num_rows() > 0) {
                log_message('error', 'Duplicate email detected: ' . $data['email']);
                return false;
            }
            // Validate address length (plain text)
            if (isset($data['address']) && strlen(strip_tags($data['address'])) > 5000) {
                log_message('error', 'Address exceeds maximum length of 500 characters');
                return false;
            }
            log_message('debug', 'Attempting to update student ID: ' . $id . ' with data: ' . json_encode($data));
            $this->db->where('id', $id);
            $result = $this->db->update('students', $data);
            if ($result) {
                log_message('debug', 'Student updated successfully, ID: ' . $id);
            } else {
                log_message('error', 'Failed to update student ID: ' . $id . ': ' . $this->db->error()['message']);
            }
            return $result;
        case 'delete':
            log_message('debug', 'Attempting to soft delete student ID: ' . $id);
            $this->db->where('id', $id);
            $result = $this->db->update('students', array('is_deleted' => 1));
            if ($result) {
                log_message('debug', 'Student soft deleted successfully, ID: ' . $id);
            } else {
                log_message('error', 'Failed to soft delete student ID: ' . $id . ': ' . $this->db->error()['message']);
            }
            return $result;
        default:
            log_message('error', 'Invalid action in manage_student: ' . $action);
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