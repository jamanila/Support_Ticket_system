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

 $tickets = $ticketModel->getTicketsWithUnreadCountForUser($user_id);
 $unReadNotifications = $notificationModel->getUnreadNotificationCount($user_id);
?>

<?php require_once __DIR__ . "/../partials/header.php"; ?>

<div id="app-refresh" style="font-family:'Segoe UI',Roboto,Arial,sans-serif; background:#0b1220; height:100vh; display:flex; flex-direction:column; color:#e5e7eb; overflow:hidden;">
    
    <div style="position: sticky; top: 0; background: #0b1220; z-index: 30; padding: 30px 30px 10px 30px; flex-shrink: 0;">
        <div style="max-width:1200px; margin:0 auto;">
            
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
                <div>
                    <h2 style="margin:0; font-size:22px; font-weight:700;">🎫 My Tickets</h2>
                    <p style="margin:5px 0 0; font-size:13px; color:#94a3b8;">
                        Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
                    </p>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                    <a href="../tickets/create.php" style="background:#3b82f6; color:white; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px;">
                        + New Ticket
                    </a>
                    <div style="position:relative; padding:10px 14px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:10px; font-size:13px; display:flex; align-items:center; gap:6px;">
                        💬
                        <span style="color:#e5e7eb;">Ticket chats</span>
                        <?php if($unReadNotifications > 0): ?>
                            <span style="position:absolute; top:-5px; right:-7px; background:#22c55e; color:white; font-size:10px; padding:3px 6px; border-radius:999px; font-weight:700;">
                                <?= $unReadNotifications ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <a href="/OOP/SupportSystem/app/middleware/logout.php" style="background:#ef4444; color:white; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:600; font-size:13px;">
                        Logout
                    </a>
                </div>
            </div>

            <div style="display:flex; gap:15px; margin-bottom:20px; flex-wrap:wrap;">
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">Total Tickets</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count($tickets) ?>
                    </div>
                </div>
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">Open</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count(array_filter($tickets, fn($t)=>$t['status']=='open')) ?>
                    </div>
                </div>
                <div style="flex:1; min-width:180px; background:#1e293b; padding:15px; border-radius:12px;">
                    <div style="font-size:13px; color:#94a3b8;">Closed</div>
                    <div style="font-size:22px; font-weight:700; margin-top:5px;">
                        <?= count(array_filter($tickets, fn($t)=>$t['status']=='closed')) ?>
                    </div>
                </div>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; background:#1e293b; padding: 12px 16px; border-radius: 14px 14px 0 0; border-bottom: 1px solid #334155;">
                <input id="ticketSearch" onkeyup="filterUserTickets()" placeholder="Search tickets..." style="flex:1; min-width:220px; padding:10px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.12); background:rgba(255,255,255,0.04); color:#e5e7eb; outline:none; font-size:13px;">
                <div style="color:#94a3b8; font-size:12px;">Search by ID, title, description, status, or agent</div>
            </div>

        </div>
    </div>

    <div style="flex: 1; overflow-y: auto; padding: 0 30px 30px 30px; min-height: 0;">
        <div style="max-width:1200px; margin:0 auto;">
            
            <div style="background:#1e293b; border-radius: 0 0 14px 14px; padding:0 15px 15px 15px; overflow:auto;">
                <table style="width:100%; border-collapse:collapse; min-width:900px;" class="table-fixed">
                    <colgroup>
                        <col style="width: 80px;">
                        <col>
                        <col style="width: 140px;">
                        <col style="width: 140px;">
                        <col style="width: 180px;">
                        <col style="width: 130px;">
                    </colgroup>
                    <thead>
                        <tr style="text-align:left; border-bottom:1px solid #334155; position: sticky; top: 0; background:#1e293b; z-index: 10;">
                            <th style="padding:12px; background:#1e293b;">ID</th>
                            <th style="padding:12px; background:#1e293b;">Title</th>
                            <th style="padding:12px; background:#1e293b;">Status</th>
                            <th style="padding:12px; background:#1e293b;">Created</th>
                            <th style="padding:12px; background:#1e293b;">Assigned Agent</th>
                            <th style="padding:12px; background:#1e293b;">Action</th>
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
                                    <span class="status-badge" style="padding:5px 10px; border-radius:20px; font-size:12px; font-weight:600; color:#fff; display:inline-block; background: <?= $t['status']=='closed'?'#14532d': ($t['status']=='in_progress'?'#1e40af':'#92400e') ?>; text-transform:capitalize;">
                                        <?= htmlspecialchars($t['status']) ?>
                                    </span>
                                </td>
                                
                                <td style="padding:12px; font-size:13px; color:#94a3b8;">
                                    <?= date("M d, Y", strtotime($t['created_at'])) ?>
                                </td>
                                
                                <td style="padding:12px; font-size:13px; color:#e5e7eb;">
                                    <?= htmlspecialchars($t['agent_name'] ?? 'Not Assigned') ?>
                                </td>
                                
                                <td style="padding:12px; display:flex; align-items:center; gap:4px;">
                                    <a class="action-btn action-btn--view" href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" title="View" aria-label="View ticket <?= $t['id'] ?>" style="text-decoration:none; background:rgba(255,255,255,0.05); padding:6px 10px; border-radius:6px; color:#e5e7eb; display:inline-flex; align-items:center; font-size:12px; font-weight:600;">
                                        👁 View<?= (!empty($t['unread_count']) && $t['unread_count'] > 0) ? ' <span style="color:#ff0033; font-weight:900; font-size:13px; margin-left:5px;">' . $t['unread_count'] . '</span>' : '' ?>
                                    </a>
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
function filterUserTickets(){
    const input = document.getElementById('ticketSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.ticket-row');

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        row.style.display = text.includes(filter) ? '' : 'none';
    });
}

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

                    // Re-execute inline scripts from fetched content
                    const scripts = Array.from(newContainer.querySelectorAll('script'));
                    scripts.forEach(s => {
                        const ns = document.createElement('script');
                        if (s.src) ns.src = s.src;
                        ns.text = s.textContent;
                        document.body.appendChild(ns);
                        document.body.removeChild(ns);
                    });
                }
            } catch (e) {
                // silently ignore network errors and retry
            }

            setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
        }

        setTimeout(refresh, AUTO_RELOAD_INTERVAL_MS);
    })();
}
</script>