<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . "/../../app/models/Users.php";

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $user = new Users();

    $user->name = $_POST['name'];
    $user->email = $_POST['email'];

    // HASH PASSWORD
    $user->password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    $user->role = $_POST['role'];

    if($user->createUser()){
        $_SESSION['flash'][] = ['type' => 'success', 'message' => 'Account created. Please log in.'];
        header("Location: /OOP/SupportSystem/app/middleware/login.php");
        exit();

    } else {
        $_SESSION['flash'][] = ['type' => 'error', 'message' => 'Failed to create account'];
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en" style="height: 100%;">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Support System</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
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

    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(16px); padding: 40px; border-radius: 24px; box-shadow: 0 20px 50px rgba(0,0,0,0.4); width: 100%; max-width: 420px; box-sizing: border-box;">
        
        <div style="text-align: center; margin-bottom: 28px;">
            <div style="font-size: 32px; margin-bottom: 8px;">🎫</div>
            <h2 style="margin: 0; color: white; font-size: 24px; font-weight: 800; letter-spacing: -0.5px;">Get Started</h2>
            <p style="margin: 6px 0 0 0; color: #64748b; font-size: 14px;">Create your system dashboard account</p>
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
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">Full Name</label>
                <input type="text" name="name" required placeholder="John Doe" class="input-focus"
                       style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; transition: all 0.2s ease;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">Email Address</label>
                <input type="email" name="email" required placeholder="name@company.com" class="input-focus"
                       style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; transition: all 0.2s ease;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="input-focus"
                       style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; transition: all 0.2s ease;">
            </div>

            <div style="margin-bottom: 28px; position: relative;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #94a3b8; font-size: 13px;">User Role</label>
                <div style="position: relative;">
                    <select name="role" class="input-focus"
                            style="width: 100%; padding: 14px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; box-sizing: border-box; color: #f1f5f9; font-size: 15px; outline: none; appearance: none; transition: all 0.2s ease; cursor: pointer;">
                        <option value="user" style="background: #111827; color: white;">User (Customer)</option>
                        <option value="agent" style="background: #111827; color: white;">Agent (Support Staff)</option>
                        <option value="admin" style="background: #111827; color: white;">Admin (Management)</option>
                    </select>
                    <div style="position: absolute; top: 50%; right: 16px; transform: translateY(-50%); color: #64748b; pointer-events: none; display: flex; align-items: center;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-primary" 
                    style="width: 100%; padding: 14px; background: linear-gradient(135deg,#3b82f6,#2563eb); border: none; border-radius: 12px; color: white; font-size: 15px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 10px 20px rgba(37,99,235,0.15); margin-bottom: 20px;">
                Register
            </button>

            <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <span style="color: #64748b; font-size: 14px;">Already have an account?</span>
                <a href="login.php" class="btn-secondary" 
                   style="display: inline-block; margin-left: 4px; color: #60a5fa; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.2s ease;"
                   onmouseover="this.style.color='#93c5fd'" 
                   onmouseout="this.style.color='#60a5fa'">
                    Sign in
                </a>
            </div>
        </form>
    </div>

</body>
</html>