<?php
session_start();
require_once("../../middleware/Auth.php");
require_once "../../models/Ticket.php";
Auth::checkRole(['admin']);   
$ticket = new Ticket();

if(isset($_GET["id"])){
    $id = $_GET["id"];

    $ticket->deleteTicket($id);
    header("Location: index.php");
    exit();
}