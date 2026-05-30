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

<div id="app-refresh" style="font-family:'Segoe UI',Roboto,Arial,sans-serif; background:#0f172a; height:100vh; display:flex; flex-direction:column; color:#e5e7eb; overflow:hidden;">
    
    <div style="position: sticky; top: 0; background: #0f172a; z-index: 30; padding: 30px 30px 10px 30px; flex-shrink: 0;">
        <div style="max-width:1200px; margin:0 auto;">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap;">
                <div>
                    <h2 style="margin:0; font-size:22px; font-weight:700;">👨‍💻 Agent Workspace</h2>
                    <p style="margin:5px 0 0; font-size:13px; color:#94a3b8;">
                        Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
                    </p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <div id="notificationBell" style="position:relative; padding:10px 14px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:10px; font-size:13px; cursor:pointer;">
                        🔔
                        <?php if($unReadNotifications > 0): ?>
                            <span style="position:absolute; top:-5px; right:-7px; background:red; color:white; font-size:10px; padding:3px 6px; border-radius:999px; font-weight:700;">
                                <?= $unReadNotifications ?>
                            </span>
                        <?php endif; ?>
                        
                        <div id="notificationDropdown" style="display:none; position:absolute; right:0; top:calc(100% + 12px); width:320px; max-height:360px; overflow:auto; background:rgba(15,23,42,0.98); border:1px solid rgba(255,255,255,0.1); box-shadow:0 18px 50px rgba(0,0,0,0.35); border-radius:18px; z-index:40; padding:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                <div style="font-size:14px; font-weight:700; color:#e2e8f0;">Unread ticket alerts</div>
                                <span style="font-size:12px; color:#94a3b8;"><?= $unReadNotifications ?> new</span>
                            </div>
                            <?php if(empty($unreadTicketAlerts)): ?>
                                <div style="color:#94a3b8; font-size:13px; padding:14px 12px;">No ticket notification alerts.</div>
                            <?php else: ?>
                                <?php foreach($unreadTicketAlerts as $alert): ?>
                                    <a href="../tickets/ticket-details.php?id=<?= htmlspecialchars($alert['ticket_id']) ?>" style="display:block; padding:12px 14px; margin-bottom:10px; border-radius:14px; background:rgba(255,255,255,0.03); text-decoration:none; color:#f8fafc;">
                                        <div style="font-size:13px; font-weight:600; line-height:1.4;"><?= htmlspecialchars($alert['message']) ?></div>
                                        <div style="font-size:11px; color:#94a3b8; margin-top:6px;"><?= date('M d, Y H:i', strtotime($alert['created_at'])) ?></div>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <a href="/OOP/SupportSystem/app/middleware/logout.php" style="background:#ef4444; color:white; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px;">
                        Logout
                    </a>
                </div>
            </div>

            <div style="display:flex; gap:15px; margin-bottom:20px; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">Total Assigned</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count($tickets) ?>
                    </div>
                </div>
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">Open</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count(array_filter($tickets, function($t){ return $t['status'] == 'open'; })) ?>
                    </div>
                </div>
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">In Progress</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count(array_filter($tickets, function($t){ return $t['status'] == 'in_progress'; })) ?>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; background:#1e293b; padding: 12px 16px; border-radius: 14px 14px 0 0; border-bottom: 1px solid #334155;">
                <input id="ticketSearch" onkeyup="filterAgentTickets()" placeholder="Search tickets..." style="flex:1; min-width:220px; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#e5e7eb; outline:none; font-size:13px;">
                <div style="color:#94a3b8; font-size:12px;">Search by ID, title, description, or status</div>
            </div>

        </div>
    </div>

    <div style="flex: 1; overflow-y: auto; padding: 0 30px 30px 30px; min-height: 0;">
        <div style="max-width:1200px; margin:0 auto;">
            
            <div style="background:#1e293b; border-radius: 0 0 14px 14px; padding: 0 15px 15px 15px; overflow:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:900px;" class="table-fixed">
                    <colgroup>
                        <col style="width: 80px;">
                        <col>
                        <col style="width: 140px;">
                        <col style="width: 140px;">
                        <col style="width: 320px;">
                    </colgroup>
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid #334155; position: sticky; top: 0; background:#1e293b; z-index: 10;">
                            <th style="padding:12px; background:#1e293b;">ID</th>
                            <th style="padding:12px; background:#1e293b;">Title</th>
                            <th style="padding:12px; background:#1e293b;">Status</th>
                            <th style="padding:12px; background:#1e293b;">Created</th>
                            <th style="padding:12px; text-align:right; background:#1e293b;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tickets as $t): ?>
                            <tr class="ticket-row" style="border-bottom:1px solid #334155;">
                                
                                <td style="padding:12px; color:#93c5fd; font-weight:600;">
                                    #<?= $t['id'] ?>
                                </td>
                                
                                <td style="padding:12px;">
                                    <div style="font-weight:600;">
                                        <?= htmlspecialchars($t['title']) ?>
                                    </div>
                                    <div style="font-size:12px; color:#94a3b8;">
                                        <?= htmlspecialchars($t['description']) ?>
                                    </div>
                                </td>
                                
                                <td style="padding:12px;">
                                    <span style="padding:5px 10px; border-radius:20px; font-size:12px; font-weight:600; background: <?= $t['status']=='closed'?'#14532d': ($t['status']=='in_progress'?'#1e40af':'#92400e') ?>; color:white; text-transform:capitalize; display:inline-block;">
                                        <?= htmlspecialchars($t['status']) ?>
                                    </span>
                                </td>
                                
                                <td style="padding:12px; font-size:13px; color:#94a3b8;">
                                    <?= date("M d, Y", strtotime($t['created_at'])) ?>
                                </td>
                                
                                <td style="padding:12px;">
                                    <form method="POST" style="display:flex; gap:8px; align-items:center; justify-content:flex-end;">
                                        <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">

                                        <select name="status" style="background:#0f172a; color:#e5e7eb; border:1px solid rgba(255,255,255,0.1); padding:6px 10px; border-radius:8px; font-size:12px; outline:none;" class="status-select">
                                            <option value="open" <?= $t['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                            <option value="in_progress" <?= $t['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="closed" <?= $t['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                        </select>

                                        <a href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" style="background:#10b981; color:white; padding:6px 10px; border-radius:8px; text-decoration:none; font-size:12px; font-weight:600; display:inline-flex; align-items:center;">
                                            View<?= (isset($t['unread_count']) && $t['unread_count'] > 0) ? ' <span style="color:#ff0033; font-weight:900; font-size:13px; margin-left:4px;">' . $t['unread_count'] . '</span>' : '' ?>
                                        </a>

                                        <button type="submit" name="update_status" style="background:linear-gradient(135deg,#3b82f6,#2563eb); color:white; border:none; padding:7px 12px; border-radius:8px; font-size:12px; font-weight:700; cursor:pointer;">
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
    </div>
</div>

<script>
// Notification Dropdown Component Handler Initialization
(function initNotificationComponent(){
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

// Real-Time Table Client-Side Filter Utility
function filterAgentTickets(){
    const input = document.getElementById('ticketSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.ticket-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

// Background Dynamic State Syncer (Polled Dom Diff Architecture)
if (typeof window.refreshApplied === 'undefined') {
    window.refreshApplied = true;

    (function scheduleBackgroundRefresh(){
        const AUTO_RELOAD_INTERVAL_MS = 5000;

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
                    current.innerHTML = newContainer.innerHTML;
                    
                    // Reinitialize dropdown triggers instantly after content injection
                    initNotificationComponent();

                    const scripts = Array.from(newContainer.querySelectorAll('script'));
                    scripts.forEach(s => {
                        if(!s.textContent.includes('scheduleBackgroundRefresh')){
                            const ns = document.createElement('script');
                            if (s.src) ns.src = s.src;
                            ns.text = s.textContent;
                            document.body.appendChild(ns);
                            document.body.removeChild(ns);
                        }
                    });
                }
            } catch (e) {}

            setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
        }

        setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
    })();
}
</script>