<?php
require_once __DIR__ . "/../models/Ticket.php";

class Auth{


    public static function checkRole($role){
        if(!isset($_SESSION["user"])){
            header("Location: /OOP/SupportSystem/views/errors/401.php");
            exit();
        }
        if(!in_array($_SESSION["user"]["role"],$role) ){
            header("Location: /OOP/SupportSystem/views/errors/403.php");
            exit();
        }
        else{
            return true;
        }
        
    }

    public static function canAccessTicket($ticket_id){
        if(!isset($_SESSION["user"])){
            
            header("Location: /OOP/SupportSystem/views/errors/401.php");
            exit();
        }
        
        $ticketModel = new Ticket();
        $ticket = $ticketModel->getTicketById($ticket_id);
        
        $currentLoggedUser = $_SESSION["user"];
        if($currentLoggedUser["role"] === "admin"){
            return true;
        }
        if($currentLoggedUser["id"] == $ticket["user_id"] || $currentLoggedUser['id'] == $ticket["assigned_to"]){
            return true;
        }

        header("Location: /OOP/SupportSystem/views/errors/403.php");
        exit();
    }
}

