<?php

function konekDb() {
    $servername = 'localhost';
    $username = 'root';
    $password = '';
    $dbname = 'psm';

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    return $conn;
}