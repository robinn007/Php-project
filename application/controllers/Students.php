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
}