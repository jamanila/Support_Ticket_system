<?php
session_start();
require_once ("../models/Users.php");

$user = new Users();

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $email = $_POST["email"];
    $password = $_POST["password"];

    $foundUser = $user->login($email);

    if($foundUser){

        if(password_verify($password, $foundUser["password"])){

            // ✅ THIS IS THE MISSING PART
            $_SESSION["user"] = [
                "id" => $foundUser["id"],
                "name" => $foundUser["name"],
                "email" => $foundUser["email"],
                "role" => $foundUser["role"]
            ];

            header("Location: ../views/tickets/index.php");
            exit();
        }
        else{
            echo "Login failed";
        }
    }
    else{
        echo "No user found";
    }
}
?>



<!DOCTYPE html>
<html style="height: 100%;">
<head>
    <title>Login</title>
</head>
<body style="margin: 0; font-family: sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 350px;">
        
        <h2 style="margin: 0 0 20px 0; text-align: center; color: #1c1e21; font-size: 24px;">Login</h2>

        <form method="POST">

            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4b4f56; font-size: 14px;">Email</label>
                <input type="email" name="email" required placeholder="Enter your email" 
                    style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; box-sizing: border-box; font-size: 16px;">
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #4b4f56; font-size: 14px;">Password</label>
                <input type="password" name="password" required placeholder="Enter password" 
                    style="width: 100%; padding: 12px; border: 1px solid #dddfe2; border-radius: 6px; box-sizing: border-box; font-size: 16px;">
            </div>

            <button type="submit" 
                style="width: 100%; padding: 12px; background-color: #1877f2; border: none; border-radius: 6px; color: white; font-size: 18px; font-weight: bold; cursor: pointer;">
                Log In
            </button>
            <a href="register.php">Dont have account</a>

        </form>
        
    </div>

</body>
</html>
