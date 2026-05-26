<?php

require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../middleware/Auth.php";

class Comment{

    public $id;
    public $user_id;
    public $ticket_id;
    public $role;
    public $message;
    public $conn;

    // 1️⃣ 
    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

    // 2️⃣ Add comment
public function addComment($ticket_id, $user_id, $role, $message){

    Auth::canAccessTicket($ticket_id);

    if(empty($message)){
        return false;
    }

    $stmt = $this->conn->prepare("
        INSERT INTO comments (ticket_id, user_id, role, message)
        VALUES (:ticket_id, :user_id, :role, :message)
    ");

    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":role", $role);
    $stmt->bindParam(":message", $message);

    return $stmt->execute();
    
}

    // 3️⃣ Get comments for a ticket
    public function getCommentsByTicket($ticket_id){

        $stmt = $this->conn->prepare("
            SELECT 
                comments.id,
                comments.message,
                comments.created_at,
                comments.role,
                users.name
            FROM comments
            JOIN users ON  users.id =comments.user_id
            WHERE comments.ticket_id = :ticket_id
            ORDER BY comments.created_at ASC
        ");

        $stmt->bindParam(":ticket_id", $ticket_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function getTicketsWithUnreadCount($user_id){

    $stmt = $this->conn->prepare("
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
            ) AS unread_count

        FROM tickets t

        LEFT JOIN users AS creator ON t.user_id = creator.id
        LEFT JOIN users AS agent ON t.assigned_to = agent.id

        ORDER BY t.created_at DESC
    ");

    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>