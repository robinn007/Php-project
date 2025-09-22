<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Export data to specified format (csv, excel, or xls)
 *
 * @param string $export_type csv, excel, or xls
 * @param array $data Data array with headers, rows, header, footer
 * @return string File content
 */
function export_to_file($export_type, $data) {
    try {
        if ($export_type === 'csv') {
            $content = array_to_csv($data['headers'], $data['rows']);
        } elseif ($export_type === 'excel') {
            $content = array_to_excel($data, 'xlsx');
        } elseif ($export_type === 'xls') {
            $content = array_to_excel($data, 'xls');
        } else {
            log_message('error', 'export_to_file: Invalid export type: ' . $export_type);
            return false; // safer than returning $content when undefined
        }

        return $content;

    } catch (Exception $e) {
        log_message('error', 'export_to_file: Exception - ' . $e->getMessage());
        return false;
    } catch (Throwable $t) {
        log_message('error', 'export_to_file: Fatal error - ' . $t->getMessage());
        return false;
    }
}


/**
 * Convert array to CSV format
 *
 * @param array $headers Array of column names
 * @param array $rows Array of data rows
 * @return string The CSV content
 */
function array_to_csv($headers, $rows) {
    if (!is_array($headers) || empty($headers)) {
        log_message('error', 'array_to_csv: Invalid or empty headers array');
        return '';
    }

    $output = '';
    // Escape and format headers
    $escaped_headers = array_map('escape_csv_field', $headers);
    $output .= '"' . implode('","', $escaped_headers) . '"' . "\n";

    // Process each data row
    foreach ($rows as $row) {
        if (!is_array($row)) {
            log_message('error', 'array_to_csv: Invalid row data, skipping: ' . json_encode($row));
            continue;
        }

        // Ensure row has same number of columns as headers, pad with empty strings if needed
        $row = array_pad($row, count($headers), '');
        $escaped_row = array_map('escape_csv_field', $row);
        $output .= '"' . implode('","', $escaped_row) . '"' . "\n";
    }

    return $output;
}



/**
 * Convert array to Excel format
 *
 * @param array $data Array with 'headers', 'rows', and optional 'header' and 'footer'
 * @return string The Excel file content (binary)
 */

function array_to_excel($data, $format = 'xlsx') {
    $ci =& get_instance();
    
    // Load PHPExcel library
    $ci->load->library('phpexcel');

    try {
        log_message('debug', 'array_to_excel: Starting Excel generation in ' . $format . ' format');
        
        // Clear any existing output buffers to prevent interference
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $excel = $ci->phpexcel->excel;
        $sheet = $excel->getActiveSheet();

        // Set basic document properties
        $excel->getProperties()
              ->setCreator('Clicks Export System')
              ->setTitle('Clicks Export')
              ->setSubject('Click Data Export')
              ->setDescription('Export of click tracking data');

        $row = 1;

        // Add header (title) if provided
        if (!empty($data['header']) && is_string($data['header'])) {
            $sheet->setCellValue('A' . $row, $data['header']);
            $sheet->mergeCells('A' . $row . ':' . chr(64 + count($data['headers'])) . $row);
            // Bold the header
            $sheet->getStyle('A' . $row)->getFont()->setBold(true);
            $row++;
            $row++; // Add extra spacing
        }

        // Validate headers
        if (!is_array($data['headers']) || empty($data['headers'])) {
            log_message('error', 'array_to_excel: Invalid or empty headers array');
            throw new Exception('Invalid headers array');
        }

        // Add column headers with styling
        $col = 0;
        foreach ($data['headers'] as $header) {
            $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
            $sheet->setCellValue($cellCoordinate, $header);
            $sheet->getStyle($cellCoordinate)->getFont()->setBold(true);
            $sheet->getStyle($cellCoordinate)->getFill()
                  ->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('E0E0E0');
            $col++;
        }
        $row++;

        // Add data rows
        if (isset($data['rows']) && is_array($data['rows'])) {
            log_message('debug', 'array_to_excel: Adding ' . count($data['rows']) . ' data rows');
            
            foreach ($data['rows'] as $index => $data_row) {
                if (!is_array($data_row)) {
                    log_message('error', 'array_to_excel: Invalid row data at index ' . $index);
                    continue;
                }

                $col = 0;
                $data_row = array_pad($data_row, count($data['headers']), '');
                foreach ($data_row as $value) {
                    $cellCoordinate = PHPExcel_Cell::stringFromColumnIndex($col) . $row;
                    $sheet->setCellValue($cellCoordinate, $value);
                    $col++;
                }
                $row++;
            }
        }

        // Add footer if provided
        if (!empty($data['footer']) && is_string($data['footer'])) {
            $row++; // Add spacing
            $sheet->setCellValue('A' . $row, $data['footer']);
            $sheet->mergeCells('A' . $row . ':' . chr(64 + count($data['headers'])) . $row);
            $sheet->getStyle('A' . $row)->getFont()->setItalic(true);
        }

        // Auto-size columns for better presentation
        foreach (range(0, count($data['headers']) - 1) as $col) {
            $columnLetter = PHPExcel_Cell::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // Generate Excel file content based on format
        log_message('debug', 'array_to_excel: Generating Excel file content in ' . $format . ' format');
        
        // Choose writer based on format
        if ($format === 'xls') {
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5'); // For XLS format
        } else {
            $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007'); // For XLSX format
        }
        
        // Use temporary file to avoid memory issues with large files
        $temp_file = tempnam(sys_get_temp_dir(), 'excel_export_');
        
        if ($temp_file === false) {
            throw new Exception('Failed to create temporary file');
        }
        
        try {
            $writer->save($temp_file);
            
            if (!file_exists($temp_file) || filesize($temp_file) == 0) {
                throw new Exception('Excel file generation failed - empty or missing file');
            }
            
            $content = file_get_contents($temp_file);
            unlink($temp_file); // Clean up temp file
            
            if (empty($content)) {
                throw new Exception('Excel file content is empty');
            }
            
            log_message('debug', 'array_to_excel: Successfully generated ' . $format . ' file (' . strlen($content) . ' bytes)');
            return $content;
            
        } catch (Exception $e) {
            // Clean up temp file on error
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
            throw $e;
        }

    } catch (Exception $e) {
        log_message('error', 'array_to_excel: Exception - ' . $e->getMessage());
        log_message('error', 'array_to_excel: Stack trace - ' . $e->getTraceAsString());
        return false;
    } catch (Throwable $t) {
        log_message('error', 'array_to_excel: Fatal error - ' . $t->getMessage());
        return false;
    }
}

/**
 * Escape a field for CSV output
 *
 * @param mixed $field The field value to escape
 * @return string The escaped field
 */
function escape_csv_field($field) {
    if ($field === null || $field === '') {
        return '';
    }
    $field = (string)$field;
    $field = str_replace('"', '""', $field);
    return $field;
}

