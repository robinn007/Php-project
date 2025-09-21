<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

function get_greeting() {
    return "Hello from Common Helper!";
}

function format_name($first_name, $last_name) {
    return ucfirst($first_name) . ' ' . ucfirst($last_name);
}