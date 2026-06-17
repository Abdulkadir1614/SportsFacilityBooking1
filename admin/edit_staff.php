<?php
require_once "../config/db.php";

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM users WHERE user_id=$id");
$row = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    $query = "UPDATE users 
              SET name='$name', email='$email', phone_number='$phone'
              WHERE user_id=$id";

    mysqli_query($conn, $query);

    header("Location: manage_staff.php");
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Staff – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_staff.css">
    </head>
    <body>
        <?php $page_title = 'Edit Staff'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Editing Member</span>
                            <h1 class="page-title">Edit <span>Staff</span></h1>
                            <p class="page-sub">Update staff account details.</p>
                        </div>
                        <a href="manage_staff.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <div class="form-card">
                        <form method="POST" class="admin-form">

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-person"></i> Full Name</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($row['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><i class="bi bi-envelope"></i> Email Address</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="bi bi-telephone"></i> Phone Number</label>
                                <input type="text" name="phone" value="<?= htmlspecialchars($row['phone_number']) ?>">
                            </div>

                            <div class="form-actions">
                                <a href="manage_staff.php" class="btn-cancel">Cancel</a>
                                <button type="submit" name="update" class="btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Staff
                                </button>
                            </div>

                        </form>
                    </div>

                </section>
            </main>
        </div>

        <script>
            const layout = document.getElementById('adminLayout');
            document.getElementById('toggleSidebar').addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');
        </script>
    </body>
</html>