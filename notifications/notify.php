<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Mark as read via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    // MySQLi version:
    $upd = $conn->prepare("UPDATE notifications SET status='read' WHERE notification_id=? AND user_id=?");
    $upd->bind_param("ii", $id, $user_id);
    $upd->execute();
    exit(json_encode(['ok'=>true]));
}

// Mark ALL as read
if (isset($_GET['mark_all'])) {
    $upd = $conn->prepare("UPDATE notifications SET status='read' WHERE user_id=?");
    $upd->bind_param("i", $user_id);
    $upd->execute();
    header("Location: notify.php"); exit();
}

// Fetch notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY notification_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

$unread_count = count(array_filter($notifications, fn($n) => $n['status'] === 'unread'));
?>
<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications – Beerta Daarusalaam</title>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/notifications.css">


    </head>
    <body>
        <?php include "../includes/customer_header.php"; ?>

        <!-- NOTIFICATIONS PAGE -->
        <div class="page">
        <div class="page-header">
            <div class="page-title-wrap">
            <div class="page-icon"><i class="bi bi-bell-fill"></i></div>
            <div>
                <span class="page-title">Notifications</span>
                <?php if($unread_count > 0): ?>
                <span class="unread-badge"><?= $unread_count ?> new</span>
                <?php endif; ?>
            </div>
            </div>
            <?php if($unread_count > 0): ?>
            <a href="notify.php?mark_all=1" class="btn-mark-all">
                <i class="bi bi-check2-all"></i> Mark all read
            </a>
            <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="empty-state">
            <div class="empty-icon"><i class="bi bi-bell-slash"></i></div>
            <h3>No notifications yet</h3>
            </div>
        <?php else: ?>
            <div class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <div class="notif-card <?= $n['status'] === 'unread' ? 'unread' : 'read' ?>"
                    id="card-<?= $n['notification_id'] ?>">
                <div class="notif-dot"><i class="bi bi-bell<?= $n['status']==='unread' ? '-fill' : '' ?>"></i></div>
                <div class="notif-body">
                    <div class="notif-msg"><?= htmlspecialchars($n['message']) ?></div>
                    <div class="notif-time">
                    <i class="bi bi-clock"></i>
                    <?= date('M d, Y · h:i A', strtotime($n['notification_date'])) ?>
                    </div>
                </div>
                <?php if ($n['status'] === 'unread'): ?>
                    <button class="btn-read" onclick="markRead(<?= $n['notification_id'] ?>)">
                    <i class="bi bi-check2"></i> Read
                    </button>
                <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
        </div>

        <script>
            //  Mark single as read  
            function markRead(id) {
            fetch('notify.php', {
                method:'POST',
                headers:{'Content-Type':'application/x-www-form-urlencoded'},
                body:'id='+id
            }).then(r=>r.json()).then(()=>{
                const card = document.getElementById('card-'+id);
                card.classList.replace('unread','read');
                card.querySelector('.btn-read')?.remove();
                card.querySelector('.notif-dot i').className = 'bi bi-bell';

                // update badge
                const badge = document.querySelector('.unread-badge');
                if(badge){
                const n = parseInt(badge.textContent) - 1;
                n > 0 ? badge.textContent = n+' new' : badge.remove();
                }
                // update header bell badge
                const hb = document.getElementById('headerBellCount');
                if(hb){ const n=parseInt(hb.textContent)-1; n>0?hb.textContent=n:hb.remove(); }
            });
            }

        </script>
        
    </body>
</html>
