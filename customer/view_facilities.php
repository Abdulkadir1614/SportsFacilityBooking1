<?php
session_start();
require_once "../auth/session_timeout.php";
require_once '../config/db.php'; 

// security check
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// fetch facilities
$sql = "SELECT * FROM facilities ORDER BY facility_name ASC";
$result = mysqli_query($conn, $sql);
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Facilities – Beerta Daarusalaam</title>
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/view_facilities.css">
        <link rel="stylesheet" href="../assets/css/customer_sidebar.css">
    </head>
    <body class="customer-page customer-sidebar-collapsed">
        <?php include '../includes/customer_header.php'; ?>
        <?php include '../includes/customer_sidebar.php'; ?>
        <div class="page">
            
            <div class="customer-layout" id="customerLayout">
               

                    <div class="page-header">
                        <div>
                            <span class="page-label">Browse & Book</span>
                            <h1 class="page-title">Available <span>Facilities</span></h1>
                            <p class="page-sub">Check live availability and pricing for all venues</p>
                        </div>
                    </div>

                    <div class="facility-grid">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <div class="facility-card <?= $row['availability_status'] !== 'Available' ? 'unavailable-card' : '' ?>">

                                <div class="facility-img">
                                    <img src="<?= (!empty($row['facility_image']) && file_exists('../assets/uploads/facilities/' . $row['facility_image']))
                                        ? '../assets/uploads/facilities/' . htmlspecialchars($row['facility_image'])
                                        : '../assets/default_facility.png' ?>"
                                        alt="<?= htmlspecialchars($row['facility_name']) ?>">
                                    <div class="img-overlay"></div>
                                </div>

                                <div class="facility-body">
                                    <div class="facility-top-row">
                                        <div class="facility-type"><?= htmlspecialchars($row['facility_type']) ?></div>
                                        <span class="status-pill <?= $row['availability_status'] === 'Available' ? 'pill-available' : 'pill-unavailable' ?>">
                                            <span class="status-dot"></span>
                                            <?= $row['availability_status'] ?>
                                        </span>
                                    </div>
                                    <h3 class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></h3>
                                    <div class="facility-price">
                                        <i class="bi bi-tag-fill"></i>
                                        $ <?= number_format($row['price_per_hour'], 2) ?> <span>/hour</span>
                                    </div>

                                    <?php if ($row['availability_status'] === 'Available'): ?>
                                        <a href="booking.php?facility_id=<?= $row['facility_id'] ?>" class="btn-book">
                                            Book Now <i class="bi bi-arrow-right"></i>
                                        </a>
                                    <?php else: ?>
                                        <button class="btn-unavailable" disabled>
                                            <i class="bi bi-x-circle"></i> Unavailable
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-building-slash"></i>
                                <h3>No facilities available</h3>
                                <p>Check back soon for new venues.</p>
                            </div>
                        <?php endif; ?>
                    </div>
            </div>

        </div>
        <script>
            // Sidebar
            document.addEventListener('DOMContentLoaded', () => {
            const toggle = document.getElementById('toggleCustomerSidebar');

            if (!toggle) return;

            toggle.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                document.body.classList.toggle('customer-sidebar-open');
                } else {
                document.body.classList.toggle('customer-sidebar-collapsed');
                }
            });
            });
        </script>
    </body>
</html>

<?php include "../includes/footer.php"; ?>
