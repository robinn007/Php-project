<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Student_model extends CI_Model {
    
       public function get_students($search = '', $states = array()) {
        $this->db->select('students.*');
        $this->db->from('students');
        $this->db->where('students.is_deleted', 0);
        
        if ($search) {
            $search = $this->db->escape_like_str($search);
            $this->db->where("(students.name LIKE '%$search%' OR students.email LIKE '%$search%' OR students.phone LIKE '%$search%' OR students.address LIKE '%$search%')", NULL, FALSE);
        }
        
        if (!empty($states) && is_array($states) && count($states) > 0) {
            $this->db->where_in('students.state', $states);
            log_message('debug', 'Filtering by states: ' . json_encode($states));
        }
        
        $query = $this->db->get();
        $result = $query->result_array();
        
        log_message('debug', 'Found ' . count($result) . ' students matching criteria');
        return $result;
    }

    public function add_status_field() {
    $this->load->dbforge();
    
    $query = $this->db->query("SHOW COLUMNS FROM students LIKE 'status'");
    
    if ($query->num_rows() == 0) {
        $fields = array(
            'status' => array(
                'type' => 'VARCHAR',
                'constraint' => '20',
                'default' => 'offline'
            )
        );
        
        $this->dbforge->add_column('students', $fields);
        
        $this->db->query("UPDATE students SET status = 'offline' WHERE status IS NULL");
        
        log_message('info', 'Status field added successfully to students table');
    } else {
        log_message('info', 'Status field already exists in students table');
    }
}


     public function get_student($id) {
        log_message('debug', 'Querying student with ID: ' . $id);
        $this->db->select('students.*');
        $this->db->from('students');
        $this->db->where('students.id', $id);
        $this->db->where('students.is_deleted', 0);
        $query = $this->db->get();
        
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

    // Helper method to check if a user is online based on session
    private function is_user_online($email) {
        // Load session library if not already loaded
        if (!isset($this->session)) {
            $this->load->library('session');
        }

        // Check if user exists in users table
        $this->db->where('email', $email);
        $query = $this->db->get('users');
        if ($query->num_rows() === 0) {
            return false; // User not in users table
        }

        $user = $query->row_array();
        $user_id = $user['id'];
        
        // Check if user has an active session
        $session_user_id = $this->session->userdata('user_id');
        $logged_in = $this->session->userdata('logged_in');
        
        return ($session_user_id && $logged_in && $session_user_id == $user_id);
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
                log_message('error', 'Address exceeds maximum length of 5000 characters');
                return false;
            }
            // Validate state
            $valid_states = ['Rajasthan', 'Delhi', 'Uttar Pradesh', 'Punjab', 'Chandigarh', 'Himachal Pradesh', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Andaman and Nicobar Islands', 'Dadra and Nagar Haveli and Daman and Diu', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttarakhand', 'West Bengal'];
            if (isset($data['state']) && !in_array($data['state'], $valid_states)) {
                log_message('error', 'Invalid state value: ' . $data['state']);
                return false;
            }

            // Add created_at timestamp if not present
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }
            
            log_message('debug', 'Attempting to insert student: ' . json_encode($data));
            $result = $this->db->insert('students', $data);
            if ($result) {
                log_message('debug', 'Student inserted successfully, ID: ' . $this->db->insert_id());
            } else {
                $db_error = $this->db->error();
                log_message('error', 'Failed to insert student: ' . $db_error['message'] . ' (Code: ' . $db_error['code'] . ')');
            }
            return $result;

        case 'edit':
            if (!$id || !is_numeric($id)) {
                log_message('error', 'Invalid or missing student ID for edit: ' . $id);
                return false;
            }
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
                log_message('error', 'Address exceeds maximum length of 5000 characters');
                return false;
            }
            // Validate state
            $valid_states = ['Rajasthan', 'Delhi', 'Uttar Pradesh', 'Punjab', 'Chandigarh', 'Himachal Pradesh', 'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat', 'Haryana', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh', 'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Andaman and Nicobar Islands', 'Dadra and Nagar Haveli and Daman and Diu', 'Jammu and Kashmir', 'Ladakh', 'Lakshadweep', 'Puducherry', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttarakhand', 'West Bengal'];
            if (isset($data['state']) && !in_array($data['state'], $valid_states)) {
                log_message('error', 'Invalid state value: ' . $data['state']);
                return false;
            }
            log_message('debug', 'Attempting to update student ID: ' . $id . ' with data: ' . json_encode($data));
            $this->db->where('id', $id);
            $result = $this->db->update('students', $data);
            if ($result) {
                log_message('debug', 'Student updated successfully, ID: ' . $id);
            } else {
                $db_error = $this->db->error();
                log_message('error', 'Failed to update student ID: ' . $id . ': ' . $db_error['message'] . ' (Code: ' . $db_error['code'] . ')');
            }
            return $result;

        case 'delete':
            log_message('debug', 'Attempting to soft delete student ID: ' . $id);
            $this->db->where('id', $id);
            $result = $this->db->update('students', array('is_deleted' => 1));
            if ($result) {
                log_message('debug', 'Student soft deleted successfully, ID: ' . $id);
            } else {
                $db_error = $this->db->error();
                log_message('error', 'Failed to soft delete student ID: ' . $id . ': ' . $db_error['message'] . ' (Code: ' . $db_error['code'] . ')');
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

        $query = $this->db->query("SHOW COLUMNS FROM students LIKE 'state'");
        $output .= '<p>State field exists: ' . ($query->num_rows() > 0 ? 'Yes' : 'No') . '</p>';

        $this->db->where('is_deleted', 0);
        $this->db->from('students');
        $active_students = $this->db->count_all_results();
        $output .= '<p>Number of active students: ' . $active_students . '</p>';

        return $output;
    }
}
