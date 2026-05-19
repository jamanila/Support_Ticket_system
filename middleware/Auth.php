<?php
require_once "../../models/Ticket.php";

class Auth{


    public static function checkRole($role){
        if(!isset($_SESSION["user"])){
            die("Access denied");
        }
        if(!in_array($_SESSION["user"]["role"],$role) ){
            die("Access denied");
        }
        
    }

    public static function canAccessTicket($ticket_id){
        if(!isset($_SESSION["user"])){
            die("Unauthorized access");
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
        else{
            die("Unauthorised ticket Access");
        }
    }
}

