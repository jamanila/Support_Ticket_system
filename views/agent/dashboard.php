<?php 
session_start(); 

if (!isset($_SESSION["user"])) { 
    header("Location: ../../app/middleware/login.php"); 
    exit(); 
} 
require_once __DIR__ . "/../../app/middleware/Auth.php";
require_once __DIR__ . "/../../app/models/Ticket.php"; 
require_once __DIR__ . "/../../app/models/Notification.php"; 

$ticketModel = new Ticket(); 
$notificationModel = new Notification(); 
$agent_id = $_SESSION["user"]["id"]; 
Auth::checkRole(['admin','agent']);

/* Handle status update action */ 
if (isset($_POST['update_status'])) { 
    $ticket_id = $_POST['ticket_id']; 
    $status = $_POST['status']; 
    
    $ok = $ticketModel->updateTicket($ticket_id, $status);
    if($ok){
        $_SESSION['flash'][] = ['type' => 'success', 'message' => 'Ticket updated'];
    } else {
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Failed to update ticket'];
    }
    header("Location: dashboard.php"); 
    exit(); 
} 

/* Fetch only assigned tickets */ 
$unReadNotifications = $notificationModel->getUnreadTicketNotificationsCount($agent_id);
$unreadTicketAlerts = $notificationModel->getUnreadTicketNotifications($agent_id, 6);
$tickets = $ticketModel->getTicketsForAgent($agent_id); 
?>
<?php require_once __DIR__ . "/../partials/header.php"; ?>

<!-- MAIN WRAPPER -->
<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;background:#0f172a;min-height:100vh;padding:30px;color:#e5e7eb;">
    
    <!-- SECTION 1: HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;flex-wrap:wrap;">
        <div>
            <p style="margin:5px 0 0;font-size:13px;color:#94a3b8;">
                Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
            </p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div id="notificationBell" style="position:relative;padding:10px 14px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:10px;font-size:13px;cursor:pointer;">
                🔔
                <?php if($unReadNotifications > 0): ?>
                    <span style="position:absolute;top:-5px;right:-7px;background:red;color:white;font-size:10px;padding:3px 6px;border-radius:999px;font-weight:700;">
                        <?= $unReadNotifications ?>
                    </span>
                <?php endif; ?>
                <div id="notificationDropdown" style="display:none;position:absolute;right:0;top:calc(100% + 12px);width:320px;max-height:360px;overflow:auto;background:rgba(15,23,42,0.98);border:1px solid rgba(255,255,255,0.1);box-shadow:0 18px 50px rgba(0,0,0,0.35);border-radius:18px;z-index:20;padding:12px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                        <div style="font-size:14px;font-weight:700;color:#e2e8f0;">Unread ticket alerts</div>
                        <span style="font-size:12px;color:#94a3b8;"><?= $unReadNotifications ?> new</span>
                    </div>
                    <?php if(empty($unreadTicketAlerts)): ?>
                        <div style="color:#94a3b8;font-size:13px;padding:14px 12px;">No ticket notification alerts.</div>
                    <?php else: ?>
                        <?php foreach($unreadTicketAlerts as $alert): ?>
                            <a href="../tickets/ticket-details.php?id=<?= htmlspecialchars($alert['ticket_id']) ?>" style="display:block;padding:12px 14px;margin-bottom:10px;border-radius:14px;background:rgba(255,255,255,0.03);text-decoration:none;color:#f8fafc;">
                                <div style="font-size:13px;font-weight:600;line-height:1.4;"><?= htmlspecialchars($alert['message']) ?></div>
                                <div style="font-size:11px;color:#94a3b8;margin-top:6px;"><?= date('M d, Y H:i', strtotime($alert['created_at'])) ?></div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <a href="/OOP/SupportSystem/app/middleware/logout.php" style="background:#ef4444;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
                Logout
            </a>
        </div>
    </div>

    <!-- SECTION 2: STATS CARDS -->
    <div style="display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;">
        <!-- Total Assigned -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">Total Assigned</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count($tickets) ?>
            </div>
        </div>
        <!-- Open -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">Open</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count(array_filter($tickets, function($t){ return $t['status'] == 'open'; })) ?>
            </div>
        </div>
        <!-- In Progress -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">In Progress</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count(array_filter($tickets, function($t){ return $t['status'] == 'in_progress'; })) ?>
            </div>
        </div>
    </div>

    <!-- SECTION 3: ASSIGNED TICKETS TABLE -->
    <div style="background:#1e293b;border-radius:14px;padding:15px;overflow-x:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:16px;">
            <input id="ticketSearch" onkeyup="filterAgentTickets()" placeholder="Search tickets..." style="flex:1;min-width:220px;padding:12px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.12);background:rgba(255,255,255,0.04);color:#e5e7eb;outline:none;">
            <div style="color:#94a3b8;font-size:13px;">Search by ID, title, description, or status</div>
        </div>
        <table class="table-fixed">
            <colgroup>
                <col class="col-id">
                <col>
                <col class="col-status">
                <col class="col-created">
                <col class="col-actions">
            </colgroup>
            <thead>
                <tr style="text-align:left;border-bottom:1px solid #334155;">
                    <th style="padding:12px;">ID</th>
                    <th style="padding:12px;">Title</th>
                    <th style="padding:12px;">Status</th>
                    <th style="padding:12px;">Created</th>
                    <th class="col-actions" style="padding:12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tickets as $t): ?>
                    <tr class="ticket-row" style="border-bottom:1px solid #334155;">
                        
                        <!-- Ticket ID -->
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
                        
                        <!-- Status Badge (Dynamic Styles) -->
                        <td style="padding:12px;">
                            <span style="padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600; background: <?= $t['status']=='closed'?'#14532d': ($t['status']=='in_progress'?'#1e40af':'#92400e') ?>; color:white; text-transform:capitalize;">
                                <?= $t['status'] ?>
                            </span>
                        </td>
                        
                        <!-- Date Created -->
                        <td style="padding:12px;font-size:13px;color:#94a3b8;">
                            <?= date("M d, Y", strtotime($t['created_at'])) ?>
                        </td>
                        
                        <!-- Actions Form Block -->
                        <td class="col-actions">
                            <form method="POST" style="display:flex;gap:8px;align-items:center;justify-content:flex-end;">
                                <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">

                                <select name="status" class="status-select">
                                    <option value="open" <?= $t['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                    <option value="in_progress" <?= $t['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="closed" <?= $t['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                </select>

                                <!-- <a class="action-btn action-btn--view" href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" title="View" aria-label="View ticket <?= $t['id'] ?>" style="background:#3b82f6;color:white;padding:8px 12px;border-radius:10px;text-decoration:none;font-size:12px;font-weight:700;">👁</a> -->
                                <a href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" style="background:#10b981;color:white;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;margin-right:6px;">View</a>
                                <?php if($t['unread_count'] > 0): ?>
                                    <span style="background:#ef4444;color:white;padding:4px 8px;border-radius:999px;font-size:11px;font-weight:700;">NEW <?= $t['unread_count'] ?></span>
                                <?php endif; ?>

                                <button type="submit" name="update_status" style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;border:none;padding:8px 12px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                                    Update
                                </button>
                            </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
(function(){
    const bell = document.getElementById('notificationBell');
    const dropdown = document.getElementById('notificationDropdown');

    if(!bell || !dropdown) return;

    bell.addEventListener('click', function(event){
        event.stopPropagation();
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
    });

    dropdown.addEventListener('click', function(event){
        event.stopPropagation();
    });

    document.addEventListener('click', function(){
        dropdown.style.display = 'none';
    });
})();

(function scheduleAgentReload(){
    const active = document.activeElement;
    const isTyping = active && ['INPUT','TEXTAREA','SELECT'].includes(active.tagName) && active.value && active.value.trim() !== '';

    // Reload the agent dashboard every 5 seconds when not actively typing.
    if (isTyping) {
        setTimeout(scheduleAgentReload, 5000);
        return;
    }

    setTimeout(function(){
        window.location.reload();
    }, 5000);
})();

function filterAgentTickets(){
    const input = document.getElementById('ticketSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.ticket-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}
</script>
