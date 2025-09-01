<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Students extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('Student_model');
        $this->load->library('form_validation');
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
            redirect('/ci/ang/#/students');
        } else {
            redirect('auth/login');
        }
    }

    public function manage() {
        if (!$this->session->userdata('user_id')) {
            $this->output->set_content_type('application/json');
            echo json_encode(array(
                'success' => false,
                'message' => 'Please log in to perform this action.',
                'csrf_token' => $this->security->get_csrf_hash()
            ));
            exit();
        }

        $action = $this->input->post('action');
        if (!$action) {
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
                $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
                $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('student[phone]', 'Phone', 'trim');
                $this->form_validation->set_rules('student[address]', 'Address', 'trim');

                if ($this->form_validation->run() === FALSE) {
                    $response = array(
                        'success' => false,
                        'message' => strip_tags(validation_errors()),
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                } else {
                    $data = $this->input->post('student');
                    if ($this->Student_model->manage_student('add', null, $data)) {
                        $response = array(
                            'success' => true,
                            'message' => 'Student added successfully.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                    } else {
                        $response = array(
                            'success' => false,
                            'message' => 'Failed to add student 5.',
                            'csrf_token' => $this->security->get_csrf_hash()
                        );
                    }
                }
                break;

            case 'edit':
                $id = $this->input->post('id');
                $this->form_validation->set_rules('student[name]', 'Name', 'required|trim');
                $this->form_validation->set_rules('student[email]', 'Email', 'required|valid_email|trim');
                $this->form_validation->set_rules('student[phone]', 'Phone', 'trim');
                $this->form_validation->set_rules('student[address]', 'Address', 'trim');

                if ($this->form_validation->run() === FALSE) {
                    $response = array(
                        'success' => false,
                        'message' => strip_tags(validation_errors()),
                        'csrf_token' => $this->security->get_csrf_hash()
                    );
                } else {
                    $data = $this->input->post('student');
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
                    }
                }
                break;

            case 'delete':
                $id = $this->input->post('id');
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
                }
                break;

            default:
                $response = array(
                    'success' => false,
                    'message' => 'Invalid action.',
                    'csrf_token' => $this->security->get_csrf_hash()
                );
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

        $this->output->set_content_type('text/html');
        echo '<h2>Database Setup</h2>';
        echo '<p>Database Setup Complete!</p>';
        echo '<p>Users table created.</p>';
        echo '<p>Students table created with sample data.</p>';
        echo '<a href="/ci/ang/index.html#/students">Go to Students List</a>';
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
}