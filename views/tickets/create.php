<?php
session_start();

require_once("../../models/Ticket.php");
require_once("../../middleware/Auth.php");

Auth::checkRole(['agent','admin']);

$ticket = new Ticket();

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $ticket->title = $_POST['title'];
    $ticket->description = $_POST['description'];
    $ticket->status = "open";

    // ✅ IMPORTANT: link ticket to logged-in user
    $ticket->user_id = $_SESSION["user"]["id"];

    if($ticket->createTicket()){
        header('Location: index.php');
        exit();
    } else {
        echo "Failed to create ticket";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Ticket</title>
</head>
<body style="font-family: sans-serif; background-color: #f4f7f6; display: flex; flex-direction: column; align-items: center; padding-top: 50px;">

    <div style="background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <h2 style="margin-top: 0; color: #333; text-align: center;">Create Support Ticket</h2>

        <form method="POST">

            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Title</label>
            <input type="text" name="title" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; margin-bottom: 20px;">

            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #555;">Description</label>
            <textarea name="description" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; height: 100px; resize: vertical; margin-bottom: 20px;"></textarea>

            <button type="submit" style="width: 100%; padding: 12px; background-color: #007bff; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 16px;">
                Create Ticket
            </button>

        </form>
    </div>

</body>
</html>
