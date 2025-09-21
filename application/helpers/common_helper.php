<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Export data to a file format
 *
 * @param string $format The format of the output (currently supports 'csv')
 * @param array $data Array where first element is column names, rest are data rows
 * @return string|bool The formatted content or false on failure
 */
function export_to_file($format, $data) {
    if (!is_array($data) || empty($data)) {
        log_message('error', 'export_to_file: Invalid or empty data array');
        return false;
    }

    switch (strtolower($format)) {
        case 'csv':
            return array_to_csv($data);
        default:
            log_message('error', "export_to_file: Unsupported format '$format'");
            return false;
    }
}

/**
 * Convert array to CSV format
 *
 * @param array $data Array where first element is column names, rest are data rows
 * @return string The CSV content
 */
function array_to_csv($data) {
    // Check if data has at least the headers
    if (!isset($data[0]) || !is_array($data[0])) {
        log_message('error', 'array_to_csv: First element must be an array of column names');
        return '';
    }

    $output = '';
    $headers = array_shift($data); // Get column names (first element)

    // Escape and format headers
    $escaped_headers = array_map('escape_csv_field', $headers);
    $output .= '"' . implode('","', $escaped_headers) . '"' . "\n";

    // Process each data row
    foreach ($data as $row) {
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