<?php
http_response_code(403);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>403 Forbidden</title>
</head>

<body style="margin:0;font-family:Segoe UI,Arial,sans-serif;background:#05070f;color:white;display:flex;justify-content:center;align-items:center;height:100vh;">

<div style="text-align:center;max-width:500px;padding:30px;">

    <div style="font-size:80px;font-weight:900;color:#ef4444;">
        403
    </div>

    <h1 style="margin:10px 0;font-size:28px;">
        Forbidden
    </h1>

    <p style="color:#94a3b8;font-size:15px;line-height:1.6;">
        You do not have permission to access this resource.
    </p>

    <button onclick="history.back()"
    style="margin-top:20px;padding:12px 18px;background:#2563eb;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;">
        Go Back
    </button>

</div>

</body>
</html>