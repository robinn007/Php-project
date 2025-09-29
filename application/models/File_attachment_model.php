<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class File_attachment_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        log_message('debug', 'File_attachment_model loaded');
    }

    /**
     * Store file attachment record
     */
    public function store_attachment($data) {
        try {
            $attachment_data = array(
                'sender_email' => $data['sender_email'],
                'receiver_email' => isset($data['receiver_email']) ? $data['receiver_email'] : NULL,
                'group_id' => isset($data['group_id']) ? $data['group_id'] : NULL,
                'original_filename' => $data['original_filename'],
                'stored_filename' => $data['stored_filename'],
                'file_path' => $data['file_path'],
                'file_size' => $data['file_size'],
                'file_type' => $data['file_type'],
                'message_type' => $data['message_type'],
                'created_at' => date('Y-m-d H:i:s')
            );
            
            $result = $this->db->insert('file_attachments', $attachment_data);
            
            if ($result) {
                return $this->db->insert_id();
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Error storing file attachment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create message with attachment
     */
    public function create_message_with_attachment($sender_email, $receiver_email, $group_id, $message, $attachment_id, $message_type) {
        try {
            $message_data = array(
                'sender_email' => $sender_email,
                'receiver_email' => $message_type === 'direct' ? $receiver_email : NULL,
                'group_id' => $message_type === 'group' ? $group_id : NULL,
                'message' => $message,
                'message_type' => $message_type,
                'file_attachment_id' => $attachment_id,
                'has_attachment' => 1,
                'created_at' => date('Y-m-d H:i:s')
            );
            
            $result = $this->db->insert('messages', $message_data);
            
            if ($result) {
                $message_id = $this->db->insert_id();
                
                // Update attachment with message_id
                $this->db->where('id', $attachment_id);
                $this->db->update('file_attachments', array('message_id' => $message_id));
                
                return $message_id;
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Error creating message with attachment: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get attachment by ID
     */
    public function get_attachment($attachment_id) {
        try {
            $this->db->where('id', $attachment_id);
            $query = $this->db->get('file_attachments');
            
            return $query->row_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting attachment: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user has permission to download file
     */
    public function can_download($attachment_id, $user_email) {
        try {
            $this->db->select('fa.*, m.message_type');
            $this->db->from('file_attachments fa');
            $this->db->join('messages m', 'fa.id = m.file_attachment_id', 'left');
            $this->db->where('fa.id', $attachment_id);
            $query = $this->db->get();
            
            if ($query->num_rows() === 0) {
                return false;
            }
            
            $attachment = $query->row_array();
            
            // Check if user is sender or receiver (direct message)
            if ($attachment['message_type'] === 'direct') {
                if ($attachment['sender_email'] === $user_email || 
                    $attachment['receiver_email'] === $user_email) {
                    return true;
                }
            }
            
            // Check if user is group member
            if ($attachment['message_type'] === 'group' && $attachment['group_id']) {
                $this->db->where('group_id', $attachment['group_id']);
                $this->db->where('email', $user_email);
                $this->db->where('is_active', 1);
                $member_query = $this->db->get('group_members');
                
                return $member_query->num_rows() > 0;
            }
            
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'Error checking download permission: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete attachment
     */
    public function delete_attachment($attachment_id) {
        try {
            $attachment = $this->get_attachment($attachment_id);
            
            if ($attachment && file_exists($attachment['file_path'])) {
                unlink($attachment['file_path']);
            }
            
            $this->db->where('id', $attachment_id);
            return $this->db->delete('file_attachments');
            
        } catch (Exception $e) {
            log_message('error', 'Error deleting attachment: ' . $e->getMessage());
            return false;
        }
    }
}