<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

$result = $conn->query("SELECT * FROM facilities ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Facilities – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_manage_facilities.css">
    </head>
    <body>
        <?php $page_title = 'Manage Facilities'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Sports Venues</span>
                            <h1 class="page-title">Manage <span>Facilities</span></h1>
                            <p class="page-sub">Add, update, or remove sports facilities.</p>
                        </div>
                        <a href="add_facility.php" class="btn-primary">
                            <i class="bi bi-plus-lg"></i> Add Facility
                        </a>
                    </div>

                    <div class="table-wrap">
                        <div class="table-responsive">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Price / Hour</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <img src="../assets/uploads/facilities/<?= htmlspecialchars($row['facility_image']) ?>"
                                                    alt="<?= htmlspecialchars($row['facility_name']) ?>"
                                                    class="facility-thumb">
                                            </td>
                                            <td data-label="Name">
                                                <span class="facility-name"><?= htmlspecialchars($row['facility_name']) ?></span>
                                            </td>
                                            <td data-label="Type">
                                                <span class="type-tag"><?= htmlspecialchars($row['facility_type']) ?></span>
                                            </td>
                                            <td data-label="Price">
                                                <span class="price-cell">
                                                    <i class="bi bi-tag-fill"></i>
                                                    $ <?= number_format($row['price_per_hour'], 2) ?>
                                                </span>
                                            </td>
                                            <td data-label="Status">
                                                <span class="badge badge-<?= strtolower($row['availability_status']) === 'available' ? 'available' : 'unavailable' ?>">
                                                    <?= htmlspecialchars($row['availability_status']) ?>
                                                </span>
                                            </td>
                                            <td data-label="Actions">
                                                <div class="action-btns">
                                                    <a href="edit_facility.php?id=<?= $row['facility_id'] ?>" class="btn-action btn-edit-action" title="Edit">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="delete_facility.php?id=<?= $row['facility_id'] ?>"
                                                    onclick="return confirm('Delete this facility?')"
                                                    class="btn-action btn-delete-action" title="Delete">
                                                        <i class="bi bi-trash3"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="bi bi-building-slash"></i>
                                                    <h3>No facilities found</h3>
                                                    <p>Start by adding your first facility.</p>
                                                    <a href="add_facility.php" class="btn-primary" style="margin-top:16px">
                                                        <i class="bi bi-plus-lg"></i> Add Facility
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </section>
            </main>
        </div>

        <script>
            const layout = document.getElementById('adminLayout');
            const toggle = document.getElementById('toggleSidebar');
            toggle.addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');
        </script>
    </body>
</html>
