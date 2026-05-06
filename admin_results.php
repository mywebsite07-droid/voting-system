<?php
include('db.php');
session_start();

// --- START DESIGNED LOGIN LOGIC ---
$admin_user = "admin"; // Set your desired username here
$admin_password = "admin123";

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin_results.php");
    exit;
}

if (isset($_POST['admin_user']) && isset($_POST['admin_pass'])) {
    if ($_POST['admin_user'] === $admin_user && $_POST['admin_pass'] === $admin_password) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = "Invalid Username or Password";
    }
}

if (!isset($_SESSION['logged_in'])) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: #0f172a; display: flex; align-items: center; justify-content: center; height: 100vh; color: white; }
        .login-box { background: #1e293b; padding: 40px; border-radius: 20px; width: 100%; max-width: 320px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5); border: 1px solid #334155; text-align: center; }
        h2 { margin-bottom: 20px; font-weight: 800; }
        input { width: 100%; padding: 12px; margin-bottom: 15px; border-radius: 8px; border: 1px solid #475569; background: #0f172a; color: white; box-sizing: border-box; outline: none; }
        input:focus { border-color: #6366f1; }
        button { width: 100%; padding: 12px; background: #6366f1; border: none; border-radius: 8px; color: white; font-weight: 600; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
        button:hover { background: #4f46e5; }
        .error { color: #f43f5e; font-size: 13px; margin-bottom: 10px; background: rgba(244, 63, 94, 0.1); padding: 8px; border-radius: 5px; }
        .back-btn { display: inline-block; margin-top: 20px; color: #94a3b8; text-decoration: none; font-size: 14px; transition: 0.2s; }
        .back-btn:hover { color: white; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Access</h2>
        <?php if(isset($error)) echo "<div class='error'>$error</div>"; ?>
        <form method="POST">
            <input type="text" name="admin_user" placeholder="Username" required autofocus>
            <input type="password" name="admin_pass" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <a href="index.php" class="back-btn">← Back to Portal</a>
    </div>
</body>
</html>
<?php
    exit;
}
// --- END DESIGNED LOGIN LOGIC ---

// --- 2. AJAX DATA UPDATE ---
if (isset($_GET['fetch_data'])) {
    $votes_data = ['candidates' => [], 'voters' => []];
    $res = mysqli_query($conn, "SELECT id, votes FROM candidates");
    while($row = mysqli_fetch_assoc($res)) {
        $votes_data['candidates'][$row['id']] = $row['votes'];
    }
    $v_res = mysqli_query($conn, "SELECT student_id FROM users WHERE has_voted = 1");
    while($v_row = mysqli_fetch_assoc($v_res)) {
        $votes_data['voters'][] = $v_row['student_id'];
    }
    $votes_data['total_voters'] = count($votes_data['voters']);
    echo json_encode($votes_data);
    exit();
}

// --- 3. SETTINGS & ACTIONS ---
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'votes';
$filter_pos = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$order_query = ($sort_by == 'name') ? "ORDER BY name ASC" : "ORDER BY votes DESC";

if (isset($_POST['backup_data'])) {
    $filename = "election_backup_" . date("Y-m-d_H-i-s") . ".txt";
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = "ELECTION RESULTS BACKUP - " . date("Y-m-d H:i:s") . "\n\n";
    $query = mysqli_query($conn, "SELECT * FROM candidates ORDER BY position, votes DESC");
    while ($row = mysqli_fetch_assoc($query)) {
        $output .= "Position: " . str_pad($row['position'], 15) . " | Name: " . str_pad($row['name'], 20) . " | Votes: " . $row['votes'] . "\n";
    }
    echo $output; exit();
}

if (isset($_POST['reset_votes'])) {
    mysqli_query($conn, "UPDATE candidates SET votes = 0");
    mysqli_query($conn, "UPDATE users SET has_voted = 0");
    header("Location: admin_results.php"); exit();
}

if (isset($_POST['add_candidate'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $pos = mysqli_real_escape_string($conn, $_POST['position']);
    $photo = $_FILES['photo']['name'];
    if (move_uploaded_file($_FILES['photo']['tmp_name'], "images/" . $photo)) {
        mysqli_query($conn, "INSERT INTO candidates (name, position, photo, votes) VALUES ('$name', '$pos', '$photo', 0)");
    }
    header("Location: admin_results.php"); exit();
}

if (isset($_GET['edit_id']) && isset($_GET['new_name'])) {
    $id = (int)$_GET['edit_id'];
    $new_name = mysqli_real_escape_string($conn, $_GET['new_name']);
    mysqli_query($conn, "UPDATE candidates SET name='$new_name' WHERE id=$id");
    header("Location: admin_results.php"); exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM candidates WHERE id=$id");
    header("Location: admin_results.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Admin Dashboard</title>
    <style>
        :root { --accent: #6366f1; --success: #10b981; --danger: #f43f5e; --info: #0ea5e9; --panel-bg: rgba(255, 255, 255, 0.95); }
        body { font-family: 'Inter', sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 10px; }
        .container { max-width: 1000px; margin: auto; padding-top: 10px; }
        
        .header { background: var(--panel-bg); padding: 15px; border-radius: 12px; color: #1e293b; margin-bottom: 20px; display: flex; flex-direction: column; gap: 15px; align-items: center; text-align: center; }
        @media (min-width: 768px) { .header { flex-direction: row; justify-content: space-between; text-align: left; align-items: center; } }
        
        .action-btns { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; width: 100%; }
        @media (min-width: 768px) { .action-btns { justify-content: flex-end; width: auto; } }

        .btn, .sel-box { 
            padding: 10px 14px; 
            border-radius: 8px; 
            font-weight: 600; 
            font-size: 14px !important; 
            border: none; 
            cursor: pointer; 
            transition: 0.2s; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            min-height: 40px;
            box-sizing: border-box;
            text-align: center;
        }

        .btn-add { background: var(--accent); color: white; }
        .btn-stats { background: var(--info); color: white; }
        .btn-archive { background: #334155; color: white; }
        .btn-reset { background: var(--danger); color: white; }
        .btn-out { background: #e2e8f0; color: #475569; text-decoration: none; }
        .sel-box { background: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; -webkit-appearance: none; }

        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); backdrop-filter: blur(6px); }
        .modal-content { background: #fff; margin: 5vh auto; padding: 25px; border-radius: 20px; width: 90%; max-width: 450px; color: #1e293b; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); box-sizing: border-box; }
        
        .modal-header { border-bottom: 1px solid #f1f5f9; margin-bottom: 20px; padding-bottom: 10px; }
        .modal-header h3 { margin: 0; font-size: 1.3rem; }
        
        .form-group { text-align: left; margin-bottom: 18px; }
        .form-group label { display: block; font-size: 12px; font-weight: 700; color: #64748b; margin-bottom: 6px; text-transform: uppercase; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; font-size: 15px; box-sizing: border-box; outline: none; }

        .results-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; }
        .card { background: var(--panel-bg); border-radius: 12px; padding: 15px; color: #1e293b; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .card h2 { margin: 0 0 10px 0; font-size: 0.95rem; color: var(--accent); border-bottom: 1px solid #eee; padding-bottom: 8px; }
        
        .cand-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #f1f5f9; }
        .cand-img { width: 45px; height: 45px; border-radius: 6px; object-fit: cover; }
        
        .btn-row { padding: 6px 10px; font-size: 11px !important; border-radius: 6px; margin-right: 3px; border: none; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; }
        .btn-edit { background: #dcfce7; color: #16a34a; }
        .btn-del { background: #fee2e2; color: #ef4444; text-decoration: none; }
        
        .vote-count { font-weight: 800; color: var(--success); font-size: 1.2rem; text-align: right; min-width: 40px; }
        .voted-display { font-size: 3rem; font-weight: 800; color: var(--info); margin: 10px 0; text-align: center; }
        .voter-scroll { height: 200px; overflow-y: auto; background: #0f172a; color: #fff; border-radius: 8px; padding: 10px; margin-top: 15px; font-size: 13px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div>
            <h1 style="margin:0; font-size:1.2rem;">Live Results</h1>
            <p style="margin:0; font-size:0.7rem; color: #64748b;">Election Manager</p>
        </div>
        <div class="action-btns">
            <button class="btn btn-stats" onclick="openModal('voterModal')">📊 Stats</button>
            <select class="sel-box" onchange="updateURL('filter', this.value)">
                <option value="all">Positions</option>
                <?php
                $pl_res = mysqli_query($conn, "SELECT DISTINCT position FROM candidates");
                while($pl = mysqli_fetch_assoc($pl_res)) {
                    echo "<option value='".$pl['position']."' ".($filter_pos == $pl['position'] ? 'selected' : '').">".$pl['position']."</option>";
                }
                ?>
            </select>
            <select class="sel-box" onchange="updateURL('sort', this.value)">
                <option value="votes" <?php if($sort_by == 'votes') echo 'selected'; ?>>Sort: Votes</option>
                <option value="name" <?php if($sort_by == 'name') echo 'selected'; ?>>Sort: Name</option>
            </select>
            <form method="POST" style="margin:0; display:flex;"><button type="submit" name="backup_data" class="btn btn-archive">Backup</button></form>
            <button class="btn btn-add" onclick="openModal('addModal')">+ Add</button>
            <form method="POST" style="margin:0; display:flex;" onsubmit="return confirm('Reset?');"><button type="submit" name="reset_votes" class="btn btn-reset">Reset</button></form>
            <a href="?logout=true" class="btn btn-out">Logout</a>
        </div>
    </div>

    <div class="results-grid">
        <?php
        $f_sql = ($filter_pos != 'all') ? "WHERE position = '$filter_pos'" : "";
        $pos_query = mysqli_query($conn, "SELECT DISTINCT position FROM candidates $f_sql");
        while($p = mysqli_fetch_assoc($pos_query)) {
            echo "<div class='card'><h2>".$p['position']."</h2>";
            $c_query = mysqli_query($conn, "SELECT * FROM candidates WHERE position='".$p['position']."' $order_query");
            while($c = mysqli_fetch_assoc($c_query)) {
                echo "<div class='cand-row'>
                        <div style='display:flex; align-items:center; gap:10px;'>
                            <img src='images/".$c['photo']."' class='cand-img'>
                            <div class='cand-info'>
                                <strong id='name-".$c['id']."'>".$c['name']."</strong><br>
                                <div style='margin-top:4px; display:flex;'>
                                    <button onclick='editName(".$c['id'].")' class='btn-row btn-edit'>Edit</button>
                                    <a href='?delete=".$c['id']."' class='btn-row btn-del' onclick='return confirm(\"Del?\")'>Del</a>
                                </div>
                            </div>
                        </div>
                        <div class='vote-count' id='vote-".$c['id']."'>".$c['votes']."</div>
                      </div>";
            }
            echo "</div>";
        }
        ?>
    </div>
</div>

<div id="voterModal" class="modal"><div class="modal-content">
    <p style="margin:0; color: #64748b; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; text-align:center;">Total Votes Cast</p>
    <div class="voted-display" id="voted-count">0</div>
    <div class="voter-scroll" id="voter-list-ajax"></div>
    <button onclick="closeModal('voterModal')" class="btn btn-out" style="width:100%; margin-top:15px;">Close</button>
</div></div>

<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>New Candidate</h3>
        </div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" placeholder="Enter name" class="form-input" required>
            </div>
            <div class="form-group">
                <label>Position</label>
                <select name=" position" class="form-input" required>
                    <option value="President">President</option><option value="Vice President">Vice President</option>
                    <option value="Secretary">Secretary</option><option value="Treasurer">Treasurer</option>
                    <option value="Auditor">Auditor</option><option value="PIO">PIO</option>
                    <option value="Peace Officer">Peace Officer</option><option value="Protocol Officer">Protocol Officer</option>
                    <option value="1st Year Rep">1st Year Rep</option><option value="2nd Year Rep">2nd Year Rep</option>
                    <option value="3rd Year Rep">3rd Year Rep</option><option value="4th Year Rep">4th Year Rep</option>
                </select>
            </div>
            <div class="form-group">
                <label>Photo</label>
                <input type="file" name="photo" class="form-input" required>
            </div>
            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="button" onclick="closeModal('addModal')" class="btn" style="flex:1; background:#f1f5f9; color:#475569;">Cancel</button>
                <button type="submit" name="add_candidate" class="btn btn-add" style="flex:2;">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).style.display = 'block'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }
    function updateURL(p, v) { let u = new URL(window.location.href); u.searchParams.set(p, v); window.location.href = u.href; }
    
    function editName(id) {
        let old = document.getElementById('name-' + id).innerText;
        let n = prompt("New Name:", old);
        if (n) window.location.href = `admin_results.php?edit_id=${id}&new_name=${encodeURIComponent(n)}`;
    }

    function refresh() {
        if (document.getElementById('addModal').style.display === 'block') return;
        fetch('admin_results.php?fetch_data=true').then(r => r.json()).then(d => {
            for (let id in d.candidates) {
                let e = document.getElementById('vote-' + id);
                if (e) e.innerText = d.candidates[id];
            }
            document.getElementById('voted-count').innerText = d.total_voters;
            let l = ''; d.voters.forEach(v => { l += `<div style="padding:8px; border-bottom:1px solid #1e293b;">ID: ${v} <span style="float:right; color:#10b981;">● Voted</span></div>`; });
            document.getElementById('voter-list-ajax').innerHTML = l || "No votes yet.";
        });
    }
    setInterval(refresh, 3000); refresh();
</script>
</body>
</html>