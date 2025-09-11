<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Setup extends CI_Controller {
    public function index() {
        $this->load->view('ang/index');
    }

    public function database() {
        $this->load->view('ang/setup_database');
    }
}