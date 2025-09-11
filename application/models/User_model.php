<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        log_message('debug', 'User_model constructor called');
        if (!$this->db->conn_id) {
            log_message('error', 'Database connection failed');
            throw new Exception('Database connection failed');
        }
    }

    public function get_user_by_email($email) {
        log_message('debug', 'User_model::get_user_by_email called with email: ' . $email);
        try {
            $this->db->where('email', $email);
            $query = $this->db->get('users');
            $result = $query->row_array();
            log_message('debug', 'User query result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'get_user_by_email error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function get_user_by_id($id) {
        log_message('debug', 'User_model::get_user_by_id called with id: ' . $id);
        try {
            $this->db->where('id', $id);
            $query = $this->db->get('users');
            $result = $query->row_array();
            log_message('debug', 'User query result: ' . json_encode($result));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'get_user_by_id error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function signup($data) {
        log_message('debug', 'User_model::signup called with data: ' . json_encode($data));
        try {
            $result = $this->db->insert('users', $data);
            log_message('debug', 'Signup result: ' . ($result ? 'Success' : 'Failed'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'signup error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function update_user($id, $data) {
        log_message('debug', 'User_model::update_user called with id: ' . $id . ', data: ' . json_encode($data));
        try {
            $this->db->where('id', $id);
            $result = $this->db->update('users', $data);
            log_message('debug', 'Update result: ' . ($result ? 'Success' : 'Failed'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'update_user error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function delete_user($id) {
        log_message('debug', 'User_model::delete_user called with id: ' . $id);
        try {
            $this->db->where('id', $id);
            $result = $this->db->delete('users');
            log_message('debug', 'Delete result: ' . ($result ? 'Success' : 'Failed'));
            return $result;
        } catch (Exception $e) {
            log_message('error', 'delete_user error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }

    public function login($email, $password) {
        log_message('debug', 'User_model::login called with email: ' . $email);
        try {
            $user = $this->get_user_by_email($email);
            if ($user) {
                log_message('debug', 'User found: ' . json_encode($user));
                if (password_verify($password, $user['password'])) {
                    log_message('debug', 'Password verified for user: ' . $email);
                    return $user;
                } else {
                    log_message('error', 'Password verification failed for email: ' . $email);
                }
            } else {
                log_message('error', 'No user found with email: ' . $email);
            }
            return false;
        } catch (Exception $e) {
            log_message('error', 'login error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }
}