<?php
require_once __DIR__ . "/../../config/db.php";

class Notification{

public $conn;
public $is_read = "Not read";

public function __construct()
{
    $database = new Database();
    $this->conn = $database->connect();
}

//create notification on the database
function createNotification($user_id, $ticket_id, $message){
    $stmt = $this->conn->prepare("INSERT INTO notifications(user_id, ticket_id, message) VALUES(:user_id, :ticket_id, :message)");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->bindParam(":message", $message);
    $stmt->execute();
}

//getting notification belonging to a user
function getUserNotification($user_id){
    $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getLatestNotifications($user_id, $limit = 5){
    $stmt = $this->conn->prepare("SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT :limit");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnreadTicketNotificationsCount($user_id){
    $stmt = $this->conn->prepare(
        "SELECT COUNT(*) AS total FROM notifications WHERE user_id = :user_id AND is_read = 0 AND (
            message LIKE 'A ticket has been assigned to you%'
            OR message LIKE 'New ticket created by %'
            OR message LIKE 'Your ticket has been assigned to %'
        )"
    );
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
}

function getUnreadTicketNotifications($user_id, $limit = 5){
    $stmt = $this->conn->prepare(
        "SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 AND (
            message LIKE 'A ticket has been assigned to you%'
            OR message LIKE 'New ticket created by %'
            OR message LIKE 'Your ticket has been assigned to %'
        ) ORDER BY created_at DESC LIMIT :limit"
    );
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindValue(":limit", (int) $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

//getting all unread nottification
function getUnreadNotificationCount($user_id){
    $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO:: FETCH_ASSOC)['total'];
}

//Update notification and mark it as read
function markAsRead($id){
    $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
    $stmt->bindParam(":id", $id);
    $stmt->execute();
}

function markTicketNotificationsAsRead($user_id, $ticket_id){
    $stmt = $this->conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND ticket_id = :ticket_id AND is_read = 0");
    $stmt->bindParam(":user_id", $user_id);
    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->execute();
}




}