<?php
session_start();
require_once "../../models/Ticket.php";

//create a ticket object
$ticket = new Ticket();
if(isset($_GET['id'])){
    $id = $_GET['id'];
    $ticket->updateTicket($id, "closed");
    header("Location: ../admin/index.php");
    exit();
}