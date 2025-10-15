<?php
session_start();
header('Content-Type: text/plain');

// Display session save path and status
echo "Session Save Path: " . session_save_path() . "\n";
echo "Session Status: " . session_status() . "\n";

// Display session data
if (!empty($_SESSION)) {
    echo "Session Data:\n";
    print_r($_SESSION);
} else {
    echo "Session is empty.\n";
}