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

    public function get_clicks_with_count($limit = 50, $offset = 0, $search = null) {
        try {
            log_message('debug', "Querying clicks with limit: $limit, offset: $offset, search: " . ($search ?: 'none'));
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('SQL_CALC_FOUND_ROWS id, pid, link, campaignId, eidt, eid, timestamp', FALSE);
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                // Added 'link' field to the search query
                $where = "(id LIKE '$search%' OR pid LIKE '$search%' OR campaignId LIKE '$search%' OR eidt LIKE '$search%' OR eid LIKE '$search%' OR link LIKE '%$search%')";
                $this->db->where($where);
            }
            
            $this->db->order_by('timestamp', 'ASC');
            $this->db->limit($limit, $offset);
            
            $query = $this->db->get();
            
            if (!$query) {
                $error = $this->db->error();
                log_message('error', 'Database query failed: ' . json_encode($error));
                throw new Exception('Database query failed: ' . $error['message']);
            }
            
            $clicks = $query->result_array();
            
            // Get total count from FOUND_ROWS()
            $count_query = $this->db->query('SELECT FOUND_ROWS() as total');
            $total_count = $count_query->row()->total;
            
            log_message('debug', 'Found ' . count($clicks) . ' clicks, total count: ' . $total_count);
            
            foreach ($clicks as &$click) {
                $click['campaignId'] = $click['campaignId'] ?? '';
                $click['eidt'] = $click['eidt'] ?? '';
                $click['eid'] = $click['eid'] ?? '';
                $click['link'] = $click['link'] ?? '';
                $click['pid'] = $click['pid'] ?? '';
            }
            
            return [
                'clicks' => $clicks,
                'total_count' => $total_count
            ];
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_clicks_with_count: ' . $e->getMessage());
            throw $e;
        }
    }

    public function get_all_clicks_for_export($search = null, $limit = 100000) {
        try {
            log_message('debug', "Exporting up to $limit clicks with search: " . ($search ?: 'none'));
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('id, pid, link, campaignId, eidt, eid, timestamp');
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                // Added 'link' field to the export search query as well
                $where = "(id LIKE '$search%' OR pid LIKE '$search%' OR campaignId LIKE '$search%' OR eidt LIKE '$search%' OR eid LIKE '$search%' OR link LIKE '%$search%')";
                $this->db->where($where);
            }
            
            $this->db->order_by('timestamp', 'ASC');
            $this->db->limit($limit);
            
            $query = $this->db->get();
            
            if (!$query) {
                $error = $this->db->error();
                log_message('error', 'Database query failed: ' . json_encode($error));
                throw new Exception('Database query failed: ' . $error['message']);
            }
            
            $result = $query->result_array();
            log_message('debug', 'Export query found ' . count($result) . ' clicks');
            
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

    public function test_clicks_table() {
        try {
            if (!$this->db->table_exists('clicks')) {
                return array(
                    'success' => false,
                    'message' => 'Clicks table does not exist'
                );
            }
            
            $this->db->limit(1);
            $query = $this->db->get('clicks');
            $sample = $query->result_array();
            
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