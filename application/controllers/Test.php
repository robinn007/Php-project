<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->output->set_content_type('application/json');
    }

    public function index() {
        echo json_encode(array(
            'success' => true,
            'message' => 'Test endpoint working',
            'php_version' => phpversion(),
            'ci_version' => CI_VERSION,
            'timestamp' => date('Y-m-d H:i:s')
        ));
    }

    public function post_test() {
        // For CodeIgniter 2, use php://input instead of raw_input_stream
        $raw_input = file_get_contents('php://input');
        $method = $this->input->server('REQUEST_METHOD');
        $post_data = $this->input->post();
        
        echo json_encode(array(
            'success' => true,
            'message' => 'POST test endpoint working',
            'method' => $method,
            'raw_input' => $raw_input,
            'post_data' => $post_data,
            'json_decode' => json_decode($raw_input, true)
        ));
    }
}