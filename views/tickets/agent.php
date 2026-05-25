<?php 
session_start(); 

if (!isset($_SESSION["user"])) { 
    header("Location: ../../middleware/login.php"); 
    exit(); 
} 
require_once "../../models/Ticket.php"; 

$ticketModel = new Ticket(); 
$agent_id = $_SESSION["user"]["id"]; 
Auth::checkRole(['admin','agent']);
/* Handle status update action */ 
if (isset($_POST['update_status'])) { 
    $ticket_id = $_POST['ticket_id']; 
    $status = $_POST['status']; 
    
    $ticketModel->updateTicket($ticket_id, $status); 
    
    header("Location: agent.php"); 
    exit(); 
} 

/* Fetch only assigned tickets */ 
$tickets = $ticketModel->getTicketsForAgent($agent_id); 
?>

<!-- MAIN WRAPPER -->
<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;background:#0f172a;min-height:100vh;padding:30px;color:#e5e7eb;">
    
    <!-- SECTION 1: HEADER -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
        <div>
            <h2 style="margin:0;font-size:22px;font-weight:700;">🧑‍💻 Agent Dashboard</h2>
            <p style="margin:5px 0 0;font-size:13px;color:#94a3b8;">
                Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
            </p>
        </div>
        <a href="/OOP/SupportSystem/middleware/login.php" style="background:#ef4444;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
            Logout
        </a>
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
                <?= count(array_filter($tickets, fn($t)=>$t['status']=='open')) ?>
            </div>
        </div>
        <!-- In Progress -->
        <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
            <div style="font-size:13px;color:#94a3b8;">In Progress</div>
            <div style="font-size:22px;font-weight:700;margin-top:5px;">
                <?= count(array_filter($tickets, fn($t)=>$t['status']=='in_progress')) ?>
            </div>
        </div>
    </div>

    <!-- SECTION 3: ASSIGNED TICKETS TABLE -->
    <div style="background:#1e293b;border-radius:14px;padding:15px;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="text-align:left;border-bottom:1px solid #334155;">
                    <th style="padding:12px;">ID</th>
                    <th style="padding:12px;">Title</th>
                    <th style="padding:12px;">Status</th>
                    <th style="padding:12px;">Created</th>
                    <th style="padding:12px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($tickets as $t): ?>
                    <tr style="border-bottom:1px solid #334155;">
                        
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
                        <td style="padding:12px;">
                            <form method="POST" style="display:flex;gap:8px;align-items:center;">
                                <input type="hidden" name="ticket_id" value="<?= $t['id'] ?>">
                                
                                <select name="status" style="padding:6px;border-radius:8px;border:none;background:#0f172a;color:#e5e7eb;">
                                    <option value="open">Open</option>
                                    <option value="in_progress">In Progress</option>
                                    <option value="closed">Closed</option>
                                </select>
                                
                                <a href="ticket-details.php?id=<?= $t['id'] ?>" style="background:#10b981;color:white;padding:6px 10px;border-radius:8px;text-decoration:none;font-size:12px;font-weight:600;margin-right:6px;">
                                    View
                                </a>

                                     <?php if($t['unread_count'] > 0): ?>
                                    <span style="background:red;color:white;padding:3px 7px;border-radius:999px;font-size:11px;">
                                        <?= $t['unread_count'] ?> new
                                    </span>
                                <?php endif; ?>

                                <button type="submit" name="update_status" style="background:#22c55e;color:#0f172a;border:none;padding:7px 12px;border-radius:8px;font-weight:700;cursor:pointer;">
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
