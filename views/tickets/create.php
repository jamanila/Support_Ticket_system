<?php 
session_start(); 

require_once("../../models/Ticket.php"); 
require_once("../../middleware/Auth.php"); 

Auth::checkRole(['admin','user']); 

$ticket = new Ticket(); 

if ($_SERVER["REQUEST_METHOD"] == "POST") { 
    $ticket->title = $_POST['title']; 
    $ticket->description = $_POST['description']; 
    $ticket->status = "open"; 
    $ticket->user_id = $_SESSION["user"]["id"]; 
    
    if ($ticket->createTicket()) { 
        header('Location: index.php'); 
        exit(); 
    } else { 
        echo "Failed to create ticket"; 
    } 
} 
?>

<!-- MAIN WRAPPER (Centered Card Layout) -->
<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif; min-height:100vh;display:flex;align-items:center;justify-content:center; background:radial-gradient(circle at top,#0b1220,#05070f);padding:30px;color:#e5e7eb;"> 
    
    <!-- CARD CONTAINER -->
    <div style="width:100%;max-width:520px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); backdrop-filter:blur(12px); border-radius:18px; padding:28px; box-shadow:0 20px 60px rgba(0,0,0,0.5);"> 
        
        <!-- HEADER -->
        <div style="text-align:center;margin-bottom:25px;"> 
            <div style="font-size:22px;font-weight:800;color:#f8fafc;"> 
                🎫 Create Support Ticket 
            </div> 
            <div style="font-size:13px;color:#94a3b8;margin-top:6px;"> 
                Describe your issue and submit it to the support team 
            </div> 
        </div> 

        <!-- TICKET CREATION FORM -->
        <form method="POST"> 
            
            <!-- INPUT FIELD: TITLE -->
            <label style="display:block;margin-bottom:6px;font-size:13px;color:#cbd5e1;font-weight:600;"> 
                Title 
            </label> 
            <input type="text" name="title" required style="width:100%;padding:12px 14px;margin-bottom:18px; border-radius:12px;border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03); color:#f8fafc; outline:none; transition:0.2s;" onfocus="this.style.border='1px solid #3b82f6'" onblur="this.style.border='1px solid rgba(255,255,255,0.1)'"> 

            <!-- INPUT FIELD: DESCRIPTION -->
            <label style="display:block;margin-bottom:6px;font-size:13px;color:#cbd5e1;font-weight:600;"> 
                Description 
            </label> 
            <textarea name="description" required style="width:100%;padding:12px 14px;margin-bottom:22px; border-radius:12px;border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03); color:#f8fafc; outline:none; height:120px; resize:none; transition:0.2s;" onfocus="this.style.border='1px solid #3b82f6'" onblur="this.style.border='1px solid rgba(255,255,255,0.1)'"></textarea> 

            <!-- ACTION BUTTON -->
            <button type="submit" style="width:100%; padding:12px 14px; background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; border:none; border-radius:12px; font-weight:700; font-size:15px; cursor:pointer; box-shadow:0 10px 25px rgba(37,99,235,0.3); transition:0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'"> 
                Create Ticket 
            </button> 
        
        </form> 
    </div> 
</div>
