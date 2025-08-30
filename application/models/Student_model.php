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
                    $result = $this->db->insert('students', $data);
                    if ($result) {
                        log_message('debug', 'Student added successfully: ' . $this->db->last_query());
                        return true;
                    } else {
                        log_message('error', 'Failed to add student: ' . $this->db->error()['message']);
                        return false;
                    }
                    
                case 'edit':
                    if (!$id) {
                        log_message('error', 'Edit failed: No ID provided');
                        return false;
                    }
                    $this->db->where('id', $id);
                    $result = $this->db->update('students', $data);
                    if ($result) {
                        log_message('debug', "Student updated successfully: id=$id, query=" . $this->db->last_query());
                        return true;
                    } else {
                        log_message('error', "Failed to update student id=$id: " . $this->db->error()['message']);
                        return false;
                    }
                    
                case 'delete':
                    if (!$id) {
                        log_message('error', 'Delete failed: No ID provided');
                        return false;
                    }
                    $this->db->where('id', $id);
                    $result = $this->db->update('students', array('is_deleted' => 1));
                    if ($result) {
                        log_message('debug', "Student soft-deleted successfully: id=$id, query=" . $this->db->last_query());
                        return true;
                    } else {
                        log_message('error', "Failed to delete student id=$id: " . $this->db->error()['message']);
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
}