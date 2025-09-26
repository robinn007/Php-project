<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends CI_Controller {
   public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Clicks_model');
        $this->load->library('form_validation');
        $this->load->driver('cache', array('adapter' => 'file'));
        $this->config->set_item('csrf_protection', FALSE); // Temporary for debugging
    }

    public function index() {
        if ($this->session->userdata('user_id')) {
            if ($this->input->is_ajax_request()) {
                $this->output->set_content_type('application/json');
                echo json_encode(array(
                    'success' => true,
                    'students' => $this->Student_model->get_students(),
                    'csrf_token' => $this->security->get_csrf_hash()
                ));
                exit();
            }
            $this->load->view('ang/index');
        } else {
            redirect('/login');
        }
    }

    public function clicks() {
    if (!$this->session->userdata('user_id')) {
        $this->output->set_content_type('application/json');
        echo json_encode(array(
            'success' => false,
            'message' => 'Please log in to perform this action.',
            'csrf_token' => $this->security->get_csrf_hash()
        ));
        return;
    }

    if ($this->input->is_ajax_request()) {
        try {
            $page = (int)$this->input->get('page') ?: 1;
            $limit = (int)$this->input->get('limit') ?: 50; // Get limit from frontend
            $search = $this->input->get('search') ?: null;
            
            // Validate and sanitize limit to prevent abuse
            $limit = min(max($limit, 1), 500); // Allow 1-500 items per page
            $offset = ($page - 1) * $limit;
            
            // Include limit in cache key so different page sizes don't share cache
            $cache_key = 'clicks_' . md5($page . '_' . $limit . '_' . ($search ?: 'no_search'));
            $cached_data = $this->cache->get($cache_key);
            
            if ($cached_data) {
                log_message('debug', "Serving clicks from cache for key: $cache_key");
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($cached_data));
                return;
            }
            
            log_message('debug', "Fetching clicks - Page: $page, Limit: $limit, Offset: $offset, Search: " . ($search ?: 'none'));
            
            // Fetch clicks and count in a single query
            $result = $this->Clicks_model->get_clicks_with_count($limit, $offset, $search);
            $clicks = $result['clicks'];
            $total_count = $result['total_count'];
            $total_pages = ceil($total_count / $limit);
            
            log_message('debug', 'Clicks retrieved: ' . count($clicks) . ' out of ' . $total_count . ' total, limit was: ' . $limit);

            $response = array(
                'success' => true,
                'clicks' => $clicks,
                'pagination' => array(
                    'current_page' => $page,
                    'total_pages' => $total_pages,
                    'total_count' => $total_count,
                    'per_page' => $limit,
                    'has_next' => $page < $total_pages,
                    'has_prev' => $page > 1
                ),
                'csrf_token' => $this->security->get_csrf_hash()
            );
            
            // Cache for 10 minutes
            $this->cache->save($cache_key, $response, 600);
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
            
        } catch (Exception $e) {
            log_message('error', 'Error in clicks method: ' . $e->getMessage());
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Server error: Unable to fetch clicks data.',
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
        }
        return;
    }

    $this->load->view('ang/index');
}

    public function test_clicks() {
        if (!$this->session->userdata('user_id')) {
            redirect('/login');
            return;
        }
        
        $result = $this->Clicks_model->test_clicks_table();
        $this->output->set_content_type('application/json');
        echo json_encode($result);
    }

    public function manage() {
        log_message('debug', 'Received manage request with POST data: ' . json_encode($this->input->post()));

        // Check if user is logged in
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'flashMessage' => 'Please log in to perform this action.',
                'flashType' => 'error'
            ));
            exit();
        }

        // Parse input data
        $json_data = json_decode(file_get_contents('php://input'), true);
        $action = $this->input->post('action') ?: (isset($json_data['action']) ? $json_data['action'] : null);
        $search = $this->input->get('search') ?: (isset($json_data['search']) ? $json_data['search'] : '');
        $states = array();
        if ($this->input->get('states')) {
            $states_param = $this->input->get('states');
            if (is_string($states_param)) {
                $decoded_states = json_decode($states_param, true);
                $states = is_array($decoded_states) ? $decoded_states : array();
            }
        } elseif (isset($json_data['states'])) {
            $states = is_array($json_data['states']) ? $json_data['states'] : array();
        }

        log_message('debug', 'Action received in manage: ' . $action . ', Search: ' . $search . ', States: ' . json_encode($states));

        // Handle no action (return filtered students)
        if (!$action) {
            log_message('debug', 'No action provided, returning filtered students');
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'students' => $this->Student_model->get_students($search, $states)
            ));
            exit();
        }

        $response = array();
        switch ($action) {
            // Case for adding a new student
            case 'add':
                // Validate required fields
                if (!$this->input->post('name') || !$this->input->post('email') || !$this->input->post('state')) {
                    $response = array(
                        'success' => false,
                        'message' => 'Missing required student data (name, email, or state).',
                        'flashMessage' => 'Missing required student data (name, email, or state).',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Missing required student data for add: ' . json_encode($this->input->post()));
                    $this->output->set_content_type('application/json');
                    echo json_encode($response);
                    exit();
                }

                // Set form validation rules for add
                $this->form_validation->set_rules('name', 'Name', 'required|trim');
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('phone', 'Phone', 'required|trim');
                $this->form_validation->set_rules('address', 'Address', 'trim');
                $this->form_validation->set_rules('state', 'State', 'required|trim|in_list[Rajasthan,Delhi,Uttar Pradesh,Punjab,Chandigarh,Himachal Pradesh,Andhra Pradesh,Arunachal Pradesh,Assam,Bihar,Chhattisgarh,Goa,Gujarat,Haryana,Jharkhand,Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Odisha,Andaman and Nicobar Islands,Dadra and Nagar Haveli and Daman and Diu,Jammu and Kashmir,Ladakh,Lakshadweep,Puducherry,Sikkim,Tamil Nadu,Telangana,Tripura,Uttarakhand,West Bengal]');

                log_message('debug', 'POST data for add: ' . json_encode($this->input->post()));

                if ($this->form_validation->run() === FALSE) {
                    $validation_errors = validation_errors();
                    $response = array(
                        'success' => false,
                        'message' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'flashMessage' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Validation errors for add: ' . $validation_errors);
                } else {
                    // Prepare student data for add
                    $student_data = array(
                        'name' => $this->input->post('name'),
                        'email' => $this->input->post('email'),
                        'phone' => $this->input->post('phone'),
                        'address' => $this->input->post('address'),
                        'state' => $this->input->post('state') ?: 'Rajasthan',
                        'created_at' => date('Y-m-d H:i:s') // Ensure created_at is set
                    );

                    log_message('debug', 'Student data to add: ' . json_encode($student_data));

                    $result = $this->Student_model->manage_student('add', null, $student_data);
                    if ($result) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student added successfully.',
                            'flashMessage' => 'Student added successfully.',
                            'flashType' => 'success'
                        );
                    } else {
                        $db_error = $this->db->error();
                        $error_message = 'Failed to add student: ' . ($db_error['message'] ?: 'Unknown database error');
                        $response = array(
                            'success' => false,
                            'message' => $error_message,
                            'flashMessage' => $error_message,
                            'flashType' => 'error'
                        );
                        log_message('error', 'Failed to add student: ' . $error_message);
                    }
                }
                break;

            // Case for editing an existing student
            case 'edit':
                // Validate required fields and ID
                $id = $this->input->post('id') ?: (isset($json_data['id']) ? $json_data['id'] : null);
                if (!$id || !is_numeric($id)) {
                    $response = array(
                        'success' => false,
                        'message' => 'Invalid or missing student ID for edit.',
                        'flashMessage' => 'Invalid or missing student ID for edit.',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Invalid or missing student ID for edit: ' . $id);
                    $this->output->set_content_type('application/json');
                    echo json_encode($response);
                    exit();
                }

                if (!$this->input->post('name') || !$this->input->post('email') || !$this->input->post('state')) {
                    $response = array(
                        'success' => false,
                        'message' => 'Missing required student data (name, email, or state).',
                        'flashMessage' => 'Missing required student data (name, email, or state).',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Missing required student data for edit: ' . json_encode($this->input->post()));
                    $this->output->set_content_type('application/json');
                    echo json_encode($response);
                    exit();
                }

                // Set form validation rules for edit
                $this->form_validation->set_rules('name', 'Name', 'required|trim');
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('phone', 'Phone', 'required|trim');
                $this->form_validation->set_rules('address', 'Address', 'trim');
                $this->form_validation->set_rules('state', 'State', 'required|trim|in_list[Rajasthan,Delhi,Uttar Pradesh,Punjab,Chandigarh,Himachal Pradesh,Andhra Pradesh,Arunachal Pradesh,Assam,Bihar,Chhattisgarh,Goa,Gujarat,Haryana,Jharkhand,Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Odisha,Andaman and Nicobar Islands,Dadra and Nagar Haveli and Daman and Diu,Jammu and Kashmir,Ladakh,Lakshadweep,Puducherry,Sikkim,Tamil Nadu,Telangana,Tripura,Uttarakhand,West Bengal]');

                log_message('debug', 'POST data for edit (ID: ' . $id . '): ' . json_encode($this->input->post()));

                if ($this->form_validation->run() === FALSE) {
                    $validation_errors = validation_errors();
                    $response = array(
                        'success' => false,
                        'message' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'flashMessage' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Validation errors for edit: ' . $validation_errors);
                } else {
                    // Prepare student data for edit
                    $student_data = array(
                        'name' => $this->input->post('name'),
                        'email' => $this->input->post('email'),
                        'phone' => $this->input->post('phone'),
                        'address' => $this->input->post('address'),
                        'state' => $this->input->post('state') ?: 'Rajasthan'
                    );

                    log_message('debug', 'Student data to edit (ID: ' . $id . '): ' . json_encode($student_data));

                    $result = $this->Student_model->manage_student('edit', $id, $student_data);
                    if ($result) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student updated successfully.',
                            'flashMessage' => 'Student updated successfully.',
                            'flashType' => 'success'
                        );
                    } else {
                        $db_error = $this->db->error();
                        $error_message = 'Failed to update student: ' . ($db_error['message'] ?: 'Unknown database error');
                        $response = array(
                            'success' => false,
                            'message' => $error_message,
                            'flashMessage' => $error_message,
                            'flashType' => 'error'
                        );
                        log_message('error', 'Failed to update student ID: ' . $id . ': ' . $error_message);
                    }
                }
                break;

            // Case for deleting a student
            case 'delete':
                $id = $this->input->post('id') ?: (isset($json_data['id']) ? $json_data['id'] : null);
                if (!$id || !is_numeric($id)) {
                    $response = array(
                        'success' => false,
                        'message' => 'Invalid or missing student ID for delete.',
                        'flashMessage' => 'Invalid or missing student ID for delete.',
                        'flashType' => 'error'
                    );
                    log_message('error', 'Invalid or missing student ID for delete: ' . $id);
                } else {
                    $result = $this->Student_model->manage_student('delete', $id);
                    if ($result) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student deleted successfully.',
                            'flashMessage' => 'Student deleted successfully.',
                            'flashType' => 'success'
                        );
                    } else {
                        $db_error = $this->db->error();
                        $error_message = 'Failed to delete student: ' . ($db_error['message'] ?: 'Unknown database error');
                        $response = array(
                            'success' => false,
                            'message' => $error_message,
                            'flashMessage' => $error_message,
                            'flashType' => 'error'
                        );
                        log_message('error', 'Failed to delete student ID: ' . $id . ': ' . $error_message);
                    }
                }
                break;

            // Default case for invalid actions
            default:
                $response = array(
                    'success' => false,
                    'message' => 'Invalid action.',
                    'flashMessage' => 'Invalid action.',
                    'flashType' => 'error'
                );
                log_message('error', 'Invalid action received: ' . $action);
                break;
        }

        $this->output->set_content_type('application/json');
        echo json_encode($response);
        exit();
    }

    public function edit($id) {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $student = $this->Student_model->get_student($id);
        if ($student) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'student' => $student,
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        } else {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Student not found.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function get($id = null) {
        log_message('debug', 'Received GET request for student ID: ' . $id);
        if (!$this->session->userdata('user_id')) {
            log_message('error', 'Unauthorized access attempt to get student ID: ' . $id);
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if (!$id || !is_numeric($id)) {
            log_message('error', 'Invalid or missing student ID in get request: ' . $id);
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Invalid or missing student ID.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $student = $this->Student_model->get_student($id);
        if ($student) {
            log_message('debug', 'Student found for ID: ' . $id . ': ' . json_encode($student));
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'student' => $student,
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        } else {
            log_message('error', 'Student not found or deleted for ID: ' . $id);
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Student not found or has been deleted.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
    }

    public function deleted() {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $this->output->set_content_type('application/json');
        echo json_encode(array(
            'success' => true,
            'students' => $this->Student_model->get_deleted_students(),
            'csrf_token' => $this->security->get_csrf_hash()
        ));
        exit();
    }

    public function restore($id) {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if ($this->Student_model->restore_student($id)) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'message' => 'Student restored successfully.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        } else {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Failed to restore student.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function permanent_delete($id) {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        if ($this->Student_model->permanent_delete($id)) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'message' => 'Student permanently deleted successfully.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        } else {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Failed to delete student permanently.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
        }
        exit();
    }

    public function setup_database() {
        $this->load->dbforge();

        if (!$this->db->table_exists('students')) {
            $fields = array(
                'id' => array('type' => 'INT', 'auto_increment' => TRUE),
                'name' => array('type' => 'VARCHAR', 'constraint' => '100'),
                'email' => array('type' => 'VARCHAR', 'constraint' => '100'),
                'phone' => array('type' => 'VARCHAR', 'constraint' => '20', 'null' => TRUE),
                'address' => array('type' => 'TEXT', 'null' => TRUE),
                'state' => array('type' => 'VARCHAR', 'constraint' => '100', 'null' => TRUE, 'default' => 'Rajasthan'),
                'is_deleted' => array('type' => 'TINYINT', 'default' => 0)
            );
            $this->dbforge->add_field($fields);
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->create_table('students');

            $sample_data = array(
                array('name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890', 'address' => '123 Main St', 'state' => 'Rajasthan'),
                array('name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '0987654321', 'address' => '456 Oak Ave', 'state' => 'Delhi'),
                array('name' => 'Mike Johnson', 'email' => 'mike@example.com', 'phone' => '5551234567', 'address' => '789 Pine Rd', 'state' => 'Uttar Pradesh'),
                array('name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'phone' => '4449876543', 'address' => '321 Elm St', 'state' => 'Punjab'),
                array('name' => 'David Brown', 'email' => 'david@example.com', 'phone' => '7776543210', 'address' => '654 Birch Ln', 'state' => 'Chandigarh')
            );
            $this->db->insert_batch('students', $sample_data);
        }

        $this->load->view('ang/setup_database');
    }
    
    public function test_db() {
        $output = $this->Student_model->test_database();
        if ($this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'message' => $output,
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }
        echo $output;
    }

    public function add_created_at_field() {
        $this->load->dbforge();
        
        $query = $this->db->query("SHOW COLUMNS FROM students LIKE 'created_at'");
        
        if ($query->num_rows() == 0) {
            $fields = array(
                'created_at' => array(
                    'type' => 'TIMESTAMP',
                    'default' => 'CURRENT_TIMESTAMP'
                )
            );
            
            $this->dbforge->add_column('students', $fields);
            
            $this->db->query("UPDATE students SET created_at = NOW() WHERE created_at IS NULL");
            
            echo "created_at field added successfully to students table";
        } else {
            echo "created_at field already exists in students table";
        }
    }

    public function update_existing_states() {
        $this->db->where('state IS NULL');
        $this->db->update('students', array('state' => 'Rajasthan'));
        echo 'Existing student records updated with default state: Rajasthan';
    }

    public function export() {
    if (!$this->session->userdata('user_id')) {
        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode([
                 'success' => false,
                 'message' => 'Please log in to perform this action.'
             ]));
        return;
    }

    if (!$this->input->is_ajax_request()) {
        redirect('/clicks');
        return;
    }

    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            log_message('error', 'Fatal error during export: ' . $error['message']);
            $this->output
                 ->set_content_type('application/json')
                 ->set_output(json_encode([
                     'success' => false,
                     'message' => 'Export failed: Fatal server error - ' . $error['message']
                 ]));
        }
    });

    while (ob_get_level()) {
        ob_end_clean();
    }

    try {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M'); // Increased for 10,000 records
        
        $export_type = $this->input->get('export') ?: 'csv';
        $search = $this->input->get('search') ?: null;
        
        log_message('debug', "Export started: type=$export_type, search=" . ($search ?: 'none'));

        if (!in_array($export_type, ['csv', 'xlsx', 'xls'])) {
            throw new Exception('Invalid export format: Only CSV, XLS, and XLSX are supported');
        }

        $limit = 100000; // Updated to 10,000 as per requirement
        $clicks = $this->Clicks_model->get_all_clicks_for_export($search, $limit);
        
        if (empty($clicks)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode([
                             'success' => false,
                             'message' => 'No data available for export'
                         ]));
            return;
        }

        log_message('debug', "Export: Processing " . count($clicks) . " records");

        $content = '';
        $file_data = '';
        
        if ($export_type === 'xlsx') {
            $this->load->library('phpexcel');
            $content = $this->phpexcel->generate_excel($clicks);
            $file_data = base64_encode($content);
        } else if ($export_type === 'xls') {
            $content = $this->generate_xls($clicks);
            $file_data = base64_encode($content);
        } else {
            $content = $this->generate_csv($clicks);
            $file_data = $content;
        }

        if (empty($content)) {
            throw new Exception("Failed to generate {$export_type} content");
        }

        $response = [
            'success' => true,
            'file_data' => $file_data,
            'file_type' => $export_type,
            'total_records' => count($clicks),
            'message' => strtoupper($export_type) . ' export completed successfully',
            'csrf_token' => $this->security->get_csrf_hash()
        ];

        log_message('debug', "Export completed: " . strlen($content) . " bytes generated");
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($response));

    } catch (Exception $e) {
        log_message('error', 'Export failed: ' . $e->getMessage());
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode([
                         'success' => false,
                         'message' => 'Export failed: ' . $e->getMessage(),
                         'csrf_token' => $this->security->get_csrf_hash()
                     ]));
    }
}

private function generate_csv($clicks) {
    $output = '';
    
    $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
    $output .= '"' . implode('","', $headers) . '"' . "\n";
    
    foreach ($clicks as $click) {
        $row = [
            $click['id'] ?? '',
            $click['pid'] ?? '',
            $click['link'] ?? '',
            $click['campaignId'] ?? '',
            $click['eidt'] ?? '',
            $click['eid'] ?? '',
            $click['timestamp'] ?? ''
        ];
        
        $escaped_row = array_map(function($field) {
            return str_replace('"', '""', (string)$field);
        }, $row);
        
        $output .= '"' . implode('","', $escaped_row) . '"' . "\n";
    }
    
    return $output;
}

private function generate_xls($clicks) {
    // Create a simple XLS file using binary format
    $output = '';
    
    // XLS file header (simplified BIFF5 format)
    $output .= pack("ssssss", 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
    
    // Headers
    $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
    $row_num = 0;
    
    foreach ($headers as $col_num => $header) {
        $output .= $this->write_string($row_num, $col_num, $header);
    }
    $row_num++;
    
    // Data rows
    foreach ($clicks as $click) {
        $col_num = 0;
        
        // Write each cell
        $output .= $this->write_string($row_num, $col_num++, $click['id'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['pid'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['link'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['campaignId'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['eidt'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['eid'] ?? '');
        $output .= $this->write_string($row_num, $col_num++, $click['timestamp'] ?? '');
        
        $row_num++;
    }
    
    // XLS file footer
    $output .= pack("ss", 0x0A, 0x00);
    
    return $output;
}

private function write_string($row, $col, $value) {
    $value = (string)$value;
    $length = strlen($value);
    
    // BIFF5 LABEL record: 0x204
    return pack("ssssss", 0x204, 8 + $length, $row, $col, 0x0, $length) . $value;
}
}

