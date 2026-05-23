<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ ."/../middleware/Auth.php";

class Ticket {

    public $id;
    public $title;
    public $description;
    public $status = "open";
    public $created_at;
    public $user_id;

    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // CREATE TICKET WITH USER
    public function createTicket(){

        $stmt = $this->conn->prepare("
            INSERT INTO tickets (title, description, status, user_id)
            VALUES (:title, :description, :status, :user_id)
        ");

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    // GET ALL TICKETS with their corresponding creators
function getAllTickets(){
    $stmt = $this->conn->prepare("
        SELECT 
            tickets.*,
            creator.name AS creator_name,
            agent.name AS agent_name
        FROM tickets
        LEFT JOIN users AS creator 
            ON tickets.user_id = creator.id
        LEFT JOIN users AS agent 
            ON tickets.assigned_to = agent.id
        ORDER BY tickets.id DESC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//this function gets all tickets assigned to agent. (agents open their dashboard and interact with tickets assigned to them)

function getTicketsForAgent($agent_id){
    $stmt = $this->conn->prepare("
        SELECT * FROM tickets WHERE assigned_to = :agent_id
    ");
    $stmt->bindParam(":agent_id", $agent_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getTicketsForUser($user_id){

    $stmt = $this->conn->prepare("
        SELECT 
            tickets.*,
            users.name AS creator_name,
            agent.name AS agent_name
        FROM tickets
        LEFT JOIN users 
            ON tickets.user_id = users.id
        LEFT JOIN users AS agent 
            ON tickets.assigned_to = agent.id
        WHERE tickets.user_id = :user_id
        ORDER BY tickets.id DESC
    ");

    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
//role based tickets
function getTickets(){
    //check the logged in users
    if(!isset($_SESSION["user"])){
        die("Access denied");
    }else{
        $user = $_SESSION["user"];
        $user_id = $user["id"];
        $role = $user["role"];
        if($role == "admin"){
            $stmt = $this->conn->prepare("SELECT * FROM tickets");
            $stmt->execute();
        }
        elseif($role == "agent"){
            $stmt = $this->conn->prepare("SELECT * FROM tickets WHERE assigned_to = :assigned_to");
            $stmt->bindParam(":assigned_to", $user_id);
            $stmt->execute();
        }
        else{
            $stmt = $this->conn->prepare("SELECT * FROM tickets WHERE id = :user_id");
            $stmt->bindParam(":user_id", $user_id);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
function getTicketsForLoggedInUser($id){
   $stmt = $this->conn->prepare("SELECT * FROM tickets WHERE id = :id");
   $stmt->bindParam(":id", $id);
   $stmt->execute();
   $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
  
   if(!$ticket){
    die("Ticket not found");
   }
    Auth::canAccessTicket($ticket);
   return $ticket;
}

function assignTicketToAgent($ticket_id, $agent_id){
        Auth::checkRole(['admin']);
        $stmt = $this->conn->prepare("UPDATE tickets SET assigned_to = :agent_id WHERE id = :ticket_id");
        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->bindParam(":agent_id", $agent_id);
        $stmt->execute();
}

    // UPDATE TICKET
    function updateTicket($id, $status){
        Auth::checkRole(['admin', 'agent']);
        $stmt = $this->conn->prepare("UPDATE tickets SET status = :status WHERE id = :id
        ");
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    // DELETE TICKET
    function deleteTicket($id){
        $stmt = $this->conn->prepare(" DELETE FROM tickets WHERE id = :id");
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    function getTicketById($id){
        $stmt= $this->conn->prepare("SELECT tickets.*, creator.name AS creator_name, agent.name AS agent_name
        FROM tickets
        JOIN users as creator ON
        creator.id = tickets.user_id
        LEFT JOIN users as agent ON
        agent.id = tickets.assigned_to
        WHERE tickets.id = :ticket_id");
        $stmt->bindParam(":ticket_id", $id);
        $stmt->execute();
        $ticket = $stmt->fetch(PDO:: FETCH_ASSOC);
        return $ticket;;
    }
}