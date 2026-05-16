<?php
require_once "../../models/Ticket.php";

//create a ticket object
$ticket = new Ticket();

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $ticket->updateTicket($id, "Closed");
    header("Location: index.php");
    exit();
}