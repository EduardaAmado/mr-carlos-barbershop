<?php
session_start();
header('Content-Type: text/plain');

if (!isset($_SESSION['test_key'])) {
    $_SESSION['test_key'] = 'test_value';
    echo "Session data set.\n";
} else {
    echo "Session data retrieved: " . $_SESSION['test_key'] . "\n";
}

session_write_close();