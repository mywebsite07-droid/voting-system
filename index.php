<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDG | Voting Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #0f172a;
            /* Professional Mesh Gradient */
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,15%,1) 0, transparent 50%), 
                radial-gradient(at 100% 100%, hsla(225,39%,25%,1) 0, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .container {
            width: 90%;
            max-width: 450px;
            text-align: center;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .logo-ring {
            width: 100px;
            height: 100px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
        }

        .logo-ring img { width: 70px; }

        h1 { font-size: 2rem; font-weight: 800; letter-spacing: -1px; margin-bottom: 10px; }
        p { color: #94a3b8; margin-bottom: 35px; font-size: 1rem; }

        .btn-stack { display: flex; flex-direction: column; gap: 15px; }

        .btn {
            position: relative;
            padding: 18px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            border: 1px solid transparent;
        }

        .btn-voter {
            background: #6366f1; /* Indigo */
            color: white;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        }

        .btn-admin {
            background: rgba(255, 255, 255, 0.05);
            color: white;
            border-color: rgba(255, 255, 255, 0.1);
        }

        .btn:hover {
            transform: translateY(-3px);
            filter: brightness(1.2);
        }

        .btn-admin:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .footer { margin-top: 30px; font-size: 0.75rem; color: #475569; text-transform: uppercase; letter-spacing: 2px; }
    </style>
</head>
<body>

<div class="container">
    <div class="glass-card">
        <div class="logo-ring">
            <img src="logo.jpg" alt="CDG Logo">
        </div>
        <h1>CDG PORTAL</h1>
        <p>Please select your gateway</p>

        <div class="btn-stack">
            <a href="student_login.php" class="btn btn-voter">VOTER ACCESS</a>
            <a href="admin_results.php" class="btn btn-admin">ADMIN SERVER</a>
        </div>
    </div>
    <div class="footer">Secure Voting Environment</div>
</div>

</body>
</html>