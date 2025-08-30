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
    
    // Combined method for adding, editing, and deleting students
    public function manage() {
        // Get action and id from URI segments or POST data
        $action = $this->uri->segment(3, 'add'); // Default to 'add'
        $id = $this->uri->segment(4); // Get ID for edit/delete operations
        
        // Override with POST data if available (for AJAX requests)
        if ($this->input->post('action')) {
            $action = $this->input->post('action');
        }
        if ($this->input->post('id')) {
            $id = $this->input->post('id');
        }
        
        log_message('debug', "Manage method called: action='$action', id='$id', is_ajax=" . ($this->input->is_ajax_request() ? 'YES' : 'NO'));
        
        // Handle AJAX requests (mainly for delete)
        if ($this->input->is_ajax_request()) {
            return $this->handle_ajax_request($action, $id);
        }
        
        // Handle non-AJAX requests (add/edit forms)
        return $this->handle_form_request($action, $id);
    }
    
    private function handle_ajax_request($action, $id) {
        // Prevent any output buffering issues
        ob_clean();
        
        // Force JSON response
        $this->output->set_content_type('application/json');
        
        $response = array('success' => false, 'message' => '');
        
        try {
            switch ($action) {
                case 'delete':
                    if (!$id) {
                        $response['message'] = 'Student ID is required.';
                    } else {
                        // Check if student exists
                        $student = $this->student_model->get_student($id);
                        if (!$student) {
                            $response['message'] = 'Student not found.';
                            log_message('debug', 'Student not found with ID: ' . $id);
                        } else {
                            // Attempt to delete
                            if ($this->student_model->manage_student('delete', $id)) {
                                $response['success'] = true;
                                $response['message'] = 'Student deleted successfully.';
                                log_message('debug', 'Student deleted successfully: ' . $id);
                            } else {
                                $response['message'] = 'Failed to delete student. Database error.';
                                log_message('error', 'Failed to delete student: ' . $id);
                            }
                        }
                    }
                    break;
                    
                case 'add':
                case 'edit':
                    // Set form validation for AJAX add/edit (if you want to support this)
                    $this->form_validation->set_rules('name', 'Name', 'required|trim');
                    $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
                    $this->form_validation->set_rules('phone', 'Phone', 'trim');
                    
                    if ($this->form_validation->run() === FALSE) {
                        $response['message'] = strip_tags(validation_errors());
                    } else {
                        $student_data = array(
                            'name' => $this->input->post('name'),
                            'email' => $this->input->post('email'),
                            'phone' => $this->input->post('phone') ?: null,
                            'address' => $this->input->post('address') ?: null
                        );
                        
                        if ($action === 'add') {
                            if ($this->student_model->manage_student('add', null, $student_data)) {
                                $response['success'] = true;
                                $response['message'] = 'Student added successfully.';
                            } else {
                                $response['message'] = 'Failed to add student.';
                            }
                        } elseif ($action === 'edit' && $id) {
                            if ($this->student_model->manage_student('edit', $id, $student_data)) {
                                $response['success'] = true;
                                $response['message'] = 'Student updated successfully.';
                            } else {
                                $response['message'] = 'Failed to update student.';
                            }
                        }
                    }
                    break;
                    
                default:
                    $response['message'] = 'Invalid action specified.';
            }
            
            // Always include CSRF token
            $response['csrf_token'] = $this->security->get_csrf_hash();
            
        } catch (Exception $e) {
            log_message('error', 'Exception in AJAX manage method: ' . $e->getMessage());
            $response['message'] = 'An error occurred while processing your request.';
        }
        
        // Output the JSON response
        echo json_encode($response);
        exit();
    }
    
    private function handle_form_request($action, $id) {
        // Set form validation rules
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        
        // Initialize data array
        $data = array();
        $data['action'] = $action;
        
        // Handle edit action - verify student exists
        if ($action === 'edit') {
            if (!$id) {
                log_message('error', 'Edit action called without ID');
                show_404('Student ID is required for edit operation');
            }
            
            $student = $this->student_model->get_student($id);
            if (!$student) {
                log_message('error', "Student not found for edit: id=$id");
                show_404('Student not found');
            }
            
            $data['student'] = $student;
            $data['id'] = $id;
        } else {
            // For add action, create empty student object
            $action = 'add';
            $data['action'] = 'add';
            $data['student'] = (object) array('id' => '', 'name' => '', 'email' => '', 'phone' => '', 'address' => '');
        }
        
        // Handle form submission
        if ($this->input->post()) {
            log_message('debug', "Form submitted for action='$action'");
            
            if ($this->form_validation->run() === FALSE) {
                $data['error'] = validation_errors();
                log_message('debug', 'Form validation failed: ' . strip_tags(validation_errors()));
            } else {
                $student_data = array(
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone') ?: null,
                    'address' => $this->input->post('address') ?: null
                );
                
                $success = false;
                $success_message = '';
                
                if ($action === 'add') {
                    $success = $this->student_model->manage_student('add', null, $student_data);
                    $success_message = 'Student added successfully!';
                    log_message('debug', 'Add operation result: ' . ($success ? 'SUCCESS' : 'FAILED'));
                } elseif ($action === 'edit' && $id) {
                    $success = $this->student_model->manage_student('edit', $id, $student_data);
                    $success_message = 'Student updated successfully!';
                    log_message('debug', "Edit operation result for id=$id: " . ($success ? 'SUCCESS' : 'FAILED'));
                }
                
                if ($success) {
                    $this->session->set_flashdata('success', $success_message);
                    redirect('students');
                } else {
                    $data['error'] = 'Operation failed. Please try again.';
                    log_message('error', "Operation failed: action=$action, id=$id");
                }
            }
        }
        
        // Load the manage view
        log_message('debug', "Loading manage view with action='$action'");
        $this->load->view('students/manage', $data);
    }
    
    // Convenience methods for backward compatibility
    public function add() {
        redirect('students/manage/add');
    }
    
    public function edit($id = null) {
        if (!$id) {
            show_404('Student ID is required');
        }
        redirect('students/manage/edit/' . $id);
    }
    
    public function delete() {
        // This method is just an alias for manage with delete action
        $_POST['action'] = 'delete';
        $this->manage();
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