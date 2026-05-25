<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 

require_once("../../models/Comment.php"); 
require_once("../../models/Ticket.php"); 
require_once("../../middleware/Auth.php"); 
require_once("../../models/Notification.php");

// ==========================
// AUTH CHECK
// ==========================
if (!isset($_SESSION['user'])) { 
    header("Location: ../errors/401.php");
    exit(); 
}

$user = $_SESSION['user']; 
$userRole = $user['role']; 
$userId = $user['id']; 
$userName = $user['name']; 

// ==========================
// VALIDATE TICKET ID
// ==========================
if (!isset($_GET['id'])) { 
    header("Location: ../errors/404.php");
    exit(); 
}

$ticket_id = (int) $_GET['id']; 

// ==========================
// LOAD TICKET
// ==========================
$ticketModel = new Ticket(); 
$ticket = $ticketModel->getTicketById($ticket_id); 

if (empty($ticket)) { 
    header("Location: ../errors/404.php");
    exit(); 
}

// ==========================
// ACCESS CONTROL
// ==========================
Auth::canAccessTicket($ticket_id);

// ==========================
// MARK AS READ (IMPORTANT FIX)
// ==========================
$markRead = $ticketModel->conn->prepare("
    INSERT INTO ticket_reads (ticket_id, user_id, last_read_at)
    VALUES (:ticket_id, :user_id, NOW())
    ON DUPLICATE KEY UPDATE last_read_at = NOW()
");

$markRead->execute([
    ":ticket_id" => $ticket_id,
    ":user_id" => $userId
]);

// ==========================
// HANDLE COMMENT SUBMISSION
// ==========================
$commentModel = new Comment();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty($_POST['message'])) {
        die('Comment field should not be empty');
    }

    $commentModel->addComment(
        $ticket_id,
        $userId,
        $userRole,
        $_POST['message']
    );

    // ==========================
    // NOTIFICATIONS
    // ==========================
    $notification = new Notification();

    if ($userRole == "user") {

        if (!empty($ticket['assigned_to'])) {
            $notification->createNotification(
                $ticket['assigned_to'],
                $ticket_id,
                "Customer replied to ticket"
            );
        }

    } elseif ($userRole == "agent") {

        $notification->createNotification(
            $ticket['user_id'],
            $ticket_id,
            "Agent replied to your ticket"
        );

    } elseif ($userRole == "admin") {

        $notification->createNotification(
            $ticket['user_id'],
            $ticket_id,
            "Admin replied to your ticket"
        );
    }

    header("Location: ticket-details.php?id=" . $ticket_id);
    exit();
}

// ==========================
// LOAD COMMENTS
// ==========================
$comments = $commentModel->getCommentsByTicket($ticket_id);

?>
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Ticket Conversation</title> 
</head> 
<script>
window.onload = function () {
    const chatBox = document.getElementById("chat-box");
    chatBox.scrollTop = chatBox.scrollHeight;
};
</script>
<body style="margin:0;padding:0;background:#05070f;"> 

    <!-- MAIN WRAPPER (Radial Gradient Background) --> 
        
        <!-- CENTRAL CONTENT CONTAINER --> 
        <div style="width:100%;max-width:950px;display:flex;flex-direction:column;gap:18px;"> 
            
            <!-- SECTION 1: TOP NAVIGATION BAR --> 
            <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;"> 
                <div> 
                    <div style="font-size:26px;font-weight:800;color:white;"> 
                        🎫 Ticket Conversation 
                    </div> 
                    <div style="font-size:13px;color:#94a3b8;margin-top:5px;"> 
                        <?= htmlspecialchars($ticket['title']) ?>
                    </div> 
                </div> 
                <div style="display:flex;gap:10px;align-items:center;"> 
                    <?php if($user['role'] == "admin"): ?>

                    <a href="../admin/index.php"
                    style="display:inline-block;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 14px;border-radius:12px;font-weight:600;cursor:pointer;text-decoration:none;">
                    ← Back
                    </a>

                    <?php elseif($user['role'] == "agent"): ?>

                    <a href="agent.php"
                    style="display:inline-block;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 14px;border-radius:12px;font-weight:600;cursor:pointer;text-decoration:none;">
                    ← Back
                    </a>

                    <?php else: ?>

                    <a href="user.php"
                    style="display:inline-block;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 14px;border-radius:12px;font-weight:600;cursor:pointer;text-decoration:none;">
                    ← Back
                    </a>

                    <?php endif; ?> 
                    <button style="background:#ef4444;border:none;color:white;padding:10px 14px;border-radius:12px;font-weight:700;cursor:pointer;"> 
                        Close Ticket 
                    </button> 
                </div> 
            </div> 
            
            <!-- SECTION 2: TICKET META INFO CARD --> 
            <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);backdrop-filter:blur(12px);border-radius:18px;padding:22px;box-shadow:0 10px 40px rgba(0,0,0,0.35);"> 
                <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;"> 
                    <div> 
                        <div style="font-size:22px;font-weight:800;color:#f8fafc;"> 
                            <!-- Ticket #1024 — Login Authentication Failure --> 
                        </div> 
                        <div style="display:flex;gap:14px;flex-wrap:wrap;margin-top:10px;font-size:13px;color:#94a3b8;"> 
                            <div>👤 
                                Created by:
                                <?php if($userName === $ticket['creator_name']):?>
                                    <div>you</div>
                                    <?php else:?>
                                        <?= htmlspecialchars($ticket["creator_name"]) ?>
                                        <?php endif;?>

                            </div> 
                                <div>
                                    👤 Assigned to:
                                 <?php if(!empty($ticket["agent_name"])):?>
                                    <?= htmlspecialchars($ticket["agent_name"]) ?>
                                    <?php else:?>
                                        <span style="color: red">Not assigned yet</span>
                                        <?php endif;?>
                            </div> 
                                <div> 
                                📅Created by:
                                <?= htmlspecialchars($ticket["created_at"]) ?>
                            </div> 
                        </div> 
                    </div> 
                    <!-- STATUS PILL --> 
                    <div style="padding:8px 16px;border-radius:999px;background:rgba(245,158,11,0.15);color:#fbbf24;font-size:12px;font-weight:800;letter-spacing:1px;"> 
                        <?= htmlspecialchars($ticket['status']) ?> 
                    </div> 
                </div> 
            </div> 

            <!-- SECTION 3: SCROLLABLE CHAT HISTORY --> 
            <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);backdrop-filter:blur(12px);border-radius:18px;padding:20px;height:500px;overflow-y:auto;display:flex;flex-direction:column;gap:18px;box-shadow:0 10px 35px rgba(0,0,0,0.25);"> 
                
                <?php foreach($comments as $comment): ?>
                    <?php if($comment['role'] == 'user'): ?> 
                        <!-- USER MESSAGE (Left Aligned) --> 
                        <div style="display:flex;justify-content:flex-start;"> 
                            <div style="max-width:70%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);padding:14px;border-radius:16px;"> 
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:20px;"> 
                                    <div style="font-size:13px;font-weight:700;color:#cbd5e1;">
                                        <?= htmlspecialchars($comment['name']) ?>
                                    </div> 
                                    <div style="font-size:11px;color:#64748b;">
                                        <?= $comment['created_at'] ?>
                                    </div> 
                                </div> 
                                <div style="font-size:14px;line-height:1.6;color:#e2e8f0;"> 
                                    <?= htmlspecialchars($comment['message']) ?> 
                                </div> 
                            </div> 
                        </div> 

                    <?php elseif($comment['role'] == 'agent'): ?> 
                        <!-- AGENT MESSAGE (Right Aligned) --> 
                        <div style="display:flex;justify-content:flex-end;"> 
                            <div style="max-width:70%;background:rgba(59,130,246,0.14);border:1px solid rgba(59,130,246,0.25);padding:14px;border-radius:16px;"> 
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:20px;"> 
                                    <div style="font-size:13px;font-weight:700;color:#93c5fd;">
                                        <?= htmlspecialchars($comment['name']) ?>
                                    </div> 
                                    <div style="font-size:11px;color:#60a5fa;">
                                        <?= $comment['created_at'] ?>
                                    </div> 
                                </div> 
                                <div style="font-size:14px;line-height:1.6;color:#eff6ff;"> 
                                    <?= htmlspecialchars($comment['message']) ?> 
                                </div> 
                            </div> 
                        </div> 

                    <?php else: ?> 
                        <!-- ADMIN SYSTEM MESSAGE (Center Aligned Notice) -->
                        <div style="align-self:center;max-width:80%;background:rgba(26, 209, 93, 0.15);padding:10px 14px;border-radius:14px;border:1px solid rgba(34,197,94,0.25);text-align:center;"> 
                            <div style="font-size:12px;color:#34d399;margin-bottom:4px;"> 
                                <?= htmlspecialchars($comment['name']) ?> (admin) 
                            </div> 
                            <div style="font-size:14px;color:#e5e7eb;"> 
                                <?= htmlspecialchars($comment['message']) ?> 
                            </div> 
                            <div style="font-size:11px;color:#94a3b8;margin-top:6px;"> 
                                <?= $comment['created_at'] ?> 
                            </div> 
                        </div> 
                    <?php endif; ?> 
                    
                <?php endforeach; ?> 
                
            </div> 

            <!-- SECTION 4: REPLY FORM --> 
            <form method="POST" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);padding:16px;border-radius:18px;display:flex;flex-direction:column;gap:14px;"> 
                
                <!-- Hidden Ticket Identification --> 
                <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>"> 
                
                <!-- Reply Text Input Area --> 
                <textarea name="message" placeholder="Type your reply..." required style="width:100%;height:110px;resize:none;border-radius:16px;padding:14px 16px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;font-size:14px;outline:none;box-sizing:border-box;"></textarea> 
                
                <!-- Footer Controls Layout --> 
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;"> 
                    <!-- Info Notice Tag --> 
                    <div style="font-size:12px;color:#64748b;"> 
                        Replies are visible to ticket owner and assigned agents 
                    </div> 
                    <!-- Action Buttons Group --> 
                    <div style="display:flex;gap:10px;"> 
                        <button type="button" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:12px 16px;border-radius:12px;font-weight:700;cursor:pointer;"> 
                            Attach File 
                        </button> 
                        <button type="submit" name="send_comment" style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;padding:12px 20px;border-radius:12px;font-weight:800;cursor:pointer;box-shadow:0 10px 25px rgba(37,99,235,0.35);"> 
                            Send Reply 
                        </button> 
                    </div> 
                </div> 
            </form> 

        </div> 
    </div> 
</body> 
</html>
