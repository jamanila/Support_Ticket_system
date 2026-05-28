<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/Auth.php";

class Ticket {

    public $id;
    public $title;
    public $description;
    public $attachment;
    public $status = "open";
    public $created_at;
    public $user_id;

    public $conn;

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
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return $this->id;
        }

        return false;
    }
function getTicketsForAgent($agent_id, $limit = null, $offset = 0){

    $query = "
        SELECT 
            t.*,
            creator.name AS creator_name,

            (
                SELECT COUNT(*)
                FROM comments c
                WHERE c.ticket_id = t.id
                AND c.created_at > COALESCE(
                    (
                        SELECT tr.last_read_at
                        FROM ticket_reads tr
                        WHERE tr.ticket_id = t.id
                        AND tr.user_id = :agent_id
                    ),
                    '1970-01-01'
                )
            ) AS unread_count,

            (
                SELECT MAX(n.created_at)
                FROM notifications n
                WHERE n.ticket_id = t.id
                AND n.user_id = :agent_id
            ) AS last_notification_at

        FROM tickets t

        LEFT JOIN users AS creator
            ON t.user_id = creator.id

        WHERE t.assigned_to = :agent_id

        ORDER BY unread_count DESC, last_notification_at DESC, t.created_at DESC
    ";

    if ($limit !== null) {
        $query .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":agent_id", $agent_id);

    if ($limit !== null) {
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function countTicketsForAgent($agent_id){
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tickets WHERE assigned_to = :agent_id");
    $stmt->bindParam(":agent_id", $agent_id);
    $stmt->execute();
    return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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

public function countTicketsForUser($user_id){
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tickets WHERE user_id = :user_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
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

public function getTicketsWithUnreadCount($user_id, $limit = null, $offset = 0){

    $query = "

        SELECT 
            t.*,
            creator.name AS creator_name,
            agent.name AS agent_name,

            (
                SELECT COUNT(*)
                FROM comments c

                WHERE c.ticket_id = t.id

                AND c.created_at > COALESCE(

                    (
                        SELECT tr.last_read_at
                        FROM ticket_reads tr
                        WHERE tr.ticket_id = t.id
                        AND tr.user_id = :user_id
                    ),

                    '1970-01-01'

                )

                AND c.user_id != :user_id

            ) AS unread_count,

            (
                SELECT MAX(n.created_at)
                FROM notifications n
                WHERE n.ticket_id = t.id
                AND n.user_id = :user_id
            ) AS last_notification_at

        FROM tickets t

        LEFT JOIN users AS creator
            ON t.user_id = creator.id

        LEFT JOIN users AS agent
            ON t.assigned_to = agent.id

        ORDER BY unread_count DESC, last_notification_at DESC, t.created_at DESC

    ";

    if ($limit !== null) {
        $query .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);

    if ($limit !== null) {
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getTicketsWithUnreadCountForUser($user_id, $limit = null, $offset = 0){

    $query = "

        SELECT 
            t.*,
            creator.name AS creator_name,
            agent.name AS agent_name,

            (
                SELECT COUNT(*)
                FROM comments c

                WHERE c.ticket_id = t.id

                AND c.created_at > COALESCE(

                    (
                        SELECT tr.last_read_at
                        FROM ticket_reads tr
                        WHERE tr.ticket_id = t.id
                        AND tr.user_id = :user_id
                    ),

                    '1970-01-01'

                )

                AND c.user_id != :user_id

            ) AS unread_count,

            (
                SELECT MAX(n.created_at)
                FROM notifications n
                WHERE n.ticket_id = t.id
                AND n.user_id = :user_id
            ) AS last_notification_at

        FROM tickets t

        LEFT JOIN users AS creator
            ON t.user_id = creator.id

        LEFT JOIN users AS agent
            ON t.assigned_to = agent.id

        WHERE t.user_id = :user_id

        ORDER BY unread_count DESC, last_notification_at DESC, t.created_at DESC

    ";

    if ($limit !== null) {
        $query .= " LIMIT :limit OFFSET :offset";
    }

    $stmt = $this->conn->prepare($query);
    $stmt->bindParam(":user_id", $user_id);

    if ($limit !== null) {
        $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue(":offset", (int) $offset, PDO::PARAM_INT);
    }

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function countTickets(){
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tickets");
    $stmt->execute();
    return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

public function countTicketsByStatus($status){
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM tickets WHERE status = :status");
    $stmt->bindParam(":status", $status);
    $stmt->execute();
    return (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

public function getTicketCountsForUser($user_id){
    $stmt = $this->conn->prepare("SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count
        FROM tickets
        WHERE user_id = :user_id");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getTicketCountsForAgent($agent_id){
    $stmt = $this->conn->prepare("SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count
        FROM tickets
        WHERE assigned_to = :agent_id");
    $stmt->bindParam(":agent_id", $agent_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getTicketCounts(){
    $stmt = $this->conn->prepare("SELECT 
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_count,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_count,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_count
        FROM tickets");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


}
