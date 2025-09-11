<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Students Controller
 *
 * Handles CRUD operations for student records, including soft deletes, restores, and permanent deletes.
 * Provides AJAX-compatible JSON responses for frontend integration and manages database setup.

 */

class Students extends CI_Controller {
    public function __construct() {
      parent::__construct();
        $this->load->model('Student_model');
        $this->load->library('form_validation');
            //  comment out the following line in production
       // $this->config->set_item('csrf_protection', FALSE); // Temporary for debugging
    }

      // Retrieve all active students
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
           // $this->load->view('index'); // Load AngularJS app
             $this->load->view('ang/index'); // Updated path
        } else {
            redirect('/ci/ang/login'); // Redirect to login page
        }
    }


      /**
     * Manage Method
     *
     * Handles student management actions (add, edit, delete) based on the provided action parameter.
     * Validates input data, performs the requested action, and returns a JSON response with the result.
     * Requires user authentication.
     *
     * @return void
     */

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

    // Log the raw input for debugging
    // $raw_input = file_get_contents('php://input');
    // log_message('debug', 'Students manage raw input: ' . $raw_input);

    // // Decode JSON input
    // $json_data = json_decode($raw_input, true);
    // log_message('debug', 'Students manage JSON decoded: ' . json_encode($json_data));

     // Decode JSON input
    $json_data = json_decode(file_get_contents('php://input'), true);
    $action = isset($json_data['action']) ? $json_data['action'] : $this->input->post('action');
    log_message('debug', 'Action received in manage: ' . $action);

    // Handle GET request for fetching students
    $action = isset($json_data['action']) ? $json_data['action'] : $this->input->post('action');
    log_message('debug', 'Action received: ' . $action);

    // Handle GET request for fetching students
    if (!$action) {
          log_message('debug', 'No action provided, returning all students');
        $this->output->set_content_type('application/json');
        echo json_encode(array(
            'success' => true,
            'students' => $this->Student_model->get_students(),
            'csrf_token' => $this->security->get_csrf_hash()
        ));
        exit();
    }

    $response = array();
    switch ($action) {
        case 'add':
            // Manually set POST data for form validation
            if (isset($json_data['student'])) {
                $_POST['student'] = $json_data['student'];
                $_POST['student[name]'] = isset($json_data['student']['name']) ? $json_data['student']['name'] : '';
                $_POST['student[email]'] = isset($json_data['student']['email']) ? $json_data['student']['email'] : '';
                $_POST['student[phone]'] = isset($json_data['student']['phone']) ? $json_data['student']['phone'] : '';
                $_POST['student[address]'] = isset($json_data['student']['address']) ? $json_data['student']['address'] : '';
            }

            // Set form validation rules
            $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
            $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('student[phone]', 'Phone', 'required|trim');
            $this->form_validation->set_rules('student[address]', 'Address', 'trim');

            // Log POST data after setting
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
                    $data = $json_data['student']; // Fallback to JSON data
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
            // Manually set POST data for form validation
            if (isset($json_data['student'])) {
                $_POST['student'] = $json_data['student'];
                $_POST['student[name]'] = isset($json_data['student']['name']) ? $json_data['student']['name'] : '';
                $_POST['student[email]'] = isset($json_data['student']['email']) ? $json_data['student']['email'] : '';
                $_POST['student[phone]'] = isset($json_data['student']['phone']) ? $json_data['student']['phone'] : '';
                $_POST['student[address]'] = isset($json_data['student']['address']) ? $json_data['student']['address'] : '';
            }

            $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
            $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
            $this->form_validation->set_rules('student[phone]', 'Phone', 'required|trim');
            $this->form_validation->set_rules('student[address]', 'Address', 'trim');

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
                    $data = $json_data['student']; // Fallback to JSON data
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

    /**
     * Edit Method
     *
     * Retrieves a specific student's data by ID for editing purposes. Returns a JSON response with the student data
     * if found, or an error message if not. Requires user authentication.
     *
     * @param int $id The ID of the student to retrieve
     * @return void
     */

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


    // when i edit the student's table data to render on UI step - 4
      /**
     * Get Method
     *
     * Retrieves a specific student's data by ID for rendering on the UI. Returns a JSON response with the student data
     * if found, or an error message if not. Requires user authentication and a valid numeric ID.
     *
     * @param int|null $id The ID of the student to retrieve
     * @return void
     */

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

  /**
     * Deleted Method
     *
     * Retrieves a list of soft-deleted students. Returns a JSON response with the deleted students' data.
     * Requires user authentication.
     *
     * @return void
     */

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

    /**
     * Restore Method
     *
     * Restores a soft-deleted student by ID. Returns a JSON response indicating success or failure.
     * Requires user authentication.
     *
     * @param int $id The ID of the student to restore
     * @return void
     */

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

      /**
     * Permanent Delete Method
     *
     * Permanently deletes a student record by ID from the database. Returns a JSON response indicating success or failure.
     * Requires user authentication.
     *
     * @param int $id The ID of the student to permanently delete
     * @return void
     */

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

        /**
     * Setup Database Method
     *
     * Creates the 'students' table in the database if it does not exist and populates it with sample data.
     * Returns an HTML response confirming the setup completion.
     *
     * @return void
     */

   public function setup_database() {
    $this->load->dbforge();

    if (!$this->db->table_exists('students')) {
        $fields = array(
            'id' => array('type' => 'INT', 'auto_increment' => TRUE),
            'name' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'email' => array('type' => 'VARCHAR', 'constraint' => '100'),
            'phone' => array('type' => 'VARCHAR', 'constraint' => '20', 'null' => TRUE),
            'address' => array('type' => 'TEXT', 'null' => TRUE),
            'is_deleted' => array('type' => 'TINYINT', 'default' => 0)
        );
        $this->dbforge->add_field($fields);
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->create_table('students');

        $sample_data = array(
            array('name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '1234567890', 'address' => '123 Main St'),
            array('name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '0987654321', 'address' => '456 Oak Ave'),
            array('name' => 'Mike Johnson', 'email' => 'mike@example.com', 'phone' => '5551234567', 'address' => '789 Pine Rd'),
            array('name' => 'Sarah Williams', 'email' => 'sarah@example.com', 'phone' => '4449876543', 'address' => '321 Elm St'),
            array('name' => 'David Brown', 'email' => 'david@example.com', 'phone' => '7776543210', 'address' => '654 Birch Ln')
        );
        $this->db->insert_batch('students', $sample_data);
    }

    $this->load->view('ang/setup_database');
}

     /**
     * Test Database Method
     *
     * Tests the database connection and returns the result. For AJAX requests, returns a JSON response with the test output.
     * For non-AJAX requests, outputs the result directly.
     *
     * @return void
     */
    
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
}