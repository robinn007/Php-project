<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends CI_Controller {
      public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Clicks_model');
        $this->load->library('form_validation');
        $this->load->driver('cache', array('adapter' => 'file')); // Enable caching
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
                $limit = (int)$this->input->get('limit') ?: 50;
                $search = $this->input->get('search') ?: null;
                $offset = ($page - 1) * $limit;
                $limit = min($limit, 100);
                
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
                
                $clicks = $this->Clicks_model->get_clicks($limit, $offset, $search);
                $total_count = $this->Clicks_model->get_clicks_count($search);
                $total_pages = ceil($total_count / $limit);
                
                log_message('debug', 'Clicks retrieved: ' . count($clicks) . ' out of ' . $total_count . ' total');
                
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
                
                // Cache for 5 minutes
                $this->cache->save($cache_key, $response, 300);
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
                
            } catch (Exception $e) {
                log_message('error', 'Error in clicks method: ' . $e->getMessage());
                log_message('error', 'Stack trace: ' . $e->getTraceAsString());
                
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'Server error: Unable to fetch clicks data.',
                        'debug_info' => $e->getMessage(),
                        'csrf_token' => $this->security->get_csrf_hash()
                    )));
            }
            return;
        }

        $this->load->view('ang/index');
    }
// Add this helper method for testing large datasets
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
        log_message('debug', 'Received manage request with input: ' . file_get_contents('php://input'));
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $json_data = json_decode(file_get_contents('php://input'), true);
        $action = isset($json_data['action']) ? $json_data['action'] : $this->input->post('action');
        $search = $this->input->get('search') ? $this->input->get('search') : (isset($json_data['search']) ? $json_data['search'] : '');
        
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

        if (!$action) {
            log_message('debug', 'No action provided, returning filtered students');
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => true,
                'students' => $this->Student_model->get_students($search, $states),
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $response = array();
        switch ($action) {
            case 'add':
                if (isset($json_data['student'])) {
                    $_POST['student'] = $json_data['student'];
                    $_POST['student[name]'] = isset($json_data['student']['name']) ? $json_data['student']['name'] : '';
                    $_POST['student[email]'] = isset($json_data['student']['email']) ? $json_data['student']['email'] : '';
                    $_POST['student[phone]'] = isset($json_data['student']['phone']) ? $json_data['student']['phone'] : '';
                    $_POST['student[address]'] = isset($json_data['student']['address']) ? $json_data['student']['address'] : '';
                    $_POST['student[state]'] = isset($json_data['student']['state']) ? $json_data['student']['state'] : '';
                }

                $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
                $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('student[phone]', 'Phone', 'required|trim');
                $this->form_validation->set_rules('student[address]', 'Address', 'trim');
                $this->form_validation->set_rules('student[state]', 'State', 'required|trim|in_list[Rajasthan,Delhi,Uttar Pradesh,Punjab,Chandigarh,Himachal Pradesh,Andhra Pradesh,Arunachal Pradesh,Assam,Bihar,Chhattisgarh,Goa,Gujarat,Haryana,Jharkhand,Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Odisha,Andaman and Nicobar Islands,Dadra and Nagar Haveli and Daman and Diu,Jammu and Kashmir,Ladakh,Lakshadweep,Puducherry,Sikkim,Tamil Nadu,Telangana,Tripura,Uttarakhand,West Bengal]');

                log_message('debug', 'POST data for add: ' . json_encode($this->input->post()));

                if ($this->form_validation->run() === FALSE) {
                    $validation_errors = validation_errors();
                    $response = array(
                        'success' => false,
                        'message' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                    log_message('error', 'Validation errors: ' . $validation_errors);
                } else {
                    $data = $this->input->post('student');
                    if (!$data && isset($json_data['student'])) {
                        $data = $json_data['student'];
                    }
                    log_message('debug', 'Student data to add: ' . json_encode($data));

                    if ($this->Student_model->manage_student('add', null, $data)) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student added successfully.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                    } else {
                        $response = array(
                            'success' => false,
                            'message' => 'Failed to add student to database.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                        log_message('error', 'Failed to add student to database.');
                    }
                }
                break;

            case 'edit':
                $id = isset($json_data['id']) ? $json_data['id'] : $this->input->post('id');
                if (isset($json_data['student'])) {
                    $_POST['student'] = $json_data['student'];
                    $_POST['student[name]'] = isset($json_data['student']['name']) ? $json_data['student']['name'] : '';
                    $_POST['student[email]'] = isset($json_data['student']['email']) ? $json_data['student']['email'] : '';
                    $_POST['student[phone]'] = isset($json_data['student']['phone']) ? $json_data['student']['phone'] : '';
                    $_POST['student[address]'] = isset($json_data['student']['address']) ? $json_data['student']['address'] : '';
                    $_POST['student[state]'] = isset($json_data['student']['state']) ? $json_data['student']['state'] : '';
                }

                $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
                $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('student[phone]', 'Phone', 'required|trim');
                $this->form_validation->set_rules('student[address]', 'Address', 'trim');
                $this->form_validation->set_rules('student[state]', 'State', 'required|trim|in_list[Rajasthan,Delhi,Uttar Pradesh,Punjab,Chandigarh,Himachal Pradesh,Andhra Pradesh,Arunachal Pradesh,Assam,Bihar,Chhattisgarh,Goa,Gujarat,Haryana,Jharkhand,Karnataka,Kerala,Madhya Pradesh,Maharashtra,Manipur,Meghalaya,Mizoram,Nagaland,Odisha,Andaman and Nicobar Islands,Dadra and Nagar Haveli and Daman and Diu,Jammu and Kashmir,Ladakh,Lakshadweep,Puducherry,Sikkim,Tamil Nadu,Telangana,Tripura,Uttarakhand,West Bengal]');

                log_message('debug', 'POST data for edit: ' . json_encode($this->input->post()));

                if ($this->form_validation->run() === FALSE) {
                    $validation_errors = validation_errors();
                    $response = array(
                        'success' => false,
                        'message' => $validation_errors ? strip_tags($validation_errors) : 'Validation failed: No specific errors provided.',
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                    log_message('error', 'Validation errors: ' . $validation_errors);
                } else {
                    $data = $this->input->post('student');
                    if (!$data && isset($json_data['student'])) {
                        $data = $json_data['student'];
                    }
                    log_message('debug', 'Student data to edit: ' . json_encode($data));

                    if ($this->Student_model->manage_student('edit', $id, $data)) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student updated successfully.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                    } else {
                        $response = array(
                            'success' => false,
                            'message' => 'Failed to update student.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                        log_message('error', 'Failed to update student in database.');
                    }
                }
                break;

            case 'delete':
                $id = isset($json_data['id']) ? $json_data['id'] : $this->input->post('id');
                if ($this->Student_model->manage_student('delete', $id)) {
                    $response = array(
                        'success' => true,
                        'message' => 'Student deleted successfully.',
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                } else {
                    $response = array(
                        'success' => false,
                        'message' => 'Failed to delete student.',
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                    log_message('error', 'Failed to delete student ID: ' . $id);
                }
                break;

            default:
                $response = array(
                    'success' => false,
                    'message' => 'Invalid action.',
                    'csrf_token' => $this->security->get_csrf_hash()
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

    // export for a  clicks() method
public function export() {
    // Authentication check
    if (!$this->session->userdata('user_id')) {
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode([
                         'success' => false,
                         'message' => 'Please log in to perform this action.'
                     ]));
        return;
    }

    // AJAX check
    if (!$this->input->is_ajax_request()) {
        redirect('/clicks');
        return;
    }

    // Clear any existing output buffers
    while (ob_get_level()) {
        ob_end_clean();
    }

    try {
        // Configuration
        ini_set('max_execution_time', 300); // 5 minutes max
        ini_set('memory_limit', '256M');
        
        $export_type = $this->input->get('export') ?: 'csv';
        $search = $this->input->get('search') ?: null;
        
        log_message('debug', "Export started: type=$export_type, search=" . ($search ?: 'none'));

        // Validate export type - now includes 'xls'
        if (!in_array($export_type, ['csv', 'excel', 'xls'])) {
            throw new Exception('Invalid export format');
        }

        // Get data with reasonable limit
        $limit = 1000; // Start small for testing
        $clicks = $this->Clicks_model->get_clicks($limit, 0, $search);
        
        if (empty($clicks)) {
            $this->output->set_content_type('application/json')
                         ->set_output(json_encode([
                             'success' => false,
                             'message' => 'No data available for export'
                         ]));
            return;
        }

        log_message('debug', "Export: Processing " . count($clicks) . " records");

        if ($export_type === 'csv') {
            $content = $this->generate_csv($clicks);
            $file_data = $content;
        } elseif ($export_type === 'excel') {
            $content = $this->generate_excel($clicks);
            $file_data = base64_encode($content);
        } elseif ($export_type === 'xls') {
            $content = $this->generate_xls($clicks);
            $file_data = base64_encode($content);
        }

        if (empty($content)) {
            throw new Exception('Failed to generate export content');
        }

        $response = [
            'success' => true,
            'file_data' => $file_data,
            'file_type' => $export_type,
            'total_records' => count($clicks),
            'message' => strtoupper($export_type) . ' export completed successfully'
        ];

        log_message('debug', "Export completed: " . strlen($content) . " bytes generated");
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($response));

    } catch (Exception $e) {
        log_message('error', 'Export failed: ' . $e->getMessage());
        
        $this->output->set_content_type('application/json')
                     ->set_output(json_encode([
                         'success' => false,
                         'message' => 'Export failed: ' . $e->getMessage()
                     ]));
    }
}

private function generate_csv($clicks) {
    $output = '';
    
    // Headers
    $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
    $output .= '"' . implode('","', $headers) . '"' . "\n";
    
    // Data rows
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
        
        // Escape CSV fields
        $escaped_row = array_map(function($field) {
            return str_replace('"', '""', $field);
        }, $row);
        
        $output .= '"' . implode('","', $escaped_row) . '"' . "\n";
    }
    
    return $output;
}

private function generate_excel($clicks) {
    // Load library
    $this->load->library('phpexcel');
    
    try {
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Clicks Export');
        
        // Headers
        $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        
        // Data
        $row = 2;
        foreach ($clicks as $click) {
            $sheet->setCellValue('A' . $row, $click['id'] ?? '');
            $sheet->setCellValue('B' . $row, $click['pid'] ?? '');
            $sheet->setCellValue('C' . $row, $click['link'] ?? '');
            $sheet->setCellValue('D' . $row, $click['campaignId'] ?? '');
            $sheet->setCellValue('E' . $row, $click['eidt'] ?? '');
            $sheet->setCellValue('F' . $row, $click['eid'] ?? '');
            $sheet->setCellValue('G' . $row, $click['timestamp'] ?? '');
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Generate file using Excel2007 writer (XLSX format)
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        
        // Use string buffer
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        
        if (empty($content)) {
            throw new Exception('Excel generation produced empty content');
        }
        
        return $content;
        
    } catch (Exception $e) {
        log_message('error', 'Excel generation error: ' . $e->getMessage());
        throw new Exception('Excel generation failed: ' . $e->getMessage());
    }
}

// New method for XLS generation
private function generate_xls($clicks) {
    // Load library
    $this->load->library('phpexcel');
    
    try {
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        
        // Set title
        $sheet->setTitle('Clicks Export');
        
        // Headers
        $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true);
            $col++;
        }
        
        // Data
        $row = 2;
        foreach ($clicks as $click) {
            $sheet->setCellValue('A' . $row, $click['id'] ?? '');
            $sheet->setCellValue('B' . $row, $click['pid'] ?? '');
            $sheet->setCellValue('C' . $row, $click['link'] ?? '');
            $sheet->setCellValue('D' . $row, $click['campaignId'] ?? '');
            $sheet->setCellValue('E' . $row, $click['eidt'] ?? '');
            $sheet->setCellValue('F' . $row, $click['eid'] ?? '');
            $sheet->setCellValue('G' . $row, $click['timestamp'] ?? '');
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', 'G') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        
        // Generate file using Excel5 writer (XLS format - older Excel format)
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        
        // Use string buffer
        ob_start();
        $writer->save('php://output');
        $content = ob_get_contents();
        ob_end_clean();
        
        if (empty($content)) {
            throw new Exception('XLS generation produced empty content');
        }
        
        return $content;
        
    } catch (Exception $e) {
        log_message('error', 'XLS generation error: ' . $e->getMessage());
        throw new Exception('XLS generation failed: ' . $e->getMessage());
    }
}

}