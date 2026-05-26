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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <?php require_once __DIR__ . "/../partials/header.php"; ?>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

    <div style="background: #ffffff; padding: 40px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); width: 100%; max-width: 400px;">
        
        <h2 style="margin-top: 0; color: #333; font-size: 24px; text-align: center; margin-bottom: 30px;">Create Account</h2>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px;">Full Name</label>
                <input type="text" name="name" required placeholder="John Doe" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px;">Email Address</label>
                <input type="email" name="email" required placeholder="name@company.com" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; outline: none;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px;">Password</label>
                <input type="password" name="password" required placeholder="••••••••" 
                       style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 16px; outline: none;">
            </div>

            <div style="margin-bottom: 25px;">
                <label style="display: block; font-size: 14px; font-weight: 600; color: #555; margin-bottom: 8px;">User Role</label>
                <select name="role" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; background-color: white; font-size: 16px; outline: none; appearance: none;">
                    <option value="user">User</option>
                    <option value="agent">Agent</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" style="width: 100%; background-color: #4f46e5; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background-color 0.2s;">
                Register
            </button>
            <a href="login.php">Already have account</a>
        </form>
    </div>

</body>
</html>
