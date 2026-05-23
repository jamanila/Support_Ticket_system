<?php
class Notification{

public $conn;

public function __construct()
{
    $database = new Database();
    $this->conn = $database->connect();
}

//create notification on the database
function createNotification($user_id, $ticket_id, $message){
    $stmt = $this->conn->prepare("INSERT INTO notifications VALUES(:user_id, :ticket_id, :message)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->bindParam(":message", $message);
    $stmt->execute();
}

//getting notification from the datbase and displays to the corresponding user
function getUserNotification(){
    $stmt = $this->conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//Update notification and mark it as read
function markAsRead($id){
    $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
}


}