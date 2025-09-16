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

    public function get_clicks($limit = 50, $offset = 0, $search = null) {
        try {
            log_message('debug', "Querying clicks with limit: $limit, offset: $offset, search: " . ($search ?: 'none'));
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('id, pid, link, campaignId, eidt, eid, timestamp');
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                // Prefer full-text search if index exists
                if ($this->db->field_exists('link', 'clicks') && $this->db->index_exists('idx_clicks_link_fulltext', 'clicks')) {
                    $this->db->where("MATCH(link) AGAINST(? IN BOOLEAN MODE)", $search);
                } else {
                    $this->db->group_start();
                    $this->db->like('link', $search, 'after'); // Search from start to leverage index
                    $this->db->or_like('pid', $search, 'after');
                    $this->db->or_like('campaignId', $search, 'after');
                    $this->db->group_end();
                }
            }
            
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

    public function get_clicks_count($search = null) {
        try {
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                if ($this->db->field_exists('link', 'clicks') && $this->db->index_exists('idx_clicks_link_fulltext', 'clicks')) {
                    $this->db->where("MATCH(link) AGAINST(? IN BOOLEAN MODE)", $search);
                } else {
                    $this->db->where("(link LIKE '$search%' OR pid LIKE '$search%' OR campaignId LIKE '$search%')");
                }
            }
            
            return $this->db->count_all_results();
            
        } catch (Exception $e) {
            log_message('error', 'Error in get_clicks_count: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_all_clicks_for_export($search = null) {
        try {
            log_message('debug', "Exporting all clicks with search: " . ($search ?: 'none'));
            
            if (!$this->db->table_exists('clicks')) {
                log_message('error', 'Clicks table does not exist');
                throw new Exception('Clicks table does not exist');
            }
            
            $this->db->select('id, pid, link, campaignId, eidt, eid, timestamp');
            $this->db->from('clicks');
            
            if (!empty($search)) {
                $search = $this->db->escape_like_str($search);
                if ($this->db->field_exists('link', 'clicks') && $this->db->index_exists('idx_clicks_link_fulltext', 'clicks')) {
                    $this->db->where("MATCH(link) AGAINST(? IN BOOLEAN MODE)", $search);
                } else {
                    $this->db->where("(link LIKE '$search%' OR pid LIKE '$search%' OR campaignId LIKE '$search%')");
                }
            }
            
            $this->db->order_by('timestamp', 'ASC');
            $this->db->limit(200000); // Prevent memory issues
            
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