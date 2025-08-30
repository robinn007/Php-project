<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    public function get_students() {
        try {
            $this->db->where('is_deleted', 0);
            $query = $this->db->get('students');
            if (!$query) {
                log_message('error', 'get_students failed: ' . $this->db->error()['message']);
                return array();
            }
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'Exception in get_students: ' . $e->getMessage());
            return array();
        }
    }
    
    public function get_student($id) {
        try {
            $this->db->where('id', $id);
            $this->db->where('is_deleted', 0);
            $query = $this->db->get('students');
            if (!$query) {
                log_message('error', "get_student failed for id=$id: " . $this->db->error()['message']);
                return null;
            }
            return $query->row();
        } catch (Exception $e) {
            log_message('error', "Exception in get_student for id=$id: " . $e->getMessage());
            return null;
        }
    }
    
    public function get_deleted_students() {
        try {
            $this->db->where('is_deleted', 1);
            $query = $this->db->get('students');
            if (!$query) {
                log_message('error', 'get_deleted_students failed: ' . $this->db->error()['message']);
                return array();
            }
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'Exception in get_deleted_students: ' . $e->getMessage());
            return array();
        }
    }
    
    public function manage_student($action, $id = null, $data = array()) {
        log_message('debug', "manage_student called with action=$action, id=$id, data=" . json_encode($data));
        
        try {
            switch ($action) {
                case 'add':
                    // Add created_at timestamp if column exists
                    if ($this->db->field_exists('created_at', 'students')) {
                        $data['created_at'] = date('Y-m-d H:i:s');
                    }
                    
                    $result = $this->db->insert('students', $data);
                    if ($result) {
                        log_message('debug', 'Student added successfully: ' . $this->db->last_query());
                        return $this->db->insert_id();
                    } else {
                        $error = $this->db->error();
                        log_message('error', 'Failed to add student: ' . $error['message']);
                        return false;
                    }
                    
                case 'edit':
                    if (!$id) {
                        log_message('error', 'Edit failed: No ID provided');
                        return false;
                    }
                    
                    // Add updated_at timestamp if column exists
                    if ($this->db->field_exists('updated_at', 'students')) {
                        $data['updated_at'] = date('Y-m-d H:i:s');
                    }
                    
                    $this->db->where('id', $id);
                    $this->db->where('is_deleted', 0); // Only update active records
                    $result = $this->db->update('students', $data);
                    
                    if ($result && $this->db->affected_rows() > 0) {
                        log_message('debug', "Student updated successfully: id=$id, query=" . $this->db->last_query());
                        return true;
                    } else {
                        $error = $this->db->error();
                        log_message('error', "Failed to update student id=$id: " . $error['message'] . ", affected_rows: " . $this->db->affected_rows());
                        return false;
                    }
                    
                case 'delete':
                    if (!$id) {
                        log_message('error', 'Delete failed: No ID provided');
                        return false;
                    }
                    
                    // Check if student exists and is not already deleted
                    $this->db->where('id', $id);
                    $this->db->where('is_deleted', 0);
                    $existing = $this->db->get('students');
                    
                    if (!$existing || $existing->num_rows() === 0) {
                        log_message('error', "Delete failed: Student id=$id not found or already deleted");
                        return false;
                    }
                    
                    // Soft delete - set is_deleted to 1
                    $update_data = array('is_deleted' => 1);
                    
                    // Add deleted_at timestamp if column exists
                    if ($this->db->field_exists('deleted_at', 'students')) {
                        $update_data['deleted_at'] = date('Y-m-d H:i:s');
                    }
                    
                    $this->db->where('id', $id);
                    $this->db->where('is_deleted', 0); // Only delete active records
                    $result = $this->db->update('students', $update_data);
                    
                    if ($result && $this->db->affected_rows() > 0) {
                        log_message('debug', "Student soft-deleted successfully: id=$id, query=" . $this->db->last_query());
                        return true;
                    } else {
                        $error = $this->db->error();
                        log_message('error', "Failed to delete student id=$id: " . $error['message'] . ", affected_rows: " . $this->db->affected_rows());
                        return false;
                    }
                    
                default:
                    log_message('error', "Invalid action in manage_student: $action");
                    return false;
            }
        } catch (Exception $e) {
            log_message('error', "Exception in manage_student action=$action, id=$id: " . $e->getMessage());
            return false;
        }
    }
    
    // Additional helper method to permanently delete a student (if needed)
    public function permanent_delete($id) {
        try {
            if (!$id) {
                return false;
            }
            
            $this->db->where('id', $id);
            $result = $this->db->delete('students');
            
            if ($result) {
                log_message('debug', "Student permanently deleted: id=$id");
                return true;
            } else {
                $error = $this->db->error();
                log_message('error', "Failed to permanently delete student id=$id: " . $error['message']);
                return false;
            }
        } catch (Exception $e) {
            log_message('error', "Exception in permanent_delete for id=$id: " . $e->getMessage());
            return false;
        }
    }
    
    // Method to restore a soft-deleted student
    public function restore_student($id) {
        try {
            if (!$id) {
                return false;
            }
            
            $update_data = array('is_deleted' => 0);
            
            // Remove deleted_at timestamp if column exists
            if ($this->db->field_exists('deleted_at', 'students')) {
                $update_data['deleted_at'] = null;
            }
            
            $this->db->where('id', $id);
            $this->db->where('is_deleted', 1);
            $result = $this->db->update('students', $update_data);
            
            if ($result && $this->db->affected_rows() > 0) {
                log_message('debug', "Student restored successfully: id=$id");
                return true;
            } else {
                log_message('error', "Failed to restore student id=$id");
                return false;
            }
        } catch (Exception $e) {
            log_message('error', "Exception in restore_student for id=$id: " . $e->getMessage());
            return false;
        }
    }
}