<?php 
session_start(); 

if (!isset($_SESSION["user"])) { 
    header("Location: ../../app/middleware/login.php"); 
    exit(); 
} 

require_once __DIR__ . "/../../app/middleware/Auth.php";
require_once __DIR__ . "/../../app/models/Ticket.php"; 
require_once __DIR__ . "/../../app/models/Notification.php";
Auth::checkRole(['admin', 'user']);
$ticketModel = new Ticket(); 
$notificationModel = new Notification();
$user_id = $_SESSION["user"]["id"]; 
$unReadNotifications = $notificationModel->getUnreadNotificationCount($user_id);
/* Fetch user tickets */ 
$tickets = $ticketModel->getTicketsForUser($user_id); 
?>
<?php require_once __DIR__ . "/../partials/header.php"; ?>

<!-- MAIN WRAPPER -->
<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;background:#0b1220;min-height:100vh;padding:30px;color:#e5e7eb;">
    
    <!-- SECTION 1: HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <div>
            <h2 style="margin:0;font-size:22px;font-weight:700;">🎫 My Tickets</h2>
            <p style="margin:5px 0 0;font-size:13px;color:#94a3b8;">
                Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <a href="../tickets/create.php" style="background:#3b82f6;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
                + New Ticket
            </a>
            <div style="position:relative;padding:10px 14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:10px;font-size:13px;">
                🔔
                <?php if($unReadNotifications > 0): ?>
                    <span style="position:absolute;top:-5px;right:-7px;background:red;color:white;font-size:10px;padding:3px 6px;border-radius:999px;font-weight:700;">
                        <?= $unReadNotifications ?>
                    </span>
                <?php endif; ?>
            </div>
            <a href="/OOP/SupportSystem/app/middleware/logout.php" style="background:#ef4444;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
                Logout
            </a>
        </div>
    </div>

    <!-- SECTION 2: STATS CARDS -->
    <div style="display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;">
        <!-- Total -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">Total Tickets</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count($tickets) ?>
            </div>
        </div>
        <!-- Open -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">Open</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count(array_filter($tickets, fn($t)=>$t['status']=='open')) ?>
            </div>
        </div>
        <!-- Closed -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">Closed</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count(array_filter($tickets, fn($t)=>$t['status']=='closed')) ?>
            </div>
        </div>
    </div>

    <!-- SECTION 3: USER TICKETS TABLE -->
    <div style="background:#1e293b;border-radius:14px;padding:15px;overflow-x:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
            <input id="ticketSearch" onkeyup="filterUserTickets()" placeholder="Search tickets..." style="flex:1;min-width:220px;padding:12px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.04);color:#e5e7eb;outline:none;">
            <div style="color:#94a3b8;font-size:13px;">Search by ID, title, description, status, or agent</div>
        </div>
        <table class="table-fixed">
            <colgroup>
                <col class="col-id">
                <col>
                <col class="col-status">
                <col class="col-created">
                <col>
                <col class="col-actions">
            </colgroup>
            <thead>
                <tr style="text-align:left;border-bottom:1px solid #334155;">
                    <th style="padding:12px;">ID</th>
                    <th style="padding:12px;">Title</th>
                    <th style="padding:12px;">Status</th>
                    <th style="padding:12px;">Created</th>
                    <th style="padding:12px;">Assigned Agent</th>
                    <th class="col-actions" style="padding:12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tickets as $t): ?>
                    <tr class="ticket-row" style="border-bottom:1px solid #334155;">
                        
                        <!-- ID -->
                        <td style="padding:12px;color:#93c5fd;font-weight:600;">
                            #<?= $t['id'] ?>
                        </td>
                        
                        <!-- Title & Description -->
                        <td style="padding:12px;">
                            <div style="font-weight:600;">
                                <?= htmlspecialchars($t['title']) ?>
                            </div>
                            <div style="font-size:12px;color:#94a3b8;">
                                <?= htmlspecialchars($t['description']) ?>
                            </div>
                        </td>
                        
                        <!-- Status Badge -->
                        <td style="padding:12px;">
                            <span class="status-badge" style="background: <?= $t['status']=='closed'?'#14532d': ($t['status']=='in_progress'?'#1e40af':'#92400e') ?>; text-transform:capitalize;">
                                <?= htmlspecialchars($t['status']) ?>
                            </span>
                        </td>
                        
                        <!-- Date Created -->
                        <td style="padding:12px;font-size:13px;color:#94a3b8;">
                            <?= date("M d, Y", strtotime($t['created_at'])) ?>
                        </td>
                        
                        <!-- Agent Assigned -->
                        <td style="padding:12px;font-size:13px;color:#e5e7eb;">
                            <?= htmlspecialchars($t['agent_name'] ?? 'Not Assigned') ?>
                        </td>
                        
                        <!-- Action Links -->
                        <td class="col-actions">
                            <a class="action-btn action-btn--view" href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" title="View" aria-label="View ticket <?= $t['id'] ?>">👁</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
function filterUserTickets(){
    const input = document.getElementById('ticketSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.ticket-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
