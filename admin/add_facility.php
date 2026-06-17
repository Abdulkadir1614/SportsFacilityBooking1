<?php
session_start();
require_once "../auth/session_timeout.php";
require_once "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name  = $_POST['facility_name'];
    $type  = $_POST['facility_type'];
    $price = $_POST['price_per_hour'];

    /* IMAGE UPLOAD */
    $imageName = $_FILES['facility_image']['name'];
    $tmpName   = $_FILES['facility_image']['tmp_name'];

    $ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) {
        die("Invalid image type.");
    }

    $newImageName = uniqid("facility_") . "." . $ext;
    $uploadPath = "../assets/uploads/facilities/" . $newImageName;

    move_uploaded_file($tmpName, $uploadPath);

    /* INSERT INTO DATABASE */
    $stmt = $conn->prepare(
        "INSERT INTO facilities (facility_name, facility_type, facility_image, price_per_hour)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("sssd", $name, $type, $newImageName, $price);
    $stmt->execute();

    header("Location: manage_facilities.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Facility – Admin</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_base.css">
        <link rel="stylesheet" href="../assets/css/admin/admin_add_facility.css">
    </head>
    <body>
        <?php $page_title = 'Add Facility'; ?>

        <div class="admin-layout" id="adminLayout">
            <?php include "../includes/admin_sidebar.php"; ?>

            <main class="admin-content">
                <?php include "../includes/admin_header.php"; ?>

                <section class="content-area">

                    <div class="page-top">
                        <div>
                            <span class="page-label">New Venue</span>
                            <h1 class="page-title">Add <span>Facility</span></h1>
                            <p class="page-sub">Fill in the details to add a new sports facility.</p>
                        </div>
                        <a href="manage_facilities.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Back
                        </a>
                    </div>

                    <div class="form-card">
                        <form method="POST" enctype="multipart/form-data" class="admin-form">

                            <!-- Image upload -->
                            <div class="form-group">
                                <label>Facility Image</label>
                                <div class="file-upload" id="fileUpload">
                                    <input type="file" name="facility_image" accept="image/*" required id="imageInput">
                                    <div class="file-upload-ui" id="fileUploadUI">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                        <span>Click to upload image</span>
                                        <small>JPG, PNG, WEBP accepted</small>
                                    </div>
                                    <img id="imagePreview" class="img-preview" src="" alt="Preview">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="bi bi-building"></i> Facility Name</label>
                                    <input type="text" name="facility_name" placeholder="e.g. Field A" required>
                                </div>

                                <div class="form-group">
                                    <label><i class="bi bi-tag"></i> Facility Type</label>
                                    <input type="text" name="facility_type" placeholder="e.g. Football, Basketball" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label><i class="bi bi-currency-dollar"></i> Price per Hour ($)</label>
                                <div class="input-prefix-wrap">
                                    <span class="input-prefix">$</span>
                                    <input type="number" step="0.01" min="0" name="price_per_hour" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <a href="manage_facilities.php" class="btn-cancel">Cancel</a>
                                <button type="submit" class="btn-primary">
                                    <i class="bi bi-check-lg"></i> Save Facility
                                </button>
                            </div>

                        </form>
                    </div>

                </section>
            </main>
        </div>

        <script>
            // Sidebar toggle
            const layout = document.getElementById('adminLayout');
            document.getElementById('toggleSidebar').addEventListener('click', () => {
                layout.classList.toggle('collapsed');
                localStorage.setItem('sidebarCollapsed', layout.classList.contains('collapsed'));
            });
            if (localStorage.getItem('sidebarCollapsed') === 'true') layout.classList.add('collapsed');

            // Image preview
            document.getElementById('imageInput').addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = e => {
                    const preview = document.getElementById('imagePreview');
                    const ui = document.getElementById('fileUploadUI');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    ui.style.display = 'none';
                };
                reader.readAsDataURL(file);
            });
        </script>
    </body>
</html>
