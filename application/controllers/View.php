<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!class_exists('View')) {
    class View extends CI_Controller {
         public function __construct() {
        parent::__construct();
         $this->load->library('session');
        $this->load->helper('url');
        $this->config->set_item('csrf_protection', FALSE); // Temporary for debugging
    }

        public function index($view = '') {
            $view = str_replace('.php', '', $view);
            $view_path = 'ang/' . $view;
            if (file_exists(APPPATH . 'views/' . $view_path . '.php')) {
                $this->output->set_content_type('text/html');
                $this->load->view($view_path);
            } else {
                show_404();
            }
        }

        public function partial($partial = '') {
            $partial = str_replace('.php', '', $partial);
            $partial_path = 'ang/partials/' . $partial;
            if (file_exists(APPPATH . 'views/' . $partial_path . '.php')) {
                $this->output->set_content_type('text/html');
                $this->load->view($partial_path);
            } else {
                show_404();
            }
        }

        public function layout($layout = '') {
            $layout = str_replace('.php', '', $layout);
            $layout_path = 'layout/' . $layout;
            if (file_exists(APPPATH . 'views/' . $layout_path . '.php')) {
                $this->output->set_content_type('text/html');
                $this->load->view($layout_path);
            } else {
                show_404();
            }
        }
    }
}