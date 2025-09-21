<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Phpexcel {
    protected $CI;
    public $excel;

    public function __construct() {
        $this->CI =& get_instance();
        
        try {
            // Set error reporting to catch PHPExcel issues
            $old_error_reporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
            
            // Check if PHPExcel is already loaded to prevent multiple inclusions
            if (!class_exists('PHPExcel', false)) {
                $phpexcel_path = APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel.php';
                
                if (!file_exists($phpexcel_path)) {
                    log_message('error', 'PHPExcel library file not found at: ' . $phpexcel_path);
                    throw new Exception('PHPExcel library file not found');
                }
                
                require_once $phpexcel_path;
                log_message('debug', 'PHPExcel library loaded from ' . $phpexcel_path);
            } else {
                log_message('debug', 'PHPExcel class already loaded, skipping require_once');
            }
            
            // Create PHPExcel object
            $this->excel = new PHPExcel();
            
            // Set default properties
            $this->excel->getProperties()
                        ->setCreator('Clicks Export System')
                        ->setLastModifiedBy('Clicks Export System')
                        ->setTitle('Data Export')
                        ->setSubject('Exported Data')
                        ->setDescription('Data exported from application')
                        ->setKeywords('export data')
                        ->setCategory('Export');
            
            // Restore error reporting
            error_reporting($old_error_reporting);
            
            log_message('debug', 'PHPExcel initialized successfully');
            
        } catch (Exception $e) {
            log_message('error', 'PHPExcel initialization failed: ' . $e->getMessage());
            throw new Exception('Failed to initialize PHPExcel: ' . $e->getMessage());
        }
    }
    
    /**
     * Get a clean PHPExcel object
     * @return PHPExcel
     */
    public function getExcel() {
        return $this->excel;
    }
    
    /**
     * Reset the PHPExcel object to defaults
     */
    public function reset() {
        try {
            $this->excel = new PHPExcel();
            log_message('debug', 'PHPExcel object reset');
        } catch (Exception $e) {
            log_message('error', 'Failed to reset PHPExcel object: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check if PHPExcel is properly loaded
     * @return boolean
     */
    public function isLoaded() {
        return class_exists('PHPExcel', false) && isset($this->excel);
    }
}

