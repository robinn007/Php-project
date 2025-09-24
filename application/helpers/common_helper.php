<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Export data to CSV format
 *
 * @param string $export_type Must be 'csv'
 * @param array $data Data array with headers, rows
 * @return string File content
 */
function export_to_file($export_type, $data) {
    try {
        if ($export_type !== 'csv') {
            log_message('error', 'export_to_file: Invalid export type: ' . $export_type);
            return false;
        }

        $content = array_to_csv($data['headers'], $data['rows']);
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

/**
 * Export data to XLSX format using PHPEXCEL
 *
 * @param array $data Data array with rows
 * @return string File content
 */
function export_to_xlsx($data) {
    // This will be handled by the PHPEXCEL library
    return '';
}

