<?php
include('db.php');
session_start();
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }

$user = $_SESSION['user'];
$user_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT has_voted FROM users WHERE student_id='$user'"));

// Process the vote
if (isset($_POST['submit_vote'])) {
    if(!empty($_POST['candidate_id'])) {
        foreach($_POST['candidate_id'] as $pos_name => $id) {
            $safe_id = mysqli_real_escape_string($conn, $id);
            mysqli_query($conn, "UPDATE candidates SET votes = votes + 1 WHERE id = $safe_id");
        }
        mysqli_query($conn, "UPDATE users SET has_voted = 1 WHERE student_id = '$user'");
        header("Location: vote.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Official CDG Ballot</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --success: #10b981;
            --slate: #1e293b;
            --glass: rgba(255, 255, 255, 0.95);
        }

        * { 
            box-sizing: border-box; 
            -webkit-tap-highlight-color: transparent; 
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); 
            background-attachment: fixed;
            margin: 0; 
            padding: 0; 
            color: #f8fafc;
            overscroll-behavior: none;
        }
        
        .container { 
            max-width: 600px;
            margin: 0 auto;
            padding: 20px; 
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-card {
            background: var(--glass);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            color: var(--slate);
        }

        .header-card h2 { margin: 0; font-weight: 700; font-size: 1.5rem; letter-spacing: -0.5px; }
        .student-id { color: #64748b; font-size: 0.9rem; margin-top: 5px; }

        .pos-block { 
            background: var(--glass);
            padding: 20px; 
            margin-bottom: 20px; 
            border-radius: 20px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            color: var(--slate);
        }

        .pos-block h3 { 
            color: var(--primary); 
            margin: 0 0 15px 0; 
            font-size: 1.1rem; 
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #f1f5f9;
            padding-bottom: 10px;
        }

        .candidate-option { 
            display: flex; 
            align-items: center; 
            padding: 12px; 
            margin: 12px 0; 
            border: 2px solid #f1f5f9; 
            border-radius: 15px; 
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .img-container {
            width: 120px;
            height: 120px;
            margin-right: 15px;
            flex-shrink: 0;
            background: #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .candidate-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover;
        }

        .cand-name { font-size: 1.1rem; font-weight: 700; color: #334155; }

        input[type="radio"]:checked + .img-container { 
            border-color: var(--primary); 
            box-shadow: 0 0 15px rgba(99, 102, 241, 0.5); 
        }

        .btn-vote { 
            width: 100%; 
            padding: 18px; 
            background: var(--success); 
            color: white; 
            border: none; 
            border-radius: 15px; 
            font-size: 1.1rem; 
            font-weight: 700; 
            cursor: pointer; 
            margin-bottom: 50px;
        }

        /* --- CENTERED MODAL FIX --- */
        .modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(15, 23, 42, 0.9); 
            backdrop-filter: blur(8px);
            /* This centers the content perfectly */
            display: none; 
            align-items: center; 
            justify-content: center; 
        }

        .modal-content { 
            background: #f1f5f9; 
            padding: 25px; 
            border-radius: 24px; 
            width: 90%; 
            max-width: 400px; 
            color: var(--slate);
            max-height: 85vh; 
            display: flex; 
            flex-direction: column;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            /* Reset any margins that might push it off-center */
            margin: auto;
            animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes modalPop {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        #reviewList { 
            overflow-y: auto; 
            padding-right: 5px; 
            margin-bottom: 20px;
        }

        .review-box {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-confirm { flex: 2; padding: 14px; background: var(--success); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }
        .btn-edit { flex: 1; padding: 14px; background: #94a3b8; color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; }

        @media (max-width: 600px) {
            .img-container { width: 100px; height: 100px; margin-left: 10px; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header-card">
        <h2>Official CDG Ballot</h2>
        <div class="student-id">Student ID: <?php echo htmlspecialchars($user); ?></div>
    </div>

    <?php if ($user_check['has_voted']): ?>
        <div style="text-align:center; padding: 60px 20px; background: white; border-radius: 20px; color: var(--slate);">
            <div style="font-size: 50px; margin-bottom: 10px;">✅</div>
            <h2 style="color:var(--success); font-size: 2rem; margin-bottom: 10px;">Vote Recorded!</h2>
            <p style="color: #64748b;">Thank you for exercising your right to vote.</p>
            <br>
            <a href="logout.php" style="display:inline-block; padding: 15px 40px; background: var(--primary); color: white; text-decoration: none; border-radius: 12px; font-weight: bold;">Logout Portal</a>
        </div>
    <?php else: ?>
        <form id="ballotForm" method="POST">
            <?php
            $positions = mysqli_query($conn, "SELECT DISTINCT position FROM candidates");
            while($p = mysqli_fetch_assoc($positions)) {
                $posName = $p['position'];
                echo "<div class='pos-block'><h3>$posName</h3>";
                $cands = mysqli_query($conn, "SELECT * FROM candidates WHERE position='$posName'");
                while($c = mysqli_fetch_assoc($cands)) {
                    echo "<label class='candidate-option'>
                            <input type='radio' name='candidate_id[$posName]' value='".$c['id']."' data-name='".htmlspecialchars($c['name'])."' data-pos='".htmlspecialchars($posName)."' required style='accent-color: var(--primary); transform: scale(1.3); margin-left: 5px;'>
                            <div class='img-container'>
                                <img src='images/".$c['photo']."' class='candidate-img' loading='eager'>
                            </div>
                            <span class='cand-name'>".$c['name']."</span>
                          </label>";
                }
                echo "</div>";
            }
            ?>
            <button type="button" onclick="showReview()" class="btn-vote">REVIEW & SUBMIT BALLOT</button>

            <div id="reviewModal" class="modal">
                <div class="modal-content">
                    <h3 style="text-align:center; margin: 0 0 15px 0;">Confirm Your Choices</h3>
                    <div id="reviewList"></div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" onclick="closeReview()" class="btn-edit">Edit</button>
                        <button type="submit" name="submit_vote" class="btn-confirm">Submit Vote</button>
                    </div>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<script>
    function showReview() {
        const form = document.getElementById('ballotForm');
        const reviewList = document.getElementById('reviewList');
        const radios = form.querySelectorAll('input[type="radio"]:checked');
        const allPos = form.querySelectorAll('.pos-block').length;

        if (radios.length < allPos) {
            alert("Please select one candidate for every position before submitting.");
            return;
        }

        reviewList.innerHTML = ''; 
        radios.forEach(radio => {
            const pos = radio.getAttribute('data-pos');
            const name = radio.getAttribute('data-name');
            const imgSrc = radio.parentElement.querySelector('.candidate-img').src;

            const box = document.createElement('div');
            box.className = 'review-box';
            box.innerHTML = `
                <img src="${imgSrc}" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover; border: 1px solid var(--primary);">
                <div style="display: flex; flex-direction: column;">
                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase;">${pos}</span>
                    <span style="font-size: 0.95rem; font-weight: 600; color: #1e293b;">${name}</span>
                </div>
            `;
            reviewList.appendChild(box);
        });

        // Set to flex to trigger the centering logic
        document.getElementById('reviewModal').style.display = 'flex';
    }

    function closeReview() {
        document.getElementById('reviewModal').style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target.id == 'reviewModal') closeReview();
    }
</script>

</body>
</html>