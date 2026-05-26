<?php
session_start();
require_once __DIR__ . "/../../app/models/Ticket.php";

//create a ticket object
$ticket = new Ticket();
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $ok = $ticket->updateTicket($id, "closed");
    if($ok){
        $_SESSION['flash'][] = ['type' => 'success', 'message' => 'Ticket closed'];
    } else {
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Failed to close ticket'];
    }
    header("Location: ../admin/index.php");
    exit();
}