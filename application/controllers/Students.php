<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->model('Clicks_model');
        $this->load->library('form_validation');
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
    // Check authentication
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
            // Get pagination parameters
            $page = (int)$this->input->get('page') ?: 1;
            $limit = (int)$this->input->get('limit') ?: 100; // Default 100 records per page
            $search = $this->input->get('search') ?: null;
            
            // Calculate offset
            $offset = ($page - 1) * $limit;
            
            // Validate limits to prevent abuse
            $limit = min($limit, 500); // Max 500 records per request
            
            log_message('debug', "Fetching clicks - Page: $page, Limit: $limit, Offset: $offset");
            
            // Get clicks data with pagination
            $clicks = $this->Clicks_model->get_clicks($limit, $offset, $search);
            
            // Get total count for pagination info
            $total_count = $this->Clicks_model->get_clicks_count($search);
            $total_pages = ceil($total_count / $limit);
            
            log_message('debug', 'Clicks retrieved: ' . count($clicks) . ' out of ' . $total_count . ' total');
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
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
                )));
            
        } catch (Exception $e) {
            log_message('error', 'Error in clicks method: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Server error: ' . $e->getMessage(),
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
        }
        return;
    }

    // For non-AJAX requests, load the main view
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
    // Check authentication
    if (!$this->session->userdata('user_id')) {
        log_message('error', 'Export: User not authenticated');
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
            $export_type = $this->input->get('export') ?: 'csv';
            $search = $this->input->get('search') ?: null;
            
            log_message('debug', "Export: Starting export - Type: $export_type, Search: " . ($search ?: 'none'));
            
            if ($export_type !== 'csv') {
                throw new Exception('Only CSV export is currently supported');
            }
            
            // First, let's try the regular get_clicks method to see if data exists
            log_message('debug', 'Export: Testing regular clicks query first');
            $test_clicks = $this->Clicks_model->get_clicks(10, 0, $search);
            log_message('debug', 'Export: Test query returned ' . count($test_clicks) . ' records');
            
            if (empty($test_clicks)) {
                log_message('error', 'Export: No data found even in test query');
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'No clicks data available for export. Please check if clicks table has data.',
                        'debug_info' => 'Test query returned 0 records',
                        'csrf_token' => $this->security->get_csrf_hash()
                    )));
                return;
            }
            
            // Now try the export method
            log_message('debug', 'Export: Calling get_all_clicks_for_export');
            $clicks = $this->Clicks_model->get_all_clicks_for_export($search);
            log_message('debug', 'Export: get_all_clicks_for_export returned ' . count($clicks) . ' records');
            
            if (empty($clicks)) {
                log_message('error', 'Export: get_all_clicks_for_export returned empty but test query had data');
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(array(
                        'success' => false,
                        'message' => 'Export method failed to retrieve data, but data exists. Check get_all_clicks_for_export method.',
                        'debug_info' => 'Export query failed, but test query returned ' . count($test_clicks) . ' records',
                        'csrf_token' => $this->security->get_csrf_hash()
                    )));
                return;
            }
            
            // Generate CSV content
            log_message('debug', 'Export: Generating CSV');
            $csv_content = $this->generate_csv($clicks);
            
            if (empty($csv_content)) {
                throw new Exception('Failed to generate CSV content');
            }
            
            log_message('debug', 'Export: CSV generated successfully with ' . count($clicks) . ' records');
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => true,
                    'csv_data' => $csv_content,
                    'total_records' => count($clicks),
                    'message' => 'Export generated successfully',
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
            
        } catch (Exception $e) {
            log_message('error', 'Error in export method: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(array(
                    'success' => false,
                    'message' => 'Export failed: ' . $e->getMessage(),
                    'debug_info' => 'Check application logs for details',
                    'csrf_token' => $this->security->get_csrf_hash()
                )));
        }
        return;
    }
    
    // For non-AJAX requests, redirect to clicks page
    redirect('/clicks');
}
    
    // Helper method to generate CSV content
    private function generate_csv($data) {
        if (empty($data)) {
            return '';
        }
        
        // CSV headers
        $headers = array('ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp');
        
        // Start building CSV content
        $csv_content = '';
        
        // Add headers
        $csv_content .= '"' . implode('","', $headers) . '"' . "\n";
        
        // Add data rows
        foreach ($data as $row) {
            $csv_row = array(
                $this->escape_csv_field($row['id']),
                $this->escape_csv_field($row['pid']),
                $this->escape_csv_field($row['link']),
                $this->escape_csv_field($row['campaignId']),
                $this->escape_csv_field($row['eidt']),
                $this->escape_csv_field($row['eid']),
                $this->escape_csv_field($row['timestamp'])
            );
            
            $csv_content .= '"' . implode('","', $csv_row) . '"' . "\n";
        }
        
        return $csv_content;
    }
    
    // Helper method to properly escape CSV fields
    private function escape_csv_field($field) {
        // Handle null/empty values
        if ($field === null || $field === '') {
            return '';
        }
        
        // Convert to string and escape quotes
        $field = (string)$field;
        $field = str_replace('"', '""', $field);
        
        return $field;
    }
}