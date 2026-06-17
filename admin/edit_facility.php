<?php
session_start();
require_once "../config/db.php";

// Security check (optional but recommended)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("Facility ID not provided.");
}

$id = (int) $_GET['id'];

// Fetch existing facility
$stmt = $conn->prepare("SELECT * FROM facilities WHERE facility_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$facility = $stmt->get_result()->fetch_assoc();

if (!$facility) {
    die("Facility not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Keep old image by default
    $imageName = $facility['facility_image'];

    // Check if new image uploaded
    if (isset($_FILES['facility_image']) && $_FILES['facility_image']['error'] === 0) {

        $tmpName = $_FILES['facility_image']['tmp_name'];

        $imageName = time() . "_" . basename($_FILES['facility_image']['name']);

        $uploadPath = "../assets/uploads/facilities/" . $imageName;

        move_uploaded_file($tmpName, $uploadPath);
    }

    $stmt = $conn->prepare("
        UPDATE facilities 
        SET facility_image = ?,
            facility_name = ?,
            facility_type = ?,
            price_per_hour = ?,
            availability_status = ?
        WHERE facility_id = ?
    ");

    $stmt->bind_param(
        "sssdsi",
        $imageName,
        $_POST['facility_name'],
        $_POST['facility_type'],
        $_POST['price_per_hour'],
        $_POST['availability_status'],
        $id
    );

    $stmt->execute();

    header("Location: manage_facilities.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Facility – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_edit_facility.css">
    </head>
    <body>
        <?php $page_title = 'Edit Facility'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">Editing Venue</span>
                            <h1 class="page-title">Edit <span>Facility</span></h1>
                            <p class="page-sub">Update the details for this sports facility.</p>
                        </div>
                        <a href="manage_facilities.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data" class="admin-form">

                            <!-- Current image preview -->
                            <?php if (!empty($facility['facility_image'])): ?>
                            <div class="form-group">
                                <label><i class="bi bi-image"></i> Current Image</label>
                                <div class="current-image-wrap">
                                    <img src="../assets/uploads/facilities/<?= htmlspecialchars($facility['facility_image']) ?>"
                                        alt="Current facility image" class="current-img" id="imagePreview">
                                    <span class="img-change-hint">Upload below to replace</span>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Replace image -->
                            <div class="form-group" enctype="multipart/form-data">
                                <label><i class="bi bi-cloud-arrow-up"></i> Replace Image </label>
                                <div class="file-upload">
                                    <input type="file" name="facility_image" accept="image/*" id="imageInput">
                                    <div class="file-upload-ui">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <span>Click to upload new image</span>
                                        <small>JPG, PNG, WEBP accepted</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-building"></i> Facility Name</label>
                                    <input type="text" name="facility_name"
                                        value="<?= htmlspecialchars($facility['facility_name']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-tag"></i> Facility Type</label>
                                    <input type="text" name="facility_type"
                                        value="<?= htmlspecialchars($facility['facility_type']) ?>" required>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-currency-dollar"></i> Price per Hour ($)</label>
                                    <div class="input-prefix-wrap">
                                        <span class="input-prefix">$</span>
                                        <input type="number" step="0.01" min="0" name="price_per_hour"
                                            value="<?= htmlspecialchars($facility['price_per_hour']) ?>" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-toggle-on"></i> Availability Status</label>
                                    <select name="availability_status">
                                        <option value="Available"   <?= $facility['availability_status'] === 'Available'   ? 'selected' : '' ?>>Available</option>
                                        <option value="Unavailable" <?= $facility['availability_status'] === 'Unavailable' ? 'selected' : '' ?>>Unavailable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="manage_facilities.php" class="btn-cancel">Cancel</a>
                                <button type="submit" class="btn-primary">
                                    <i class="bi bi-check-lg"></i> Update Facility
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

            // Live image preview when replacing
            document.getElementById('imageInput').addEventListener('change', function(e){

            const file = e.target.files[0];

            if(file){

                const reader = new FileReader();

                reader.onload = function(event){
                    document.getElementById('imagePreview').src = event.target.result;
                };

                reader.readAsDataURL(file);
            }
        });
        </script>
    </body>
</html>
