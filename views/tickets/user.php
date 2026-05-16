<?php
session_start();

if (!isset($_SESSION["user"])) {
    header("Location: ../../middleware/login.php");
    exit();
}

require_once "../../models/Ticket.php";

$ticketModel = new Ticket();

$user_id = $_SESSION["user"]["id"];

/* fetch user tickets */
$tickets = $ticketModel->getTicketsForUser($user_id);
?>

<div style="font-family:'Segoe UI',Roboto,Arial,sans-serif;background:#0b1220;min-height:100vh;padding:30px;color:#e5e7eb;">

<!-- HEADER -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">

    <div>
        <h2 style="margin:0;font-size:22px;font-weight:700;">🎫 My Tickets</h2>
        <p style="margin:5px 0 0;font-size:13px;color:#94a3b8;">
            Welcome, <?= htmlspecialchars($_SESSION["user"]["name"]) ?>
        </p>
    </div>

    <div style="display:flex;gap:10px;align-items:center;">

        <a href="create.php"
           style="background:#3b82f6;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
           + New Ticket
        </a>

        <a href="/OOP/SupportSystem/middleware/login.php"
           style="background:#ef4444;color:white;padding:10px 14px;border-radius:10px;text-decoration:none;font-weight:600;font-size:13px;">
           Logout
        </a>

    </div>

</div>

<!-- STATS -->
<div style="display:flex;gap:15px;margin-bottom:25px;flex-wrap:wrap;">

    <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
        <div style="font-size:13px;color:#94a3b8;">Total Tickets</div>
        <div style="font-size:22px;font-weight:700;margin-top:5px;">
            <?= count($tickets) ?>
        </div>
    </div>

    <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
        <div style="font-size:13px;color:#94a3b8;">Open</div>
        <div style="font-size:22px;font-weight:700;margin-top:5px;">
            <?= count(array_filter($tickets, fn($t)=>$t['status']=='open')) ?>
        </div>
    </div>

    <div style="flex:1;min-width:180px;background:#1e293b;padding:15px;border-radius:12px;">
        <div style="font-size:13px;color:#94a3b8;">Closed</div>
        <div style="font-size:22px;font-weight:700;margin-top:5px;">
            <?= count(array_filter($tickets, fn($t)=>$t['status']=='closed')) ?>
        </div>
    </div>

</div>

<!-- TABLE -->
<div style="background:#1e293b;border-radius:14px;padding:15px;overflow-x:auto;">

<table style="width:100%;border-collapse:collapse;">

<thead>
<tr style="text-align:left;border-bottom:1px solid #334155;">
<th style="padding:12px;">ID</th>
<th style="padding:12px;">Title</th>
<th style="padding:12px;">Status</th>
<th style="padding:12px;">Created</th>
<th style="padding:12px;">Assigned Agent</th>
</tr>
</thead>

<tbody>

<?php foreach($tickets as $t): ?>

<tr style="border-bottom:1px solid #334155;">

<td style="padding:12px;color:#93c5fd;font-weight:600;">#<?= $t['id'] ?></td>

<td style="padding:12px;">
<div style="font-weight:600;"><?= htmlspecialchars($t['title']) ?></div>
<div style="font-size:12px;color:#94a3b8;"><?= htmlspecialchars($t['description']) ?></div>
</td>

<td style="padding:12px;">

<span style="padding:5px 10px;border-radius:999px;font-size:12px;font-weight:600;
background:
<?= 
$t['status']=='closed'?'#14532d':
($t['status']=='in_progress'?'#1e40af':'#92400e')
?>;
color:white;
text-transform:capitalize;">
<?= $t['status'] ?>
</span>

</td>

<td style="padding:12px;font-size:13px;color:#94a3b8;">
<?= date("M d, Y", strtotime($t['created_at'])) ?>
</td>

<td style="padding:12px;font-size:13px;color:#e5e7eb;">
<?= htmlspecialchars($t['agent_name'] ?? 'Not Assigned') ?>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>