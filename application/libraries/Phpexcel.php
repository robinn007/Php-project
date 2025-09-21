<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phpexcel {
    protected $CI;
    public $excel;

    public function __construct() {
        $this->CI =& get_instance();
        // Prevent multiple inclusions of PHPExcel
        if (!class_exists('PHPExcel', false)) {
            require_once APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel.php';
            log_message('debug', 'PHPExcel library loaded from ' . APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel.php');
        } else {
            log_message('debug', 'PHPExcel class already loaded, skipping require_once');
        }
        $this->excel = new PHPExcel();
    }
}