<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/api/utils/server.php';

function getDatabaseConnection(): PDO
{
    try {
        $host = 'localhost';
        $db = 'businesscare';
        $user = 'root';
        $pass = 'alpine';
        $port = '3306';
        return new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    } catch (PDOException $e) {
        returnError(500, 'Could not connect to the database. ' . $e->getMessage());
        die();
    }
}
