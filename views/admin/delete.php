<?php
session_start();
require_once(__DIR__ . "/../../app/middleware/Auth.php");
require_once __DIR__ . "/../../app/models/Ticket.php";
Auth::checkRole(['admin']);   
$ticket = new Ticket();

if(isset($_GET["id"])){
    $id = $_GET["id"];

    $ticket->deleteTicket($id);
    $_SESSION['flash'][] = ['type' => 'success', 'message' => 'Ticket deleted'];
    header("Location: index.php");
    exit();
}