<?php
class User_model extends CI_Model {
    
    public function __construct() {
        parent::__construct();
      //  $this->load->database(); 
        if (!$this->db->conn_id) {
            log_message('error', 'Database connection failed in User_model');
            show_error('Database connection failed. Please check your database configuration.');
        }
    }
    
    public function signup($data) {
        try {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            return $this->db->insert('users', $data);
        } catch (Exception $e) {
            log_message('error', 'Exception in signup(): ' . $e->getMessage());
            return FALSE;
        }
    }
    
    public function login($email, $password) {
        try {
            $query = $this->db->get_where('users', array('email' => $email));
            if ($query && $query->num_rows() > 0) {
                $user = $query->row();
                if (password_verify($password, $user->password)) {
                    return $user;
                }
            }
            return FALSE;
        } catch (Exception $e) {
            log_message('error', 'Exception in login(): ' . $e->getMessage());
            return FALSE;
        }
    }
}