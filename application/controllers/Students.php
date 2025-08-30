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
    
    // Separate method for adding students
    public function add() {
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        
        if ($this->input->post()) {
            if ($this->form_validation->run() === FALSE) {
                $data['error'] = validation_errors();
            } else {
                $student_data = array(
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone') ?: null,
                    'address' => $this->input->post('address') ?: null
                );
                
                if ($this->student_model->manage_student('add', null, $student_data)) {
                    $this->session->set_flashdata('success', 'Student added successfully!');
                    redirect('students');
                } else {
                    $data['error'] = 'Failed to add student. Please try again.';
                }
            }
        }
        
        $data['action'] = 'add';
        $data['student'] = (object) array('id' => '', 'name' => '', 'email' => '', 'phone' => '', 'address' => '');
        $this->load->view('students/manage', $data);
    }
    
    // Separate method for editing students
    public function edit($id = null) {
        if (!$id) {
            show_404();
        }
        
        $student = $this->student_model->get_student($id);
        if (!$student) {
            show_404();
        }
        
        $this->form_validation->set_rules('name', 'Name', 'required|trim');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim');
        $this->form_validation->set_rules('phone', 'Phone', 'trim');
        
        if ($this->input->post()) {
            if ($this->form_validation->run() === FALSE) {
                $data['error'] = validation_errors();
            } else {
                $student_data = array(
                    'name' => $this->input->post('name'),
                    'email' => $this->input->post('email'),
                    'phone' => $this->input->post('phone') ?: null,
                    'address' => $this->input->post('address') ?: null
                );
                
                if ($this->student_model->manage_student('edit', $id, $student_data)) {
                    $this->session->set_flashdata('success', 'Student updated successfully!');
                    redirect('students');
                } else {
                    $data['error'] = 'Failed to update student. Please try again.';
                }
            }
        }
        
        $data['action'] = 'edit';
        $data['student'] = $student;
        $data['id'] = $id;
        $this->load->view('students/manage', $data);
    }
    
    // AJAX method for deleting students
    public function delete() {
        // Prevent any output buffering issues
        ob_clean();
        
        // Force JSON response
        $this->output->set_content_type('application/json');
        
        $response = array('success' => false, 'message' => '');
        
        try {
            $id = $this->input->post('id');
            
            log_message('debug', 'Delete request received for ID: ' . $id);
            
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
            
            // Always include CSRF token
            $response['csrf_token'] = $this->security->get_csrf_hash();
            
        } catch (Exception $e) {
            log_message('error', 'Exception in delete method: ' . $e->getMessage());
            $response['message'] = 'An error occurred while deleting the student.';
        }
        
        // Output the JSON response
        echo json_encode($response);
        exit();
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