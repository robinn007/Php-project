<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Manually include PHPExcel files to avoid autoloader conflicts
log_message('debug', 'Attempting to include PHPExcel from: ' . APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel.php');
if (!class_exists('PHPExcel', false)) {
    require_once APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel.php';
    log_message('debug', 'Included PHPExcel successfully');
}
require_once APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once APPPATH . 'third_party/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';

class CI_Phpexcel {
    private $CI;
    private $excel;

    public function __construct() {
        $this->CI = &get_instance();
        log_message('debug', 'CI_Phpexcel library initialized for Excel export at: ' . __FILE__);
        $this->excel = new PHPExcel();
    }

    public function generate_excel($clicks) {
        try {
            log_message('debug', 'Generating Excel file in CI_Phpexcel.php');
            $this->excel->getProperties()->setCreator('Your Name')
                                        ->setLastModifiedBy('Your Name')
                                        ->setTitle('Clicks Export')
                                        ->setSubject('Clicks Data Export')
                                        ->setDescription('Export of clicks data');

            $sheet = $this->excel->setActiveSheetIndex(0);

            $headers = ['ID', 'PID', 'Link', 'Campaign ID', 'EIDT', 'EID', 'Timestamp'];
            $col = 0;
            foreach ($headers as $header) {
                $sheet->setCellValueByColumnAndRow($col++, 1, $header);
            }

            $row = 2;
            foreach ($clicks as $click) {
                $col = 0;
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['id'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['pid'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['link'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['campaignId'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['eidt'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['eid'] ?? '');
                $sheet->setCellValueByColumnAndRow($col++, $row, $click['timestamp'] ?? '');
                $row++;
            }

            foreach (range('A', 'G') as $columnID) {
                $sheet->getColumnDimension($columnID)->setAutoSize(true);
            }

            $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
            ob_start();
            $objWriter->save('php://output');
            $content = ob_get_clean();

            return $content;
        } catch (Exception $e) {
            log_message('error', 'CI_Phpexcel export error: ' . $e->getMessage());
            throw $e;
        }
    }
}

