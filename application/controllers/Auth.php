<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('User_model');
        $this->load->helper(array('url', 'form'));
        $this->load->library('form_validation');
        $this->load->library('session');
        log_message('debug', 'Auth controller loaded. Loader available: ' . (is_object($this->load) ? 'Yes' : 'No'));
             log_message('debug', 'User_model available: ' . (isset($this->User_model) ? 'Yes' : 'No'));
    }
    
    public function signup() {
        if ($this->session->userdata('user_id')) {
            redirect('students');
        }
        
        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('confirm_password', 'Confirm Password', 'required|matches[password]');
        
        if ($this->form_validation->run() === FALSE) {
            // die('dsgfdf');
            $this->load->view('auth/signup');
        } else {
            $data = array(
                'username' => $this->input->post('username'),
                'email' => $this->input->post('email'),
                'password' => $this->input->post('password')
            );
            
            if ($this->User_model->signup($data)) {
                $this->session->set_flashdata('success', 'Account created successfully! Please log in.');
                redirect('auth/login');
            } else {
                $data['error'] = 'Failed to create account. Please try again.';
                $this->load->view('auth/signup', $data);
            }
        }
    }
    
    public function login() {
        if ($this->session->userdata('user_id')) {
            redirect('students');
        }
        
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('password', 'Password', 'required');
        
        if ($this->form_validation->run() === FALSE) {
            $this->load->view('auth/login');
        } else {
            $email = $this->input->post('email');
            $password = $this->input->post('password');
            
            $user = $this->User_model->login($email, $password);
            if ($user) {
                $this->session->set_userdata(array(
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email
                ));
                redirect('students');
            } else {
                $data['error'] = 'Invalid email or password.';
                $this->load->view('auth/login', $data);
            }
        }
    }
    
    public function logout() {
        $this->session->unset_userdata(array('user_id', 'username', 'email'));
        $this->session->sess_destroy();
        redirect('auth/login');
    }
}