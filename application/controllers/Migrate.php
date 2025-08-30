<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Migrate extends CI_Controller {

    public function __construct() {
        parent::__construct();
        // Load migration library only when needed
        $this->load->library('migration');
         // Debug: Log config loading
        $this->load->config('migration', TRUE);
        log_message('debug', 'Migration config loaded: ' . json_encode($this->config->item('migration')));
    }

    public function index() {
        // Restrict to CLI or add authentication for production
        if (!$this->input->is_cli_request()) {
            // Comment out for testing in browser
            // show_error('Migration can only be run from the command line.', 403);
        }

          // Debug: Output migration config values
        echo '<pre>';
        echo 'Migration Enabled: ' . ($this->config->item('migration_enabled') ? 'TRUE' : 'FALSE') . '<br>';
        echo 'Migration Version: ' . $this->config->item('migration_version') . '<br>';
        echo 'Migration Path: ' . $this->config->item('migration_path') . '<br>';
        echo '</pre>';



        // Check if migrations are enabled
        if (!$this->config->item('migration_enabled')) {
            show_error('Migrations are disabled in config/migration.php.');
        }

        // Run migration to the version specified in config
        $result = $this->migration->current();
        if ($result === TRUE) {
            echo 'Migration completed successfully to version: ' . $this->migration->current_version();
        } else {
            show_error('Migration failed: ' . $this->migration->error_string());
        }
    }

    public function rollback($version = 0) {
        if (!$this->input->is_cli_request()) {
            // Comment out for testing in browser
            // show_error('Migration can only be run from the command line.', 403);
        }

         // Debug: Output migration config values
        echo '<pre>';
        echo 'Migration Enabled: ' . ($this->config->item('migration_enabled') ? 'TRUE' : 'FALSE') . '<br>';
        echo 'Migration Version: ' . $this->config->item('migration_version') . '<br>';
        echo 'Migration Path: ' . $this->config->item('migration_path') . '<br>';
        echo '</pre>';

        if (!$this->config->item('migration_enabled')) {
            show_error('Migrations are disabled in config/migration.php.');
        }

        if ($this->migration->version($version)) {
            echo 'Migration rolled back successfully to version: ' . $version;
        } else {
            show_error('Rollback failed: ' . $this->migration->error_string());
        }
    }
}