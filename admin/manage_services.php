<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

include 'db_connect.php';

// Fetch the number of pending appointments
$notificationQuery = "SELECT COUNT(*) AS pending_count FROM appointment_tbl WHERE status = 'Pending'";
$notificationResult = mysqli_query($conn, $notificationQuery);
$notificationData = mysqli_fetch_assoc($notificationResult);
$pendingCount = $notificationData['pending_count'];

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
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="css/table.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <title>Manage Services</title>
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
                    <div class="card border-0 rounded-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <td>Service Name</td>
                                            <td>Description</td>
                                            <td>Price</td>
                                            <td class="actions-column" style="display: none;">Actions</td>
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
                            </div>
                        </div>
                    </div>
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
                                            <td>Add-on Name</td>
                                            <td>Description</td>
                                            <td>Price</td>
                                            <td class="actions-column" style="display: none;">Actions</td>
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
                                                    <button class="btn btn-warning btn-sm edit-addon-btn" 
                                                        data-id="<?php echo $row['addonID']; ?>" 
                                                        data-name="<?php echo $row['addonName']; ?>" 
                                                        data-desc="<?php echo $row['addonDesc']; ?>"
                                                        data-price="<?php echo $row['addonPrice']; ?>">
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

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="successMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this haircut?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Service Confirmation Modal -->
    <div class="modal fade" id="deleteServiceConfirmModal" tabindex="-1" aria-labelledby="deleteServiceConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteServiceConfirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this service?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmServiceDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Add-on Confirmation Modal -->
    <div class="modal fade" id="deleteAddonConfirmModal" tabindex="-1" aria-labelledby="deleteAddonConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteAddonConfirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this add-on?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmAddonDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Add-on Modal -->
    <div class="modal fade" id="editAddonModal" tabindex="-1" aria-labelledby="editAddonModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAddonModalLabel">Edit Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAddonForm">
                        <input type="hidden" id="editAddonID">
                        <div class="mb-3">
                            <label for="editAddonName" class="form-label">Add-on Name</label>
                            <input type="text" class="form-control" id="editAddonName" required>
                        </div>
                        <div class="mb-3">
                            <label for="editAddonDesc" class="form-label">Add-on Description</label>
                            <textarea class="form-control" id="editAddonDesc" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="editAddonPrice" class="form-label">Price</label>
                            <input type="number" class="form-control" id="editAddonPrice" required>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning">Update Add-on</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
        <div class="position-sticky">
            <div class="list-group list-group-flush mx-3 mt-5">
                <div class="avatar-container">
                    <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo">
                    <h5>Admin</h5>
                </div>
                <a href="a_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                </a>

                <a href="appointments.php" class="list-group-item list-group-item-action py-2 ripple d-flex align-items-center justify-content-between">
                    <div>
                        <i class="fa-solid fa-users fa-fw me-3"></i>
                        <span>Appointment</span>
                    </div>
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
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
                <a href="manage_services.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Manage Services</span>
                </a>
                <a href="configuration.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Website Configuration</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="js/manage_services.js"></script>

</body>
</html>
