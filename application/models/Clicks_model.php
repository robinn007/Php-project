<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Clicks_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        log_message('debug', 'Clicks_model constructor called');
        
        if (!$this->db->conn_id) {
            log_message('error', 'Database connection failed in Clicks_model');
            throw new Exception('Database connection failed');
        }
    }

    // Get clicks with pagination - MUCH better for large datasets
    public function get_clicks($limit = 100, $offset = 0, $search = null) {
        try {
            log_message('debug', "Querying clicks with limit: $limit, offset: $offset");
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('id, pid, link, campaignId, eidt, eid, timestamp');
            $this->db->from('clicks');
            
            // Add search functionality if provided
            if (!empty($search)) {
                $this->db->group_start();
                $this->db->like('link', $search);
                $this->db->or_like('pid', $search);
                $this->db->or_like('campaignId', $search);
                $this->db->group_end();
            }
            
            // Changed from DESC to ASC for ascending order
            $this->db->order_by('timestamp', 'ASC');
            $this->db->limit($limit, $offset);
            
            $query = $this->db->get();
            
            if (!$query) {
                $error = $this->db->error();
                log_message('error', 'Database query failed: ' . json_encode($error));
                throw new Exception('Database query failed: ' . $error['message']);
            }
            
            $result = $query->result_array();
            log_message('debug', 'Found ' . count($result) . ' clicks');

 
            // Clean up data for JSON
            foreach ($result as &$click) {
                $click['campaignId'] = $click['campaignId'] ?? '';
                $click['eidt'] = $click['eidt'] ?? '';
                $click['eid'] = $click['eid'] ?? '';
                $click['link'] = $click['link'] ?? '';
                $click['pid'] = $click['pid'] ?? '';
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_clicks: ' . $e->getMessage());
            throw $e;
        }
    }
    
    // Get total count for pagination
    public function get_clicks_count($search = null) {
        try {
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                $this->db->where("(link LIKE '%$search%' OR pid LIKE '%$search%' OR campaignId LIKE '%$search%' OR id LIKE '%$search%' OR eidt LIKE '%$search%' OR eid LIKE '%$search%' OR timestamp LIKE '%$search%')");
            }
            
            return $this->db->count_all_results();
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_clicks_count: ' . $e->getMessage());
            return 0;
        }
    }
    
    // Get recent clicks only - for dashboard overview
    public function get_recent_clicks($limit = 50) {
        return $this->get_clicks($limit, 0);
    }
    
    // Get all clicks for export (without pagination)
    public function get_all_clicks_for_export($search = null) {
        try {
            log_message('debug', "Exporting all clicks with search: " . ($search ?: 'none'));
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('id, pid, link, campaignId, eidt, eid, timestamp');
            $this->db->from('clicks');
            
            // Add search functionality if provided
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                $this->db->where("(link LIKE '%$search%' OR pid LIKE '%$search%' OR campaignId LIKE '%$search%' OR id LIKE '%$search%' OR eidt LIKE '%$search%' OR eid LIKE '%$search%' OR timestamp LIKE '%$search%')");
            }
            
            // Order by timestamp ascending
            $this->db->order_by('timestamp', 'ASC');
            
            $query = $this->db->get();
            
            if (!$query) {
                $error = $this->db->error();
                log_message('error', 'Database query failed: ' . json_encode($error));
                throw new Exception('Database query failed: ' . $error['message']);
            }
            
            $result = $query->result_array();
            log_message('debug', 'Export query found ' . count($result) . ' clicks');

            // Clean up data for export
            foreach ($result as &$click) {
                $click['campaignId'] = $click['campaignId'] ?? '';
                $click['eidt'] = $click['eidt'] ?? '';
                $click['eid'] = $click['eid'] ?? '';
                $click['link'] = $click['link'] ?? '';
                $click['pid'] = $click['pid'] ?? '';
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_all_clicks_for_export: ' . $e->getMessage());
            throw $e;
        }
    }
 
    // Test method to verify table structure
    public function test_clicks_table() {
        try {
            if (!$this->db->table_exists('clicks')) {
                return array(
                    'success' => false,
                    'message' => 'Clicks table does not exist'
                );
            }
            
            // Test with a small query first
            $this->db->limit(1);
            $query = $this->db->get('clicks');
            $sample = $query->result_array();
            
            // Get table info
            $count_query = $this->db->query("SELECT COUNT(*) as total FROM clicks");
            $total = $count_query->row()->total;
            
            return array(
                'success' => true,
                'message' => 'Clicks table is working',
                'total_records' => $total,
                'sample_record' => $sample
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'message' => 'Error testing clicks table: ' . $e->getMessage()
            );
        }
    }
}