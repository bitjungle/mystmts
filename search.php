<?php
/**
 * Database live search for mystmts
 * 
 * Copyright (C) 2021 BITJUNGLE Rune Mathisen
 * This code is licensed under a GPLv3 license 
 * See http://www.gnu.org/licenses/gpl-3.0.html 
 */
require_once 'inc/Settings.php';
require_once 'inc/Database.php';

header('Content-Type: application/json');

$settings = new Settings('path/to/settings.ini');
try {
    $db = new Database($settings->db);
    if (strlen($_POST['str']) > 0) {
        echo json_encode($db->search($_POST['str']));
    } else {
        echo '{}';
    }
} catch (exception $e) {
    http_response_code(503); // Service Unavailable
    echo '{}';
    exit($e->getMessage());
}
?>
