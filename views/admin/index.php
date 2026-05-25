<?php 
session_start(); 

if (!isset($_SESSION["user"])) { 
    header("Location: ../../middleware/login.php"); 
        exit(); 
    } 

    require_once "../../middleware/Auth.php"; 
    require_once "../../models/Ticket.php"; 
    require_once "../../models/Users.php"; 

    Auth::checkRole(["admin"]); 

    $ticketModel = new Ticket();
    $userId = $_SESSION['user']['id'];
    $tickets = $ticketModel->getTicketsWithUnreadCount($userId);

    $UserModel = new Users(); 
    $users = $UserModel->getAllUsers(); 

    // Handle ticket assignment action
    if (isset($_POST['assign_ticket'])) { 
        $ticket_id = $_POST['ticket_id']; 
        $agent_id = $_POST['agent_id']; 
        
        if (!empty($agent_id)) { 
            $ticketModel->assignTicketToAgent($ticket_id, $agent_id); 
        } 
        
        header("Location: index.php"); 
        exit(); 
    } 


    /* ========================= KPI CALCULATIONS ========================= */ 
    $totalTickets = count($tickets); 
    $openTickets = count(array_filter($tickets, fn($t) => $t['status'] === 'open')); 
    $inProgressTickets = count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')); 
    $closedTickets = count(array_filter($tickets, fn($t) => $t['status'] === 'closed')); 
    ?>

<!-- MAIN WRAPPER -->
<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;background:radial-gradient(circle at top,#0b1220,#05070f);min-height:100vh;padding:30px;color:#e5e7eb;">
    
    <!-- SECTION 1: HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <div>
            <h2 style="margin:0;font-size:24px;font-weight:800;">🛠 Admin Control Center</h2>
            <p style="margin:5px 0 0;color:#94a3b8;font-size:13px;">
                Ticket system overview & management
            </p>
        </div>
        <div style="display:flex;gap:10px;align-items:center;">
            <div style="padding:8px 12px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:10px;font-size:13px;">
                👤 <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
            </div>
            <a href="/OOP/SupportSystem/middleware/login.php" style="background:#ef4444;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
                Logout
            </a>
        </div>
    </div>

    <!-- SECTION 2: SEARCH + ACTION BAR -->
    <div style="display:flex;gap:12px;align-items:center;margin-bottom:20px;flex-wrap:wrap;">
        <input type="text" id="ticketSearch" onkeyup="filterTickets()" placeholder="Search tickets..." style="flex:1;min-width:260px;padding:12px 14px;border-radius:12px;border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.03);color:#e5e7eb;outline:none;">
        <a href="../tickets/create.php" style="background:linear-gradient(135deg,#3b82f6,#2563eb);color:white;padding:11px 16px;border-radius:12px; text-decoration:none;font-weight:600;font-size:14px;">
            + New Ticket
        </a>
    </div>

    <!-- SECTION 3: KPI CARDS -->
    <div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:25px;">
        <!-- Total -->
        <div style="flex:1;min-width:200px;background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.2);border-radius:14px;padding:16px;">
            <div style="font-size:13px;color:#93c5fd;">Total Tickets</div>
            <div style="font-size:26px;font-weight:800;margin-top:6px;">
                <?= $totalTickets ?>
            </div>
        </div>
        <!-- Open -->
        <div style="flex:1;min-width:200px;background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.2);border-radius:14px;padding:16px;">
            <div style="font-size:13px;color:#fbbf24;">Open</div>
            <div style="font-size:26px;font-weight:800;margin-top:6px;">
                <?= $openTickets ?>
            </div>
        </div>
        <!-- In Progress -->
        <div style="flex:1;min-width:200px;background:rgba(59,130,246,0.12); border:1px solid rgba(59,130,246,0.2);border-radius:14px;padding:16px;">
            <div style="font-size:13px;color:#60a5fa;">In Progress</div>
            <div style="font-size:26px;font-weight:800;margin-top:6px;">
                <?= $inProgressTickets ?>
            </div>
        </div>
        <!-- Closed -->
        <div style="flex:1;min-width:200px;background:rgba(34,197,94,0.12); border:1px solid rgba(34,197,94,0.2);border-radius:14px;padding:16px;">
            <div style="font-size:13px;color:#34d399;">Closed</div>
            <div style="font-size:26px;font-weight:800;margin-top:6px;">
                <?= $closedTickets ?>
            </div>
        </div>
    </div>

    <!-- SECTION 4: DATA TABLE CONTAINER -->
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08); border-radius:16px;overflow:auto;max-height:600px;backdrop-filter:blur(10px);">
        <table style="width:100%;border-collapse:collapse;min-width:1000px;">
            <thead>
                <tr style="background:rgba(255,255,255,0.04);text-align:left;">
                    <th style="padding:14px;">ID</th>
                    <th style="padding:14px;">Title</th>
                    <th style="padding:14px;">Description</th>
                    <th style="padding:14px;">Created By</th>
                    <th style="padding:14px;">Status</th>
                    <th style="padding:14px;">Created</th>
                    <th style="padding:14px;">Assigned Agent</th>
                    <th style="padding:14px;text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tickets as $t): ?>
                    <tr class="ticket-row" style="border-top:1px solid rgba(255,255,255,0.05);transition:0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.03)'" onmouseout="this.style.background='transparent'">
                        
                        <!-- ID -->
                        <td style="padding:14px;font-weight:700;color:#93c5fd;">
                            <?= $t['id'] ?>
                        </td>
                        
                        <!-- Title -->
                        <td style="padding:14px;font-weight:700;color:#f1f5f9;font-size:14px;">
                            <?= htmlspecialchars($t['title']) ?>
                        </td>
                        
                        <!-- Description -->
                        <td style="padding:14px;color:#94a3b8;font-size:13px;max-width:250px;">
                            <?= htmlspecialchars($t['description']) ?>
                        </td>
                        
                        <!-- Creator -->
                        <td style="padding:14px; color:#f1f5f9;">
                            <?= htmlspecialchars($t['creator_name'] ?? 'Unknown') ?>
                        </td>
                        
                        <!-- Status Badge (Dynamic Styles) -->
                        <td style="padding:14px;">
                            <span style="padding:6px 12px;border-radius:999px;font-size:12px;font-weight:700; background: <?= $t['status']==='closed'?'rgba(34,197,94,0.15)': ($t['status']==='in_progress'?'rgba(59,130,246,0.15)':'rgba(245,158,11,0.15)') ?>; color: <?= $t['status']==='closed'?'#22c55e': ($t['status']==='in_progress'?'#60a5fa':'#fbbf24') ?>; text-transform:capitalize;">
                                <?= htmlspecialchars($t['status']) ?>
                            </span>
                        </td>
                        
                        <!-- Creation Date -->
                        <td style="padding:14px;color:#94a3b8;font-size:13px;">
                            <?= date("M d, Y", strtotime($t['created_at'])) ?>
                        </td>
                        
                        <!-- Assigned Agent / Action Form -->
                        <td style="padding:14px;">
                            <?php $assignedAgent = $t['agent_name'] ?? null; ?>
                            <?php if($assignedAgent): ?>
                                <div style="padding:6px 10px;border-radius:10px;background:rgba(16,185,129,0.15); color:#34d399;font-weight:600;font-size:13px;display:inline-block;">
                                    <?= htmlspecialchars($assignedAgent) ?>
                                </div>
                            <?php else: ?>
                                <form method="POST" style="display:flex;gap:8px;align-items:center;">
                                    <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                    <select name="agent_id" style="padding:7px 10px;border-radius:10px; border:1px solid rgba(255,255,255,0.1); background:#0b1220;color:#e5e7eb;">
                                        <option value="">Select agent</option>
                                        <?php foreach($users as $user): ?>
                                            <?php if($user["role"] == "agent"): ?>
                                                <option value="<?= $user['id'] ?>">
                                                    <?= htmlspecialchars($user['name']) ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="assign_ticket" style="background:#2563eb;color:white;border:none;padding:7px 12px; border-radius:10px;font-size:12px;font-weight:700;cursor:pointer;">
                                        Assign
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                        
                        <!-- Action Row Options -->
                        <td style="padding:14px;text-align:center;white-space:nowrap;">
                            <?php if($t['status'] !== "closed"): ?>
                                <a href="../tickets/update.php?id=<?= $t['id'] ?>" style="background:#3b82f6;color:white;padding:6px 10px;border-radius:8px; text-decoration:none;font-size:12px;font-weight:600;margin-right:6px;">
                                    Close
                                </a>
                                <a href="../tickets/ticket-details.php?id=<?= $t['id'] ?>" style="background:#10b981;color:white;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;margin-right:6px;">
                                    View
                                </a>
                                <?php if($t['unread_count'] > 0): ?>
                                    <span style="background:red;color:white;padding:3px 7px;border-radius:999px;font-size:11px;">
                                        <?= $t['unread_count'] ?> new
                                    </span>
                                <?php else: ?>
                                    <span style="color:#22c55e;font-size:11px;">
                                        read
                                    </span>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <span style="color:#22c55e;font-weight:700;font-size:13px;">
                                    Resolved
                                </span>
                            <?php endif; ?>
                            <a href="delete.php?id=<?= $t['id'] ?>" onclick="return confirm('Are you sure?')" style="border:1px solid #ef4444;color:#ef4444;padding:6px 10px; border-radius:8px;text-decoration:none;font-size:12px;">
                                Delete
                            </a>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- CLIENT RE-FILTER SCRIPT -->
<script>
function filterTickets(){
    const input = document.getElementById('ticketSearch');
    const filter = input.value.toLowerCase();
    const rows = document.querySelectorAll('.ticket-row');
    
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        if(filter.length < 3){
            row.style.display = "";
        } else if(text.includes(filter)){
            row.style.display = "";
        } else {
            row.style.display = "none";
        }
    });
}
</script>
