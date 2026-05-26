<?php
http_response_code(401);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>401 Unauthorized</title>
</head>

<body style="margin:0;font-family:Segoe UI,Arial,sans-serif;background:#05070f;color:white;display:flex;justify-content:center;align-items:center;height:100vh;">

<div style="text-align:center;max-width:500px;padding:30px;">

    <div style="font-size:80px;font-weight:900;color:#f59e0b;">
        401
    </div>

    <h1 style="margin:10px 0;font-size:28px;">
        Unauthorized
    </h1>

    <p style="color:#94a3b8;font-size:15px;line-height:1.6;">
        You must log in before accessing this page.
    </p>

    <a href="/OOP/SupportSystem/app/middleware/login.php"
    style="display:inline-block;margin-top:20px;padding:12px 18px;background:#2563eb;color:white;text-decoration:none;border-radius:10px;font-weight:700;">
        Go to Login
    </a>

</div>

</body>
</html>