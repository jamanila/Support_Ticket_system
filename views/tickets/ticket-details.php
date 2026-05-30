<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); 

require_once(__DIR__ . "/../../app/models/Comment.php"); 
require_once(__DIR__ . "/../../app/models/Ticket.php"); 
require_once(__DIR__ . "/../../app/models/Attachment.php");
require_once(__DIR__ . "/../../app/middleware/Auth.php"); 
require_once(__DIR__ . "/../../app/models/Notification.php");
require_once(__DIR__ . "/../../app/models/Users.php");

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
// MARK RELATED NOTIFICATIONS AS READ
// ==========================
$notification = new Notification();
$notification->markTicketNotificationsAsRead($userId, $ticket_id);

// ==========================
// MARK TICKET AS READ
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
        $_SESSION['flash'][] = [
            'type' => 'error',
            'message' => 'Reply cannot be empty'
        ];

        header("Location: ticket-details.php?id=" . $ticket_id);
        exit();
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
    $usersModel = new Users();

    if ($userRole == "user") {

        if (!empty($ticket['assigned_to'])) {

            $notification->createNotification(
                $ticket['assigned_to'],
                $ticket_id,
                "Customer replied to ticket #{$ticket_id}"
            );

        } else {

            $admins = $usersModel->getAdmins();

            foreach ($admins as $admin) {

                $notification->createNotification(
                    $admin['id'],
                    $ticket_id,
                    "New message from customer on ticket #{$ticket_id}"
                );
            }
        }

    } elseif ($userRole == "agent") {

        $notification->createNotification(
            $ticket['user_id'],
            $ticket_id,
            "Agent replied to your ticket #{$ticket_id}"
        );

    } elseif ($userRole == "admin") {

        $notification->createNotification(
            $ticket['user_id'],
            $ticket_id,
            "Admin replied to your ticket #{$ticket_id}"
        );

        if (
            !empty($ticket['assigned_to']) &&
            $ticket['assigned_to'] != $ticket['user_id']
        ) {

            $notification->createNotification(
                $ticket['assigned_to'],
                $ticket_id,
                "Admin replied to ticket #{$ticket_id} assigned to you"
            );
        }
    }

    $_SESSION['flash'][] = [
        'type' => 'success',
        'message' => 'Reply posted successfully'
    ];

    header("Location: ticket-details.php?id=" . $ticket_id);
    exit();
}

// ==========================
// LOAD COMMENTS
// ==========================
$comments = $commentModel->getCommentsByTicket($ticket_id);

// ==========================
// LOAD ATTACHMENTS
// ==========================
$attachmentModel = new Attachment();
$attachments = $attachmentModel->getByTicketId($ticket_id);

?>

<!DOCTYPE html> 
<html lang="en"> 

<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Ticket Conversation</title> 
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #090d16;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.1) !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        .btn-danger:hover {
            background: #dc2626 !important;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg,#2563eb,#1d4ed8) !important;
        }
        .input-focus:focus {
            border-color: rgba(59,130,246,0.6) !important;
            background: rgba(255,255,255,0.06) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
        /* Custom dynamic scrollbar for modern chat look */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 99px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }
    </style>
</head> 

<script>
window.onload = function () {
    const chatBox = document.getElementById("chat-box");
    const AUTO_RELOAD_INTERVAL_MS = 5000; // Reload every 5 seconds

    if (chatBox) {
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    function isUserTyping() {
        const active = document.activeElement;
        if (!active) return false;
        const tag = active.tagName;
        if (tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT') return true;
        if (active.isContentEditable) return true;
        return false;
    }

    async function refresh() {
        if (isUserTyping()) {
            setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
            return;
        }

        try {
            const res = await fetch(window.location.href, { cache: 'no-store' });
            const text = await res.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(text, 'text/html');
            const newContainer = doc.getElementById('app-refresh');
            const current = document.getElementById('app-refresh');
            if (newContainer && current) {
                // Read current scroll positioning before injection
                const wasAtBottom = chatBox && (chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 40);

                current.innerHTML = newContainer.innerHTML;
                
                // Keep viewport scrolled downward if user was monitoring old history updates
                const newChatBox = document.getElementById("chat-box");
                if (newChatBox && wasAtBottom) {
                    newChatBox.scrollTop = newChatBox.scrollHeight;
                }

                const scripts = Array.from(newContainer.querySelectorAll('script'));
                scripts.forEach(s => {
                    const ns = document.createElement('script');
                    if (s.src) ns.src = s.src;
                    ns.text = s.textContent;
                    document.body.appendChild(ns);
                    document.body.removeChild(ns);
                });
            }
        } catch (e) {}

        setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
    }

    setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
};
</script>

<body> 

<?php require_once __DIR__ . "/../partials/header.php"; ?>

<div style="height: 100vh; display: flex; justify-content: center; align-items: stretch; padding: 20px; box-sizing: border-box;">
    <div id="app-refresh" style="width: 100%; max-width: 1000px; display: flex; flex-direction: column; gap: 16px; height: 100%;">

        <div style="flex-shrink: 0; display: flex; flex-direction: column; gap: 16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;border-bottom:1px solid rgba(255,255,255,0.05);padding-bottom:10px;"> 
                <div> 
                    <div style="font-size:24px;font-weight:800;color:white;letter-spacing:-0.5px;display:flex;align-items:center;gap:10px;"> 
                        <span>🎫</span> Ticket Conversation 
                    </div> 
                    <div style="font-size:14px;color:#94a3b8;margin-top:4px;font-weight:400;"> 
                        <?= htmlspecialchars($ticket['title']) ?>
                    </div> 
                </div> 

                <div style="display:flex;gap:12px;align-items:center;"> 
                    <?php if($user['role'] == "admin"): ?>
                        <a href="../admin/index.php" class="btn-secondary"
                        style="display:inline-block;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.2s ease;">
                            &larr; Back
                        </a>
                    <?php elseif($user['role'] == "agent"): ?>
                        <a href="agent.php" class="btn-secondary"
                        style="display:inline-block;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.2s ease;">
                            &larr; Back
                        </a>
                    <?php else: ?>
                        <a href="user.php" class="btn-secondary"
                        style="display:inline-block;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);color:#e5e7eb;padding:10px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;text-decoration:none;transition:all 0.2s ease;">
                            &larr; Back
                        </a>
                    <?php endif; ?>

                    <button class="btn-danger" style="background:#ef4444;border:none;color:white;padding:10px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s ease;box-shadow:0 4px 12px rgba(239,68,68,0.2);"> 
                        Close Ticket
                    </button> 
                </div> 
            </div> 

            <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);backdrop-filter:blur(16px);border-radius:16px;padding:16px;box-shadow:0 4px 30px rgba(0,0,0,0.2);"> 
                <div style="display:flex;justify-content:space-between;align-items:center;gap:20px;flex-wrap:wrap;"> 
                    <div> 
                        <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:13px;color:#94a3b8;"> 
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="opacity:0.7;">👤 Creator:</span>
                                <strong style="color:#f1f5f9;"><?= htmlspecialchars($ticket['creator_name']) ?></strong>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="opacity:0.7;">👨‍💻 Assigned to:</span>
                                <strong style="color:#f1f5f9;">
                                    <?php if(!empty($ticket["agent_name"])): ?>
                                        <?= htmlspecialchars($ticket["agent_name"]) ?>
                                    <?php else: ?>
                                        <span style="color:#f87171;font-weight:500;background:rgba(248,113,113,0.1);padding:2px 8px;border-radius:6px;">Unassigned</span>
                                    <?php endif; ?>
                                </strong>
                            </div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="opacity:0.7;">📅 Date:</span>
                                <span style="color:#f1f5f9;"><?= htmlspecialchars($ticket["created_at"]) ?></span>
                            </div>
                        </div>
                    </div>

                    <div style="padding:6px 14px;border-radius:8px;background:rgba(245,158,11,0.12);color:#fbbf24;border:1px solid rgba(245,158,11,0.2);font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;"> 
                        <?= htmlspecialchars($ticket['status']) ?>
                    </div>
                </div>
            </div>
        </div>

        <div style="flex: 1; min-height: 0; background:rgba(255,255,255,0.01); border:1px solid rgba(255,255,255,0.05); border-radius:24px; overflow:hidden; box-shadow:0 20px 50px rgba(0,0,0,0.3); display: flex; flex-direction: column;">
            
            <div id="chat-box" class="custom-scrollbar" style="flex: 1; overflow-y:auto; padding:30px; display:flex; flex-direction:column; gap:24px; background:rgba(10,15,30,0.4); box-sizing:border-box;">

                <div style="display:flex;justify-content:flex-start;width:100%;">
                    <div style="max-width:80%;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);padding:20px;border-radius:4px 20px 20px 20px;box-shadow:0 4px 15px rgba(0,0,0,0.15);">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:30px;">
                            <div style="font-size:13px;font-weight:700;color:#f1f5f9;display:flex;align-items:center;gap:6px;">
                                <div style="width:8px;height:8px;border-radius:50%;background:#10b981;"></div>
                                <?= htmlspecialchars($ticket['creator_name']) ?> <span style="font-weight:400;color:#64748b;font-size:11px;">(Author)</span>
                            </div>
                            <div style="font-size:11px;color:#64748b;font-weight:500;">
                                <?= $ticket['created_at'] ?>
                            </div>
                        </div>

                        <div style="font-size:16px;font-weight:700;color:white;margin-bottom:12px;letter-spacing:-0.2px;">
                            📌 <?= htmlspecialchars($ticket['title']) ?>
                        </div>

                        <?php if(!empty($attachments)): ?>
                            <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
                                <?php foreach($attachments as $file): ?>
                                    <?php
                                        $extension = strtolower(pathinfo($file['file_name'], PATHINFO_EXTENSION));
                                        $isImage = in_array($extension, ['jpg','jpeg','png','gif','webp']);
                                    ?>
                                    <?php if($isImage): ?>
                                        <div style="position:relative;border-radius:10px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);transition:transform 0.2s;">
                                            <a href="/OOP/SupportSystem/<?= htmlspecialchars($file['file_path']) ?>" target="_blank" style="display:block;">
                                                <img src="/OOP/SupportSystem/<?= htmlspecialchars($file['file_path']) ?>" style="width:160px;height:110px;object-fit:cover;display:block;">
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <a href="/OOP/SupportSystem/<?= htmlspecialchars($file['file_path']) ?>" target="_blank" 
                                           style="display:inline-flex;align-items:center;gap:6px;padding:8px 14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:8px;color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;transition:all 0.2s;">
                                            <span>📎</span> <?= htmlspecialchars($file['file_name']) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="font-size:14px;line-height:1.6;color:#cbd5e1;word-break:break-word;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($ticket['description'])) ?></div>
                    </div>
                </div>

                <?php foreach($comments as $comment): ?>
                    <?php if($comment['role'] == 'user'): ?>
                        <div style="display:flex;justify-content:flex-start;width:100%;">
                            <div style="max-width:75%;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);padding:16px;border-radius:4px 18px 18px 18px;box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:30px;">
                                    <div style="font-size:13px;font-weight:700;color:#cbd5e1;"><?= htmlspecialchars($comment['name']) ?></div>
                                    <div style="font-size:11px;color:#64748b;"><?= $comment['created_at'] ?></div>
                                </div>
                                <div style="font-size:14px;line-height:1.6;color:#e2e8f0;word-break:break-word;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($comment['message'])) ?></div>
                            </div>
                        </div>

                    <?php elseif($comment['role'] == 'agent'): ?>
                        <div style="display:flex;justify-content:flex-end;width:100%;">
                            <div style="max-width:75%;background:rgba(37,99,235,0.12);border:1px solid rgba(59,130,246,0.25);padding:16px;border-radius:18px 4px 18px 18px;box-shadow:0 4px 15px rgba(37,99,235,0.05);">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;gap:30px;">
                                    <div style="font-size:13px;font-weight:700;color:#93c5fd;display:flex;align-items:center;gap:6px;">
                                        <span style="font-size:12px;">⚡</span> <?= htmlspecialchars($comment['name']) ?> <span style="font-weight:400;color:#60a5fa;font-size:11px;">(Support Agent)</span>
                                    </div>
                                    <div style="font-size:11px;color:#60a5fa;opacity:0.8;"><?= $comment['created_at'] ?></div>
                                </div>
                                <div style="font-size:14px;line-height:1.6;color:#eff6ff;word-break:break-word;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($comment['message'])) ?></div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div style="display:flex;justify-content:center;width:100%;margin:6px 0;">
                            <div style="width:100%;max-width:85%;background:rgba(16,185,129,0.06);padding:14px 20px;border-radius:14px;border:1px solid rgba(16,185,129,0.18);box-shadow:0 4px 12px rgba(0,0,0,0.1);">
                                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;gap:20px;border-bottom:1px solid rgba(16,185,129,0.1);padding-bottom:6px;">
                                    <div style="font-size:12px;color:#34d399;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;display:flex;align-items:center;gap:6px;">
                                        🛡️ <?= htmlspecialchars($comment['name']) ?> <span style="opacity:0.7;font-weight:400;text-transform:none;">(Administrator)</span>
                                    </div>
                                    <div style="font-size:11px;color:#94a3b8;"><?= $comment['created_at'] ?></div>
                                </div>
                                <div style="font-size:14px;line-height:1.6;color:#e5e7eb;word-break:break-word;white-space:pre-wrap;"><?= nl2br(htmlspecialchars($comment['message'])) ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>

            </div>

            <div style="flex-shrink: 0; background:rgba(255,255,255,0.02); border-top:1px solid rgba(255,255,255,0.05); padding:24px;">
                <form method="POST" style="display:flex;flex-direction:column;gap:16px;margin:0;">
                    <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">

                    <div style="position:relative;width:100%;">
                        <textarea 
                            name="message"
                            class="input-focus"
                            placeholder="Type your reply message here..."
                            required
                            style="width:100%;height:100px;resize:none;border-radius:14px;padding:16px;background:rgba(0,0,0,0.2);border:1px solid rgba(255,255,255,0.08);color:#f1f5f9;font-size:14px;line-height:1.5;outline:none;box-sizing:border-box;transition:all 0.2s ease;"
                        ></textarea>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
                        <div style="font-size:12px;color:#64748b;display:flex;align-items:center;gap:6px;">
                            <span style="display:inline-block;width:6px;height:6px;background:#64748b;border-radius:50%;"></span>
                            Replies are strictly visible to the ticket owner and assigned internal staff.
                        </div>

                        <div style="display:flex;gap:12px;align-items:center;">
                            <button 
                                type="button"
                                class="btn-secondary"
                                style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);color:#e5e7eb;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s ease;display:flex;align-items:center;gap:6px;"
                            >
                                📎 Attach File
                            </button>

                            <button 
                                type="submit"
                                name="send_comment"
                                class="btn-primary"
                                style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;padding:12px 24px;border-radius:12px;font-size:14px;font-weight:700;cursor:pointer;transition:all 0.2s ease;box-shadow:0 10px 20px rgba(37,99,235,0.2);"
                            >
                                Send Reply
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>

    </div>
</div>

</body>
</html>