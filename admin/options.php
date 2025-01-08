<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

include 'db_connect.php';

// Handle all POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'delete_haircut') {
        // Existing haircut deletion code...
    } 
    else if ($_POST['action'] === 'delete_service') {
        if (!isset($_POST['serviceID'])) {
            die(json_encode(['success' => false, 'message' => 'No service ID provided']));
        }

        $serviceID = $_POST['serviceID'];

        // Check if service is being used in appointments
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_tbl WHERE serviceID = ?");
        $check_stmt->bind_param("i", $serviceID);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $check_stmt->close();
            die(json_encode([
                'success' => false, 
                'message' => 'This service cannot be deleted because it is being used in existing appointments.'
            ]));
        }

        $check_stmt->close();

        // Delete the service
        $delete_stmt = $conn->prepare("DELETE FROM service_tbl WHERE serviceID = ?");
        $delete_stmt->bind_param("i", $serviceID);

        try {
            if ($delete_stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete service']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        $delete_stmt->close();
        exit();
    }
    else if ($_POST['action'] === 'delete_addon') {
        if (!isset($_POST['addonID'])) {
            die(json_encode(['success' => false, 'message' => 'No add-on ID provided']));
        }

        $addonID = $_POST['addonID'];

        // Check if add-on is being used in appointments
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_tbl WHERE addonID = ?");
        $check_stmt->bind_param("i", $addonID);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row['count'] > 0) {
            $check_stmt->close();
            die(json_encode([
                'success' => false, 
                'message' => 'This add-on cannot be deleted because it is being used in existing appointments.'
            ]));
        }

        $check_stmt->close();

        // Delete the add-on
        $delete_stmt = $conn->prepare("DELETE FROM addon_tbl WHERE addonID = ?");
        $delete_stmt->bind_param("i", $addonID);

        try {
            if ($delete_stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to delete add-on']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        $delete_stmt->close();
        exit();
    }
}

// Handle haircut deletion request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_haircut') {
    if (!isset($_POST['haircut_id'])) {
        die(json_encode(['success' => false, 'message' => 'No haircut ID provided']));
    }

    $haircut_id = $_POST['haircut_id'];

    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointment_tbl WHERE hcID = ?");
    $check_stmt->bind_param("i", $haircut_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $check_stmt->close();
        die(json_encode([
            'success' => false, 
            'message' => 'This haircut cannot be deleted because it is being used in existing appointments.'
        ]));
    }

    $check_stmt->close();

    $delete_stmt = $conn->prepare("DELETE FROM haircut_tbl WHERE hcID = ?");
    $delete_stmt->bind_param("i", $haircut_id);

    try {
        if ($delete_stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete haircut']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }

    $delete_stmt->close();
    exit();
}

// Handle get haircuts request
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_haircuts') {
    $category = $_GET['category'] ?? 'Basic';
    $sql = "SELECT * FROM haircut_tbl WHERE hcCategory = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    ob_start();
    while($row = $result->fetch_assoc()) {
        ?>
        <div class="haircut-item" data-category="<?php echo $row['hcCategory']; ?>" data-id="<?php echo $row['hcID']; ?>">
            <div class="position-relative">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['hcImage']); ?>" 
                     alt="<?php echo $row['hcName']; ?>">
                <button class="delete-btn btn btn-danger" style="display: none;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <p class="text-center mt-2"><?php echo $row['hcName']; ?></p>
        </div>
        <?php
    }
    $html = ob_get_clean();
    $stmt->close();
    echo $html;
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/calendar.css">
    <title>Options</title>
    <style>
        body {
            background-color: #090909;
        }
        .dashboard {
            color: white;
        }
        /* Sidebar styling */
        .sidebar {
            background-color: #F3CD32 !important;
            min-height: 100vh;
        }
        .list-group-item {
            background-color: transparent !important;
            border: none !important;
            color: black !important;
            border-radius: 10px !important;
            margin-bottom: 5px;
        }
        .list-group-item:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
        .list-group-item.active {
            background-color: black !important;
            color: #F3CD32 !important;
            border-radius: 10px !important;
        }
        .list-group-item.active i,
        .list-group-item.active span {
            color: #F3CD32 !important;
        }
        /* Avatar container styling */
        .avatar-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        .avatar-container .logo {
            margin-bottom: 20px;
        }
        .avatar-container img.avatar {
            display: block;
            margin: 0 auto;
        }
        .avatar-container h5 {
            margin-top: 10px;
            text-align: center;
        }
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            max-width: 800px;
        }
        .haircut-item img {
            width: 100%;
            aspect-ratio: 1/1;
            object-fit: cover;
            border-radius: 8px;
        }
        .haircut-item p {
            color: black;
            margin-top: 8px;
            font-weight: 500;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            border-radius: 50%;
            padding: 5px 10px;
        }
        .nav-tabs {
            border-bottom: none;
        }
        .nav-tabs .nav-link {
            color: #F3CD32;
            position: relative;
            background-color: transparent;
            border: none;
        }
        .nav-tabs .nav-link.active {
            color: #F3CD32;
            background-color: transparent;
        }
        .nav-tabs .nav-link:hover {
            background-color: transparent;
            color: #F3CD32;
        }
        .nav-tabs .nav-link.active::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -5px;
            height: 2px;
            background-color: #F3CD32;
        }
        .modal-content {
            border-radius: 10px;
            overflow: hidden;
        }
        .modal-header {
            background-color: #F3CD32;
            color: black;
        }
        .modal-title {
            font-weight: bold;
        }
        .form-control,
        .form-select {
            border: 2px solid #F3CD32;
            border-radius: 5px;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: #F3CD32;
            box-shadow: 0 0 0 0.25rem rgba(243, 205, 50, 0.25);
        }
        .btn-warning {
            background-color: #F3CD32;
            border-color: #F3CD32;
            color: black;
            font-weight: bold;
        }
        .btn-warning:hover {
            background-color: #e0b91d;
            border-color: #e0b91d;
        }
        /* Dark mode styles for the modal */
        .modal-content {
            background-color: #1f1f1f;
            color: #fff;
        }
        .modal-header {
            background-color: #1f1f1f;
            border-bottom: 1px solid #444;
        }
        .modal-title {
            color: #fff;
        }
        .form-label {
            color: #fff;
        }
        .form-control,
        .form-select {
            background-color: #333;
            color: #fff;
            border: 1px solid #444;
        }
        .form-control:focus,
        .form-select:focus {
            background-color: #333;
            color: #fff;
            border-color: #F3CD32;
            box-shadow: 0 0 0 0.25rem rgba(243, 205, 50, 0.25);
        }
        .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .services-container {
            max-width: 800px;
        }
        .btn-active {
            background-color: gray !important;
            border-color: gray !important;
            color: white !important;
        }
        /* Add these new styles for layout management */
        .main-content {
            margin-left: 260px; /* Increased width of sidebar */
            padding: 20px;
        }

        /* Media query for smaller screens */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
            }
            .sidebar {
                position: fixed;
                top: 0;
                left: -240px; /* Hide sidebar by default on mobile */
                width: 240px;
                z-index: 1000;
                transition: left 0.3s ease;
            }
            .sidebar.show {
                left: 0;
            }
        }

        /* Add a container for all the content */
        .content-wrapper {
            position: relative;
            min-height: 100vh;
        }

        /* Mobile toggle button styling */
        .mobile-toggle {
            position: fixed;
            top: 25px;
            left: 20px;
            z-index: 1000;
            background: none;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            display: none;
            color: #F3CD32;
            font-size: 24px;
        }

        /* Show toggle button only on smaller screens */
        @media (max-width: 991.98px) {
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 25px;
                left: 20px;
            }
            .sidebar {
                display: none;
                background-color: #F3CD32 !important;
            }
            .sidebar.show {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 240px;
                z-index: 999;
            }
        }

        /* Adjust main content area to account for sidebar */
        .container-xxl {
            padding-left: 260px; /* Width of sidebar + some padding */
            width: 100%;
            transition: padding-left 0.3s ease;
        }

        /* Sidebar positioning */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            z-index: 999;
        }

        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .container-xxl {
                padding-left: 15px; /* Reset padding on mobile */
            }
            
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
        }

        /* Adjust margin for content */
        @media (min-width: 992px) and (max-width: 1680px) {
            .ms-5 {
                margin-left: 0 !important;
            }
            
            .dashboard.mb-5.ms-5 {
                margin-left: 0 !important;
            }
            
            /* Gallery section adjustments */
            .haircut-gallery {
                margin-right: 15px;
            }
            
            /* Services section adjustments */
            .services-container {
                margin-right: 15px;
            }
            
            /* Table section adjustments */
            .table-responsive {
                margin-right: 15px;
            }
            
            /* Card adjustments */
            .card {
                margin-right: 15px;
            }
        }

        /* Update mobile styles */
        @media (max-width: 991.98px) {
            .mobile-toggle {
                display: block;
                position: fixed;
                top: 25px;
                left: 20px;
            }
            
            .sidebar {
                display: none;
                background-color: #F3CD32 !important;
            }
            
            .sidebar.show {
                display: block;
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                width: 240px;
                z-index: 999;
            }
            
            /* Adjust containers on mobile */
            .haircut-gallery,
            .services-container {
                margin: 0 10px;
            }
            
            /* Adjust table container on mobile */
            .table-responsive {
                margin: 0;
                padding: 0;
            }
            
            .card {
                margin: 0 10px;
            }
            
            /* Adjust buttons on mobile */
            .btn-warning {
                font-size: 14px;
                padding: 6px 12px;
            }
            
            /* Adjust gallery grid on mobile */
            .gallery-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
            }
            
            /* Adjust modal content on mobile */
            .modal-dialog {
                margin: 10px;
            }
            
            .modal-body {
                padding: 15px;
            }
            
            /* Adjust nav tabs on mobile */
            .nav-tabs {
                flex-wrap: nowrap;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            .nav-tabs .nav-link {
                white-space: nowrap;
                padding: 8px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-5 ms-5">Edit Haircut Gallery</h1>
                <button class="mobile-toggle d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <!-- Add Photo Button -->
            <div class="ms-5">
                <button class="btn btn-warning" id="addPhotoBtn">
                    + Add Haircut
                </button>

                <button class="btn btn-warning ms-2" id="editGalleryBtn">
                    Edit
                </button>

                <div class="haircut-gallery mt-1">
                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#Basic">Basic</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#Premium">Premium</a>
                        </li>
                    </ul>
                    
                    <div class="gallery-grid mt-3">
                        <?php
                        // Fetch haircuts from database with category filter
                        $sql = "SELECT * FROM haircut_tbl WHERE hcCategory = 'Basic'"; // Default to showing Basic
                        $result = $conn->query($sql);
                        
                        while($row = $result->fetch_assoc()) {
                            ?>
                            <div class="haircut-item" data-category="<?php echo $row['hcCategory']; ?>" data-id="<?php echo $row['hcID']; ?>">
                                <div class="position-relative">
                                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['hcImage']); ?>" 
                                         alt="<?php echo $row['hcName']; ?>">
                                    <button class="delete-btn btn btn-danger" style="display: none;">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="text-center mt-2"><?php echo $row['hcName']; ?></p>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    
                    <button class="btn btn-warning confirm-btn mt-3" id="confirmGalleryEditBtn" style="display: none;">
                        Confirm
                    </button>
                </div>

                <h1 class="dashboard mb-3">Edit Services</h1>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <!-- Add Services Button -->
                        <button class="btn btn-warning" id="addServiceBtn">
                            + Add Service
                        </button>

                        <!-- Edit Services Button -->
                        <button class="btn btn-warning ms-2" id="editServicesBtn">
                            Edit
                        </button>
                    </div>
                </div>

                <div class="services-container">
                    <table class="table table-striped" style="border-radius: 8px; overflow: hidden;">
                        <thead>
                            <tr>
                                <th>Service Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th class="actions-column" style="display: none;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch services from the database
                            $sql = "SELECT * FROM service_tbl";
                            $result = $conn->query($sql);

                            while ($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><?php echo $row['serviceName']; ?></td>
                                    <td><?php echo $row['serviceDesc']; ?></td>
                                    <td>₱<?php echo $row['servicePrice']; ?></td>
                                    <td class="actions-column" style="display: none;">
                                        <button class="btn btn-danger btn-sm delete-service-btn" data-id="<?php echo $row['serviceID']; ?>">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button class="btn btn-warning btn-sm edit-service-btn" 
                                            data-id="<?php echo $row['serviceID']; ?>" 
                                            data-name="<?php echo $row['serviceName']; ?>" 
                                            data-desc="<?php echo $row['serviceDesc']; ?>"
                                            data-price="<?php echo $row['servicePrice']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                    <button class="btn btn-warning mt-3" id="confirmServicesEditBtn" style="display: none;">
                        Confirm
                    </button>
                </div>

                <!-- Confirm Button -->
                <div class="text-center mb-3">
                    <button class="btn btn-warning" id="confirmEditBtn" style="display: none;">
                        Confirm
                    </button>
                </div>

                <!-- Add Service Modal -->
                <div class="modal fade" id="addServiceModal" tabindex="-1" aria-labelledby="addServiceModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="addServiceModalLabel">Add New Service</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="addServiceForm">
                                    <div class="mb-3">
                                        <label for="serviceName" class="form-label">Service Name</label>
                                        <input type="text" class="form-control" id="serviceName" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="serviceDesc" class="form-label">Service Description</label>
                                        <textarea class="form-control" id="serviceDesc" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="servicePrice" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="servicePrice" required>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-warning">Add Service</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Service Modal -->
                <div class="modal fade" id="editServiceModal" tabindex="-1" aria-labelledby="editServiceModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editServiceModalLabel">Edit Service</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="editServiceForm">
                                    <input type="hidden" id="editServiceID">
                                    <div class="mb-3">
                                        <label for="editServiceName" class="form-label">Service Name</label>
                                        <input type="text" class="form-control" id="editServiceName" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editServiceDesc" class="form-label">Service Description</label>
                                        <textarea class="form-control" id="editServiceDesc" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="editServicePrice" class="form-label">Price</label>
                                        <input type="number" class="form-control" id="editServicePrice" required>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-warning">Update Service</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <h1 class="dashboard mb-3">Edit Add-ons</h1>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <button class="btn btn-warning" id="addAddonBtn">
                            + Add Add-on
                        </button>
                        <button class="btn btn-warning ms-2" id="editAddonsBtn">
                            Edit
                        </button>
                    </div>
                </div>

                <!-- Add-ons Table -->
                <div class="services-container">
                    <div class="card border-0 rounded-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>Add-on Name</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th class="actions-column" style="display: none;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Fetch add-ons from the database
                                        $sql = "SELECT * FROM addon_tbl";
                                        $result = $conn->query($sql);

                                        while ($row = $result->fetch_assoc()) {
                                            ?>
                                            <tr>
                                                <td><?php echo $row['addonName']; ?></td>
                                                <td><?php echo $row['addonDesc']; ?></td>
                                                <td>₱<?php echo $row['addonPrice']; ?></td>
                                                <td class="actions-column" style="display: none;">
                                                    <button class="btn btn-danger btn-sm delete-addon-btn" data-id="<?php echo $row['addonID']; ?>">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                    <button class="btn btn-warning btn-sm edit-addon-btn" data-id="<?php echo $row['addonID']; ?>" data-name="<?php echo $row['addonName']; ?>" data-price="<?php echo $row['addonPrice']; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-warning mt-3" id="confirmAddonsEditBtn" style="display: none;">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addHaircutModal" tabindex="-1" aria-labelledby="addHaircutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addHaircutModalLabel">Add New Haircut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addHaircutForm">
                        <div class="mb-3">
                            <label for="haircutName" class="form-label">Haircut Name</label>
                            <input type="text" class="form-control" id="haircutName" required>
                        </div>
                        <div class="mb-3">
                            <label for="haircutCategory" class="form-label">Category</label>
                            <select class="form-select" id="haircutCategory" required>
                                <option value="Basic">Basic</option>
                                <option value="Premium">Premium</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="haircutPhoto" class="form-label">Upload Photo</label>
                            <input type="file" class="form-control" id="haircutPhoto" accept="image/*" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning">Add Haircut</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Add-on Modal -->
    <div class="modal fade" id="addAddonModal" tabindex="-1" aria-labelledby="addAddonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAddonModalLabel">Add New Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addAddonForm">
                        <div class="mb-3">
                            <label for="addonName" class="form-label">Add-on Name</label>
                            <input type="text" class="form-control" id="addonName" required>
                        </div>
                        <div class="mb-3">
                            <label for="addonDesc" class="form-label">Add-On Description</label>
                            <textarea class="form-control" id="addonDesc" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="addonPrice" class="form-label">Price</label>
                            <input type="number" class="form-control" id="addonPrice" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning">Add Add-on</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabs = document.querySelectorAll('.nav-link');
        const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                const category = this.getAttribute('href').replace('#', '');
                
                fetch(`options.php?action=get_haircuts&category=${category}`)
                    .then(response => response.text())
                    .then(html => {
                        document.querySelector('.gallery-grid').innerHTML = html;
                        attachDeleteListeners();
                    });
            });
        });

        // Edit button functionality
        document.getElementById('editGalleryBtn').addEventListener('click', function() {
            const deleteBtns = document.querySelectorAll('.delete-btn');
            const confirmBtn = document.querySelector('.confirm-btn');
            
            deleteBtns.forEach(btn => {
                btn.style.display = btn.style.display === 'none' ? 'block' : 'none';
            });
            
            confirmBtn.style.display = confirmBtn.style.display === 'none' ? 'block' : 'none';

            // Toggle the active class on the Edit Gallery button
            this.classList.toggle('btn-active');
            
            // Update button text color when active
            if (this.classList.contains('btn-active')) {
                this.style.color = 'white';
            } else {
                this.style.color = 'black';
            }
        });

        // Confirm button functionality
        document.querySelector('.confirm-btn').addEventListener('click', function() {
            const deleteBtns = document.querySelectorAll('.delete-btn');
            const confirmBtn = this;
            const editBtn = document.getElementById('editGalleryBtn');

            // Hide delete buttons and confirm button
            deleteBtns.forEach(btn => {
                btn.style.display = 'none';
            });
            confirmBtn.style.display = 'none';

            // Remove active state from Edit button
            editBtn.classList.remove('btn-active');
            editBtn.style.color = 'black';
        });

        // Function to attach delete event listeners
        function attachDeleteListeners() {
            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to delete this haircut?')) {
                        const haircut = this.closest('.haircut-item');
                        const haircut_id = haircut.dataset.id;

                        const formData = new FormData();
                        formData.append('action', 'delete_haircut');
                        formData.append('haircut_id', haircut_id);

                        fetch('options.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                haircut.remove();
                            } else {
                                document.getElementById('errorModalBody').textContent = data.message;
                                errorModal.show();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            document.getElementById('errorModalBody').textContent = 'An error occurred while deleting the haircut.';
                            errorModal.show();
                        });
                    }
                });
            });
        }

        // Initial attachment of delete listeners
        attachDeleteListeners();

        // Open the Add Haircut modal when the "Add Photo" button is clicked
        document.getElementById('addPhotoBtn').addEventListener('click', function() {
            const addHaircutModal = new bootstrap.Modal(document.getElementById('addHaircutModal'));
            addHaircutModal.show();
        });

        // Handle form submission
        document.getElementById('addHaircutForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('haircutName', document.getElementById('haircutName').value);
            formData.append('haircutCategory', document.getElementById('haircutCategory').value);
            formData.append('haircutPhoto', document.getElementById('haircutPhoto').files[0]);

            // Send the form data to the server using fetch
            fetch('add_haircut.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Refresh the gallery grid
                    const activeTab = document.querySelector('.nav-link.active');
                    const category = activeTab.getAttribute('href').replace('#', '');
                    fetch(`options.php?action=get_haircuts&category=${category}`)
                        .then(response => response.text())
                        .then(html => {
                            document.querySelector('.gallery-grid').innerHTML = html;
                            attachDeleteListeners();
                        });

                    // Close the modal
                    const addHaircutModal = bootstrap.Modal.getInstance(document.getElementById('addHaircutModal'));
                    addHaircutModal.hide();

                    // Reset the form
                    document.getElementById('addHaircutForm').reset();

                    // Show success message
                    alert('Haircut added successfully!');
                } else {
                    // Show error message
                    alert('Error adding haircut: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the haircut. Please check the console for more details.');
            });
        });

        // Open the Add Service modal when the "Add Services" button is clicked
        document.getElementById('addServiceBtn').addEventListener('click', function() {
            const addServiceModal = new bootstrap.Modal(document.getElementById('addServiceModal'));
            addServiceModal.show();
        });

        // Handle form submission for adding a new service
        document.getElementById('addServiceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('serviceName', document.getElementById('serviceName').value);
            formData.append('serviceDesc', document.getElementById('serviceDesc').value);
            formData.append('servicePrice', document.getElementById('servicePrice').value);

            // Send the form data to the server using fetch
            fetch('add_service.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const addServiceModal = bootstrap.Modal.getInstance(document.getElementById('addServiceModal'));
                    addServiceModal.hide();

                    // Reset the form
                    document.getElementById('addServiceForm').reset();

                    // Show success message
                    alert('Service added successfully!');

                    // Refresh the services table
                    location.reload();
                } else {
                    // Show error message
                    alert('Error adding service: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the service. Please check the console for more details.');
            });
        });

        // Edit Services button functionality
        document.getElementById('editServicesBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.services-container > .table .actions-column');
            const confirmBtn = document.getElementById('confirmServicesEditBtn');
            
            actionsColumn.forEach(column => {
                column.style.display = column.style.display === 'none' ? 'table-cell' : 'none';
            });

            // Toggle the Confirm button
            confirmBtn.style.display = confirmBtn.style.display === 'none' ? 'block' : 'none';

            // Change the color of the Edit Services button
            this.classList.toggle('btn-active');
            
            // Update button text color when active
            if (this.classList.contains('btn-active')) {
                this.style.color = 'white';
            } else {
                this.style.color = 'black';
            }
        });

        // Update the Confirm button functionality
        document.getElementById('confirmServicesEditBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.services-container > .table .actions-column');
            const editBtn = document.getElementById('editServicesBtn');
            
            actionsColumn.forEach(column => {
                column.style.display = 'none'; // Hide action buttons
            });

            // Hide the Confirm button
            this.style.display = 'none';
            
            // Remove active state from Edit button
            editBtn.classList.remove('btn-active');
            editBtn.style.color = 'black';
        });

        // Delete service functionality
        document.querySelectorAll('.delete-service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this service?')) {
                    const serviceID = this.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete_service');
                    formData.append('serviceID', serviceID);

                    fetch('options.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').remove();
                        } else {
                            document.getElementById('errorModalBody').textContent = data.message;
                            errorModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('errorModalBody').textContent = 'An error occurred while deleting the service.';
                        errorModal.show();
                    });
                }
            });
        });

        // Edit service functionality
        document.querySelectorAll('.edit-service-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const serviceID = this.dataset.id;
                const serviceName = this.dataset.name;
                const serviceDesc = this.dataset.desc;
                const servicePrice = this.dataset.price;

                // Populate the modal fields
                document.getElementById('editServiceID').value = serviceID;
                document.getElementById('editServiceName').value = serviceName;
                document.getElementById('editServiceDesc').value = serviceDesc;
                document.getElementById('editServicePrice').value = servicePrice;

                // Show the modal
                const editServiceModal = new bootstrap.Modal(document.getElementById('editServiceModal'));
                editServiceModal.show();
            });
        });

        // Handle form submission for editing a service
        document.getElementById('editServiceForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('serviceID', document.getElementById('editServiceID').value);
            formData.append('serviceName', document.getElementById('editServiceName').value);
            formData.append('serviceDesc', document.getElementById('editServiceDesc').value);
            formData.append('servicePrice', document.getElementById('editServicePrice').value);

            // Send the updated data to the server using fetch
            fetch('edit_service.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    alert('Service updated successfully!');
                    
                    // Refresh the page to show updated data
                    location.reload();

                    // Close the modal
                    const editServiceModal = bootstrap.Modal.getInstance(document.getElementById('editServiceModal'));
                    editServiceModal.hide();
                } else {
                    document.getElementById('errorModalBody').textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorModalBody').textContent = 'An error occurred while updating the service.';
                errorModal.show();
            });
        });

        // Open the Add Add-on modal when the "Add Add-ons" button is clicked
        document.getElementById('addAddonBtn').addEventListener('click', function() {
            const addAddonModal = new bootstrap.Modal(document.getElementById('addAddonModal'));
            addAddonModal.show();
        });

        // Handle form submission for adding a new add-on
        document.getElementById('addAddonForm').addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData();
            formData.append('addonName', document.getElementById('addonName').value);
            formData.append('addonDesc', document.getElementById('addonDesc').value);
            formData.append('addonPrice', document.getElementById('addonPrice').value);

            // Send the form data to the server using fetch
            fetch('add_addon.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const addAddonModal = bootstrap.Modal.getInstance(document.getElementById('addAddonModal'));
                    addAddonModal.hide();

                    // Reset the form
                    document.getElementById('addAddonForm').reset();

                    // Show success message
                    alert('Add-on added successfully!');

                    // Refresh the page to show the new add-on
                    location.reload();
                } else {
                    // Show error message
                    document.getElementById('errorModalBody').textContent = data.message;
                    errorModal.show();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('errorModalBody').textContent = 'An error occurred while adding the add-on.';
                errorModal.show();
            });
        });

        // Delete add-on functionality
        document.querySelectorAll('.delete-addon-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (confirm('Are you sure you want to delete this add-on?')) {
                    const addonID = this.dataset.id;
                    const formData = new FormData();
                    formData.append('action', 'delete_addon');
                    formData.append('addonID', addonID);

                    fetch('options.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            this.closest('tr').remove();
                        } else {
                            document.getElementById('errorModalBody').textContent = data.message;
                            errorModal.show();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        document.getElementById('errorModalBody').textContent = 'An error occurred while deleting the add-on.';
                        errorModal.show();
                    });
                }
            });
        });

        // Edit Add-ons button functionality
        document.getElementById('editAddonsBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.services-container .card .table .actions-column');
            const confirmBtn = document.getElementById('confirmAddonsEditBtn');
            
            actionsColumn.forEach(column => {
                column.style.display = column.style.display === 'none' ? 'table-cell' : 'none';
            });

            // Toggle the Confirm button
            confirmBtn.style.display = confirmBtn.style.display === 'none' ? 'block' : 'none';

            // Change the color of the Edit Add-ons button
            this.classList.toggle('btn-active');
            
            // Update button text color when active
            if (this.classList.contains('btn-active')) {
                this.style.color = 'white';
            } else {
                this.style.color = 'black';
            }
        });

        // Confirm Add-ons button functionality
        document.getElementById('confirmAddonsEditBtn').addEventListener('click', function() {
            const actionsColumn = document.querySelectorAll('.services-container .card .table .actions-column');
            const editBtn = document.getElementById('editAddonsBtn');
            
            // Hide action buttons
            actionsColumn.forEach(column => {
                column.style.display = 'none';
            });

            // Hide the Confirm button
            this.style.display = 'none';

            // Remove active state from Edit button
            editBtn.classList.remove('btn-active');
            editBtn.style.color = 'black';
        });
    });
    </script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarMenu');
            const toggle = document.querySelector('.mobile-toggle');
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.querySelector('.mobile-toggle');
            if (toggleButton) {
                toggleButton.setAttribute('onclick', 'toggleSidebar()');
            }
        });
    </script>

    <!-- Add the sidebar navigation after the main content div -->
    <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
        <div class="position-sticky">
            <div class="list-group list-group-flush mx-3 mt-5">
                <div class="avatar-container">
                    <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
                    <img src="css/images/admin.jpg" alt="Avatar" width="140" height="140" style="border: 5px solid #000000; border-radius: 50%;" class="avatar">
                    <h5>Admin</h5>
                </div>
                <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                </a>
                <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-users fa-fw me-3"></i><span>Appointment</span>
                </a>
                <a href="a_history.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
                </a>
                <a href="earnings.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-money-bill-trend-up fa-fw me-3"></i><span>Earnings</span>
                </a>
                <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-scissors fa-fw me-3"></i><span>Barbers</span>
                </a>
                <a href="options.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Options</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

</body>
</html>
