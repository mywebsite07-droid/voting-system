<?php
include('db.php');
session_start();

if (isset($_POST['login'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);

    // FUNCTION: Only checks Student ID (No Password)
    $query = "SELECT * FROM users WHERE student_id = '$student_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['user'] = $student_id;
        header("Location: vote.php");
        exit();
    } else {
        $error = "Student ID not found!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDG Voting Login</title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            /* BACKGROUND IMAGE: Ensure bg.png is in your voting folder */
            background: url('bg.png') no-repeat center center fixed; 
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        /* Dark overlay to make the login box pop */
        body::before {
            content: "";
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .login-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }

        /* LOGO IMAGE: Ensure logo.png is in your voting folder */
        .logo { 
            width: 130px; 
            height: auto;
            margin-bottom: 15px; 
        }

        h2 { color: #2c3e50; margin-bottom: 25px; font-size: 1.8rem; }
        
        input[type="text"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-size: 1.1rem;
            text-align: center;
            box-sizing: border-box;
        }

        input[type="text"]:focus {
            border-color: #3498db;
            outline: none;
        }

        .btn-login {
            width: 100%;
            padding: 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-login:hover { background: #219150; }
        
        /* BACK TO HOME BUTTON STYLING */
        .btn-home {
            display: inline-block;
            margin-top: 20px;
            color: #7f8c8d;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
            transition: 0.2s;
        }
        
        .btn-home:hover {
            color: #34495e;
        }

        .error { color: #e74c3c; font-weight: bold; margin-bottom: 15px; }
    </style>
</head>
<body>

<div class="login-box">
    <img src="logo.jpg" class="logo" alt="Logo">
    
    <h2>Student Login</h2>
    
    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
    
    <form method="POST">
        <input type="text" name="student_id" placeholder="Enter Student ID" required autofocus>
        <button type="submit" name="login" class="btn-login">SIGN IN TO VOTE</button>
    </form>
    
    <a href="index.php" class="btn-home">← Back to Portal Home</a>
</div>

</body>
</html>
