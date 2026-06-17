<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'staff') {
    header("Location: ../auth/login.php");
    exit();
}

$result = mysqli_query($conn, "SELECT * FROM facilities");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Facilities – Staff</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/staff.css">
    </head>
    <body>
        <div class="staff-layout" id="staffLayout">
            <?php include "../includes/staff_sidebar.php"; ?>
            <main class="staff-main">
                <?php include "../includes/staff_header.php"; ?>
                
                <section class="content-area">
                    <div class="page-top">
                        <div>
                            <span class="page-label">Venues</span>
                            <h1 class="page-title">Manage <span>Facilities</span></h1>
                            <p class="page-sub">Update availability status for each sports venue.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="staff-table">
                                <thead>
                                    <tr><th>#</th><th>Facility</th><th>Type</th><th>Price/hr</th><th>Status</th><th>Action</th></tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td class="id-cell">#<?= $row['facility_id'] ?></td>
                                        <td class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></td>
                                        <td><span class="type-tag"><?= htmlspecialchars($row['facility_type']) ?></span></td>
                                        <td class="amount-cell">$ <?= number_format($row['price_per_hour'], 2) ?></td>
                                        <td><span class="badge badge-<?= strtolower($row['availability_status']) ?>"><?= $row['availability_status'] ?></span></td>
                                        <td>
                                            <?php if ($row['availability_status'] === 'Available'): ?>
                                                <a href="update_facility.php?id=<?= $row['facility_id'] ?>&status=Unavailable" onclick="return confirm('Mark as Unavailable?')" class="btn-action btn-unavailable"><i class="bi bi-x-circle"></i> Set Unavailable</a>
                                            <?php else: ?>
                                                <a href="update_facility.php?id=<?= $row['facility_id'] ?>&status=Available" onclick="return confirm('Mark as Available?')" class="btn-action btn-available"><i class="bi bi-check-circle"></i> Set Available</a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>
        </div>
        <script>
            const layout  = document.getElementById('staffLayout');
            const sidebar = document.getElementById('staffSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            document.getElementById('toggleSidebar').addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('staffCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('staffCollapsed') === 'true') layout.classList.add('collapsed');

            document.getElementById('mobileMenuBtn').addEventListener('click', () => {
                sidebar.classList.add('mobile-open');
                overlay.classList.add('active');
            });
            function closeMobileSidebar() {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('active');
            }
        </script>
    </body>
</html>