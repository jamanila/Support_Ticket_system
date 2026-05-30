<?php
session_start();
require_once __DIR__ . "/../../app/models/Users.php";

$user = new Users();

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $email = $_POST["email"];
    $password = $_POST["password"];

    $foundUser = $user->login($email);

    if($foundUser){

        if(password_verify($password, $foundUser["password"])){

            $_SESSION["user"] = [
                "id" => $foundUser["id"],
                "name" => $foundUser["name"],
                "email" => $foundUser["email"],
                "role" => $foundUser["role"]
            ];

            // success flash
            $_SESSION['flash'][] = ['type' => 'success', 'message' => 'Logged in successfully'];

            if($foundUser["role"] == "admin"){
                header("Location: /OOP/SupportSystem/views/admin/index.php");
                exit();
            }elseif($foundUser["role"] == "agent"){
                header("Location: /OOP/SupportSystem/views/tickets/agent.php");
                exit();
            }elseif($foundUser["role"] == "user"){
                header("Location: /OOP/SupportSystem/views/tickets/user.php");
                exit();
            }
        } else {
            $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Invalid credentials'];
            header("Location: login.php");
            exit();
        }
    }
    else{
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'No user found with that email'];
        header("Location: login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Support System</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #090d16;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg,#2563eb,#1d4ed8) !important;
        }
        .btn-secondary:hover {
            background: rgba(255,255,255,0.08) !important;
            border-color: rgba(255,255,255,0.15) !important;
        }
        .input-focus:focus {
            border-color: rgba(59,130,246,0.6) !important;
            background: rgba(255,255,255,0.06) !important;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.15);
        }
    </style>
</head>
<body>

    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(16px); padding: 40px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); width: 100%; max-width: 380px; box-sizing: border-box;">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="font-size: 32px; margin-bottom: 8px;">🎫</div>
            <h2 style="margin: 0; color: white; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Welcome Back</h2>
            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">Sign in to manage your support tickets</p>
        </div>

        <?php if (!empty($_SESSION['flash'])): ?>
            <?php foreach ($_SESSION['flash'] as $key => $flash): ?>
                <?php if ($flash['type'] === 'error'): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #f87171; font-size: 14px; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-weight: 500;">
                        ❌ <?= htmlspecialchars($flash['message']) ?>
                    </div>
                    <?php unset($_SESSION['flash'][$key]); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" style="margin: 0;">

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">Email Address</label>
                <input type="email" name="email" required placeholder="name@company.com" class="input-focus"
                    style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; transition: all 0.2s ease;">
            </div>

            <div style="margin-bottom: 24px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="input-focus"
                    style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; transition: all 0.2s ease;">
            </div>

            <button type="submit" class="btn-primary"
                style="width: 100%; padding: 14px; background: linear-gradient(135deg,#3b82f6,#2563eb); border: none; border-radius: 12px; color: white; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 10px 20px rgba(37,99,235,0.15); margin-bottom: 20px;">
                Log In
            </button>

            <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <span style="color: #64748b; font-size: 14px;">Don't have an account?</span>
                <a href="register.php" class="btn-secondary" 
                   style="display: inline-block; margin-left: 4px; color: #60a5fa; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.2s ease;"
                   onmouseover="this.style.color='#93c5fd'" 
                   onmouseout="this.style.color='#60a5fa'">
                    Sign up
                </a>
            </div>

        </form>
        
    </div>

</body>
</html>