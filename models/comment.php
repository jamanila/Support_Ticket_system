<?php

require_once("../../middleware/Auth.php");

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
}
?>