<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Group_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
        log_message('debug', 'Group_model loaded');
    }

    /**
     * Create a new group
     */

    public function create_group($name, $description, $created_by, $members = array()) {
        try {
            log_message('debug', 'Group_model::create_group - Name: ' . $name . ', Created by: ' . $created_by . ', Members: ' . print_r($members, true));
            
            $this->db->trans_start();
            
            // Create the group
            $group_data = array(
                'name' => $name,
                'description' => $description,
                'created_by' => $created_by,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            log_message('debug', 'Group_model::create_group - Inserting group: ' . print_r($group_data, true));
            $this->db->insert('groups', $group_data);
            $group_id = $this->db->insert_id();
            
            log_message('debug', 'Group_model::create_group - Last query: ' . $this->db->last_query());
            
            if (!$group_id) {
                throw new Exception('Failed to create group - no ID returned: ' . $this->db->error()['message']);
            }
            
            log_message('debug', 'Group_model::create_group - Group created with ID: ' . $group_id);
            
            // Add creator as admin member
            $creator_member = array(
                'group_id' => $group_id,
                'email' => $created_by, // Changed from member_email to email
                 'role' => 'admin',
                'joined_at' => date('Y-m-d H:i:s'),
                'is_active' => 1
            );
            
            log_message('debug', 'Group_model::create_group - Inserting creator member: ' . print_r($creator_member, true));
            $insert_result = $this->db->insert('group_members', $creator_member);
            log_message('debug', 'Group_model::create_group - Creator insert query: ' . $this->db->last_query());
            
            if (!$insert_result) {
                throw new Exception('Failed to add creator as admin member: ' . $this->db->error()['message']);
            }
            
            log_message('debug', 'Group_model::create_group - Creator added as admin');
            
            // Add other members
            if (!empty($members)) {
                foreach ($members as $member_email) {
                    if ($member_email !== $created_by) {
                        $member_data = array(
                            'group_id' => $group_id,
                            'email' => $member_email, // Changed from member_email to email
                             'role' => 'member',
                            'joined_at' => date('Y-m-d H:i:s'),
                            'is_active' => 1
                        );
                        
                        log_message('debug', 'Group_model::create_group - Inserting member: ' . $member_email . ', Data: ' . print_r($member_data, true));
                        $member_insert = $this->db->insert('group_members', $member_data);
                        log_message('debug', 'Group_model::create_group - Member insert query: ' . $this->db->last_query());
                        
                        if (!$member_insert) {
                            throw new Exception('Failed to add member ' . $member_email . ': ' . $this->db->error()['message']);
                        }
                        log_message('debug', 'Group_model::create_group - Added member: ' . $member_email);
                    }
                }
            }
            
            $this->db->trans_complete();
            
            if ($this->db->trans_status() === FALSE) {
                throw new Exception('Transaction failed during group creation: ' . $this->db->error()['message']);
            }
            
            log_message('debug', 'Group_model::create_group - Group created successfully with ID: ' . $group_id);
            return $group_id;
            
        } catch (Exception $e) {
            log_message('error', 'Group_model::create_group - Error: ' . $e->getMessage());
            log_message('error', 'Group_model::create_group - Last query: ' . $this->db->last_query());
            $this->db->trans_rollback();
            return false;
        }
    }


    /**
     * Get groups for a user
     */
    public function get_user_groups($email) {
        try {
            $this->db->select('g.*, gm.role, COUNT(gm2.id) as member_count');
            $this->db->from('groups g');
            $this->db->join('group_members gm', 'g.id = gm.group_id');
            $this->db->join('group_members gm2', 'g.id = gm2.group_id', 'left');
            $this->db->where('gm.member_email', $email);
            $this->db->where('gm.is_active', 1);
            $this->db->where('g.is_active', 1);
            $this->db->group_by('g.id');
            $this->db->order_by('g.updated_at', 'DESC');
            
            $query = $this->db->get();
            return $query->result_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting user groups: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get group members
     */
    public function get_group_members($group_id) {
        try {
            $this->db->select('gm.*, s.name, s.status');
            $this->db->from('group_members gm');
            $this->db->join('students s', 'gm.member_email = s.email', 'left');
            $this->db->where('gm.group_id', $group_id);
            $this->db->where('gm.is_active', 1);
            $this->db->order_by('gm.role', 'ASC'); // Admins first
            $this->db->order_by('gm.joined_at', 'ASC');
            
            $query = $this->db->get();
            return $query->result_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting group members: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Get group details
     */
    public function get_group($group_id) {
        try {
            $this->db->select('g.*, COUNT(gm.id) as member_count');
            $this->db->from('groups g');
            $this->db->join('group_members gm', 'g.id = gm.group_id', 'left');
            $this->db->where('g.id', $group_id);
            $this->db->where('g.is_active', 1);
            $this->db->group_by('g.id');
            
            $query = $this->db->get();
            return $query->row_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting group: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add member to group
     */
    public function add_member($group_id, $member_email, $role = 'member') {
        try {
            // Check if member already exists
            $this->db->where('group_id', $group_id);
            $this->db->where('member_email', $member_email);
            $existing = $this->db->get('group_members');
            
            if ($existing->num_rows() > 0) {
                // Reactivate if inactive
                $member = $existing->row_array();
                if ($member['is_active'] == 0) {
                    $this->db->where('id', $member['id']);
                    $this->db->update('group_members', array('is_active' => 1));
                    return true;
                }
                return false; // Already active member
            }
            
            // Add new member
            $member_data = array(
                'group_id' => $group_id,
                'member_email' => $member_email,
                 'role' => $role,
                'joined_at' => date('Y-m-d H:i:s')
            );
            
            return $this->db->insert('group_members', $member_data);
            
        } catch (Exception $e) {
            log_message('error', 'Error adding group member: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remove member from group
     */
    public function remove_member($group_id, $member_email) {
        try {
            $this->db->where('group_id', $group_id);
            $this->db->where('member_email', $member_email);
            return $this->db->update('group_members', array('is_active' => 0));
            
        } catch (Exception $e) {
            log_message('error', 'Error removing group member: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is group member
     */
    public function is_group_member($group_id, $email) {
        try {
            $this->db->where('group_id', $group_id);
            $this->db->where('member_email', $email);
            $this->db->where('is_active', 1);
            $query = $this->db->get('group_members');
            
            return $query->num_rows() > 0;
            
        } catch (Exception $e) {
            log_message('error', 'Error checking group membership: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get group messages
     */
    public function get_group_messages($group_id, $limit = 50, $offset = 0) {
        try {
            $this->db->select('m.*, s.name as sender_name');
            $this->db->from('messages m');
            $this->db->join('students s', 'm.sender_email = s.email', 'left');
            $this->db->where('m.group_id', $group_id);
            $this->db->where('m.message_type', 'group');
            $this->db->order_by('m.created_at', 'ASC');
            $this->db->limit($limit, $offset);
            
            $query = $this->db->get();
            return $query->result_array();
            
        } catch (Exception $e) {
            log_message('error', 'Error getting group messages: ' . $e->getMessage());
            return array();
        }
    }

    /**
     * Store group message
     */
    public function store_group_message($sender_email, $group_id, $message) {
        try {
            $message_data = array(
                'sender_email' => $sender_email,
                'group_id' => $group_id,
                'message' => $message,
                'message_type' => 'group',
                'created_at' => date('Y-m-d H:i:s')
            );
            
            $result = $this->db->insert('messages', $message_data);
            
            if ($result) {
                // Update group's updated_at timestamp
                $this->db->where('id', $group_id);
                $this->db->update('groups', array('updated_at' => date('Y-m-d H:i:s')));
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error storing group message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last group message for each group (for conversation list)
     */
   public function get_group_last_messages($email) {
    try {
        $sql = "
            SELECT 
                g.id as group_id,
                g.name as group_name,
                g.description,
                m.message as last_message,
                m.sender_email,
                m.created_at,
                s.name as sender_name,
                COUNT(gm.id) as member_count
            FROM groups g
            INNER JOIN group_members gm ON g.id = gm.group_id
            LEFT JOIN messages m ON g.id = m.group_id AND m.id = (
                SELECT MAX(id) 
                FROM messages 
                WHERE group_id = g.id AND message_type = 'group'
            )
            LEFT JOIN students s ON m.sender_email = s.email
            WHERE gm.email = ?
            AND g.is_active = 1
            GROUP BY g.id
            ORDER BY m.created_at DESC
        ";
        
        $query = $this->db->query($sql, array($email));
        $results = $query->result_array();
        
        log_message('debug', 'Group last messages query executed: ' . $this->db->last_query() . ' - Rows: ' . count($results));
        return $results;
        
    } catch (Exception $e) {
        log_message('error', 'Error in get_group_last_messages: ' . $e->getMessage() . ' - Query: ' . $this->db->last_query());
        return array();
    }
}

    /**
     * Update group details
     */
    public function update_group($group_id, $data) {
        try {
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->db->where('id', $group_id);
            return $this->db->update('groups', $data);
            
        } catch (Exception $e) {
            log_message('error', 'Error updating group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete group (soft delete)
     */
    public function delete_group($group_id) {
        try {
            $this->db->where('id', $group_id);
            return $this->db->update('groups', array('is_active' => 0));
            
        } catch (Exception $e) {
            log_message('error', 'Error deleting group: ' . $e->getMessage());
            return false;
        }
    }
}
