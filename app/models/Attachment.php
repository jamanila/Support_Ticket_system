<?php

require_once __DIR__ . "/../../config/db.php";

class Attachment {

    public $conn;

    public function __construct(){
        $database = new Database();
        $this->conn = $database->connect();
    }

public function uploadTicket($ticket_id, $file, $user_id){

    $fileName = time() . "_" . basename($file['name']);

    $uploadDir = __DIR__ . "/../../uploads/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $targetPath = $uploadDir . $fileName;

    move_uploaded_file($file['tmp_name'], $targetPath);

    $dbPath = "uploads/" . $fileName;

    $stmt = $this->conn->prepare("
        INSERT INTO ticket_attachments
        (ticket_id, file_name, file_path, uploaded_by)
        VALUES
        (:ticket_id, :file_name, :file_path, :uploaded_by)
    ");

    return $stmt->execute([
        ":ticket_id" => $ticket_id,
        ":file_name" => $fileName,
        ":file_path" => $dbPath,
        ":uploaded_by" => $user_id
    ]);
}

//get attachment by ticket ID
function getByTicketId($ticket_id){
    $stmt = $this->conn->prepare("SELECT * FROM ticket_attachments WHERE ticket_id = :ticket_id ORDER BY created_at DESC");
    $stmt->bindParam(":ticket_id", $ticket_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO:: FETCH_ASSOC);
}
}
?>