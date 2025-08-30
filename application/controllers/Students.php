<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('student_model');
        $this->load->helper(array('url', 'form'));
        $this->load->library('form_validation');
        
        // Check if user is logged in, except for test_db and setup_database
        if (!$this->session->userdata('user_id') && !in_array($this->router->method, array('test_db', 'setup_database'))) {
            log_message('debug', 'Redirecting to login: No user_id in session');
            redirect('auth/login');
        }
    }
    
    public function index() {
        $data['students'] = $this->student_model->get_students();
        log_message('debug', 'Index: Retrieved ' . count($data['students']) . ' students');
        $this->load->view('students/index', $data);
    }

    public function dashboard() {
        $data['students'] = $this->student_model->get_students();
        log_message('debug', 'Dashboard: Retrieved ' . count($data['students']) . ' students');
        $this->load->view('students/index', $data);
    }
    
   public function manage($action = 'add', $id = null) {
    ob_start();
    
    $this->form_validation->set_rules('name', 'Name', 'required|trim');
    $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
    $this->form_validation->set_rules('phone', 'Phone', 'trim');
    
    if ($this->input->is_ajax_request()) {
        $action = $this->input->post('action') ?: $action;
        $id = $this->input->post('id') ?: $id;
        
        log_message('debug', "Manage AJAX request: action=$action, id=$id, post_data=" . json_encode($this->input->post()));
        
        $response = array('success' => false, 'message' => '', 'data' => array());
        
        try {
            switch ($action) {
                case 'add':
                    if ($this->form_validation->run() === FALSE) {
                        $response['message'] = validation_errors();
                    } else {
                        $data = array(
                            'name' => $this->input->post('name'),
                            'email' => $this->input->post('email'),
                            'phone' => $this->input->post('phone') ?: null,
                            'address' => $this->input->post('address') ?: null
                        );
                        if ($this->student_model->manage_student('add', null, $data)) {
                            $response['success'] = true;
                            $response['message'] = 'Student added successfully.';
                        } else {
                            $response['message'] = 'Failed to add student.';
                            log_message('error', 'Add operation failed: ' . $this->db->last_query());
                        }
                    }
                    break;
                    
                case 'edit':
                    if (!$id || !$this->student_model->get_student($id)) {
                        $response['message'] = 'Invalid student ID.';
                    } elseif ($this->form_validation->run() === FALSE) {
                        $response['message'] = validation_errors();
                    } else {
                        $data = array(
                            'name' => $this->input->post('name'),
                            'email' => $this->input->post('email'),
                            'phone' => $this->input->post('phone') ?: null,
                            'address' => $this->input->post('address') ?: null
                        );
                        if ($this->student_model->manage_student('edit', $id, $data)) {
                            $response['success'] = true;
                            $response['message'] = 'Student updated successfully.';
                        } else {
                            $response['message'] = 'Failed to update student.';
                            log_message('error', 'Edit operation failed: ' . $this->db->last_query());
                        }
                    }
                    break;
                    
                case 'delete':
                    if (!$id || !$this->student_model->get_student($id)) {
                        $response['message'] = 'Invalid student ID.';
                    } else {
                        if ($this->student_model->manage_student('delete', $id)) {
                            $response['success'] = true;
                            $response['message'] = 'Student deleted successfully.';
                        } else {
                            $response['message'] = 'Failed to delete student.';
                            log_message('error', 'Delete operation failed: ' . $this->db->last_query());
                        }
                    }
                    break;
                    
                default:
                    $response['message'] = 'Invalid action.';
            }
            
            $response['csrf_token'] = $this->security->get_csrf_hash();
            
            $buffered_output = ob_get_clean();
            if ($buffered_output) {
                log_message('error', 'Unexpected output in manage method: ' . $buffered_output);
            }
            
            $this->output
                ->set_status_header(200)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        } catch (Exception $e) {
            $response['message'] = 'Server error: ' . $e->getMessage();
            log_message('error', "Exception in manage action=$action, id=$id: " . $e->getMessage());
            $buffered_output = ob_get_clean();
            if ($buffered_output) {
                log_message('error', 'Unexpected output in manage method: ' . $buffered_output);
            }
            $this->output
                ->set_status_header(500)
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    } else {
       try {
            if ($this->input->post()) {
                if ($this->form_validation->run() === FALSE) {
                    $data['error'] = validation_errors();
                } else {
                    $data = array(
                        'name' => $this->input->post('name'),
                        'email' => $this->input->post('email'),
                        'phone' => $this->input->post('phone') ?: null,
                        'address' => $this->input->post('address') ?: null
                    );
                    if ($action === 'add' && $this->student_model->manage_student('add', null, $data)) {
                        redirect('students');
                    } elseif ($action === 'edit' && $id && $this->student_model->manage_student('edit', $id, $data)) {
                        redirect('students');
                    } elseif ($action === 'delete' && $id && $this->student_model->manage_student('delete', $id)) {
                        redirect('students');
                    } else {
                        $data['error'] = 'Operation failed.';
                        log_message('error', "Non-AJAX operation failed: action=$action, id=$id, query=" . $this->db->last_query());
                    }
                }
            }
            
            if ($action === 'edit' && $id) {
                $data['student'] = $this->student_model->get_student($id);
                if (!$data['student']) {
                    log_message('error', "Student not found for edit: id=$id");
                    show_404();
                }
                $data['action'] = 'edit';
                $data['id'] = $id;
            } else {
                $data['action'] = 'add';
                $data['student'] = (object) array('id' => '', 'name' => '', 'email' => '', 'phone' => '', 'address' => '');
            }
            
            $buffered_output = ob_get_clean();
            if ($buffered_output) {
                log_message('error', 'Unexpected output in manage method (non-AJAX): ' . $buffered_output);
            }
            
            $this->load->view('students/manage', $data);
        } catch (Exception $e) {
            log_message('error', "Exception in manage (non-AJAX) action=$action, id=$id: " . $e->getMessage());
            $buffered_output = ob_get_clean();
            if ($buffered_output) {
                log_message('error', 'Unexpected output in manage method (non-AJAX): ' . $buffered_output);
            }
            show_error('Server error: ' . $e->getMessage(), 500);
        }
    }
}

    public function test_db() {
        $this->load->database();
        if ($this->db->conn_id) {
            echo "Database connection successful!<br>";
            echo "Database: " . $this->db->database . "<br>";
            if ($this->db->table_exists('students')) {
                echo "Students table exists!<br>";
                $count = $this->db->count_all('students');
                echo "Number of students (including deleted): " . $count . "<br>";
                
                $fields = $this->db->field_data('students');
                $has_is_deleted = FALSE;
                $has_address = FALSE;
                foreach ($fields as $field) {
                    if ($field->name === 'is_deleted') {
                        $has_is_deleted = TRUE;
                    }
                    if ($field->name === 'address') {
                        $has_address = TRUE;
                    }
                }
                echo "Address field exists: " . ($has_address ? 'Yes' : 'No') . "<br>";
                echo "Is Deleted field exists: " . ($has_is_deleted ? 'Yes' : 'No') . "<br>";
                
                if ($has_is_deleted) {
                    $this->db->where('is_deleted', 0);
                    $active_count = $this->db->count_all_results('students');
                    echo "Number of active students: " . $active_count . "<br>";
                } else {
                    echo "Number of active students: Not available (is_deleted column missing)<br>";
                }
            } else {
                echo "Students table does not exist! Please create it.";
            }
        } else {
            echo "Database connection failed!";
        }
    }

    public function setup_database() {
        $this->load->library('migration');
        
        echo "<h2>Database Setup</h2>";
        
        if ($this->migration->latest() === FALSE) {
            echo "<div style='color: red; padding: 10px; background: #ffebee;'>";
            echo "<strong> Migration Failed:</strong><br>";
            echo $this->migration->error_string();
            echo "</div>";
        } else {
            echo "<div style='color: green; padding: 10px; background: #e8f5e8;'>";
            echo "<strong> Database Setup Complete!</strong><br>";
            echo "Students table created with sample data.";
            echo "</div>";
        }
        
        echo "<p><a href='" . site_url('students') . "'>Go to Students List</a></p>";
    }
}