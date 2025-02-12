<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

include 'db_connect.php';

// Fetch the number of pending appointments without an assigned barber
$notificationQuery = "
    SELECT COUNT(*) AS notif_count 
    FROM appointment_tbl a
    LEFT JOIN barb_apps_tbl b ON a.appointmentID = b.appointmentID
    WHERE a.status = 'Pending' AND b.barberID IS NULL";

$notificationResult = mysqli_query($conn, $notificationQuery);
$notificationData = mysqli_fetch_assoc($notificationResult);
$notificationCount = $notificationData['notif_count'];
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
    <title>Website Configuration</title>
    <style>
    .action-buttons {
        white-space: nowrap;
    }

    .modal-header,
    .modal-footer,
    .modal-body {
        border: none;
    }

    .modal-header .modal-title {
        width: 100%;
        text-align: center;
    }
    </style>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <button class="mobile-toggle d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="ms-5">
                <!-- Landing Page Text Section -->
                <h1 class="dashboard mb-3">Edit Landing Page Text</h1>
                <div class="d-flex justify-content-start mb-3 gap-2">
                    <button class="btn btn-warning" onclick="toggleTextEditMode()">
                        <i></i> Edit
                    </button>
                    <button class="btn btn-danger" onclick="cancelTextEdit()" style="display: none;">
                        <i class="fas fa-times"></i>
                        Cancel
                    </button>
                </div>

                <div class="services-container mb-4">
                    <div class="card border-0 rounded-4">
                        <div class="card-body p-4">
                            <!-- Welcome Message -->
                            <div class="mb-4">
                                <h5>Welcome Message</h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span id="welcomeText" class="me-3" style="display: block;">
                                            <?php include 'landing_text.php'; echo htmlspecialchars($welcomeText); ?>
                                        </span>
                                        <textarea id="welcomeTextEdit" class="form-control" style="display: none;"><?php echo htmlspecialchars($welcomeText); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Heading -->
                            <div class="mb-4">
                                <h5>Main Heading</h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span id="headingText" class="me-3" style="display: block;">
                                            <?php echo htmlspecialchars($headingText); ?>
                                        </span>
                                        <textarea id="headingTextEdit" class="form-control" style="display: none;"><?php echo htmlspecialchars($headingText); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Subheading -->
                            <div class="mb-4">
                                <h5>Subheading</h5>
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <span id="subheadingText" class="me-3" style="display: block;">
                                            <?php echo htmlspecialchars($subheadingText); ?>
                                        </span>
                                        <textarea id="subheadingTextEdit" class="form-control" style="display: none;"><?php echo htmlspecialchars($subheadingText); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Carousel Management Section -->
                <h1 class="dashboard mb-3">Edit Carousel</h1>
                <div class="d-flex justify-content-start mb-3 gap-2">
                    <button class="btn btn-warning" onclick="addCarousel()">
                        <i class="fas fa-plus"></i> Add Image
                    </button>
                    <button class="btn btn-warning" onclick="toggleEditMode()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>

                <div class="services-container">
                    <div class="card border-0 rounded-4">
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th class="actions-column" style="display: none;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $carouselQuery = "SELECT * FROM barberpic_tbl";
                                        $carouselResult = mysqli_query($conn, $carouselQuery);
                                        while ($row = mysqli_fetch_assoc($carouselResult)) {
                                            // Convert the BLOB to base64 for JSON
                                            $row['barberPic'] = base64_encode($row['barberPic']);
                                            $rowData = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                                            
                                            echo "<tr>";
                                            echo "<td><img src='data:image/jpeg;base64," . $row['barberPic'] . "' alt='Barber' style='width: 50px; height: 50px; object-fit: cover;'></td>";
                                            echo "<td>" . htmlspecialchars($row['barberName']) . "</td>";
                                            echo "<td>" . (strlen($row['barbDesc']) > 40 ? htmlspecialchars(substr($row['barbDesc'], 0, 40)) . "..." : htmlspecialchars($row['barbDesc'])) . "</td>";
                                            echo "<td>
                                                <div class='action-buttons' style='display: none;'>
                                                    <button class='btn btn-warning btn-sm me-2' onclick='editCarousel($rowData)'><i class='fas fa-edit'></i></button>
                                                    <button class='btn btn-danger btn-sm' onclick='deleteCarousel(" . $row['barberpicID'] . ")'><i class='fas fa-trash'></i></button>
                                                </div>
                                            </td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Text Modal -->
            <div class="modal fade" id="editTextModal" tabindex="-1" aria-labelledby="editTextModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editTextModalLabel">Edit Text</h5>
                        </div>
                        <div class="modal-body">
                            <form id="editTextForm">
                                <input type="hidden" id="textType">
                                <div class="mb-3">
                                    <label for="newText" class="form-label">New Text</label>
                                    <textarea class="form-control" id="newText" rows="3" required></textarea>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning">Update Text</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Barber Modal -->
            <div class="modal fade" id="addCarouselModal" tabindex="-1" aria-labelledby="addCarouselModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title w-100" id="addCarouselModalLabel">Add New Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="addCarouselForm" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="newBarberName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="newBarberName" name="barberName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newBarberDesc" class="form-label">Description</label>
                                    <textarea class="form-control" id="newBarberDesc" name="barberDesc" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="newBarberPic" class="form-label">Image</label>
                                    <input type="file" class="form-control" id="newBarberPic" name="barberPic" accept="image/*" required>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning">Add Image</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Carousel Modal -->
            <div class="modal fade" id="editCarouselModal" tabindex="-1" aria-labelledby="editCarouselModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title w-100" id="editCarouselModalLabel">Edit Image</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editCarouselForm" enctype="multipart/form-data">
                                <input type="hidden" id="carouselId" name="barberpicID">
                                <div class="mb-3">
                                    <label for="barberName" class="form-label">Name</label>
                                    <input type="text" class="form-control" id="barberName" name="barberName" required>
                                </div>
                                <div class="mb-3">
                                    <label for="barberDesc" class="form-label">Description</label>
                                    <textarea class="form-control" id="barberDesc" name="barberDesc" rows="3" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="barberPic" class="form-label">Image</label>
                                    <input type="file" class="form-control" id="barberPic" name="barberPic" accept="image/*">
                                    <small class="form-text text-muted">Leave empty to keep current image</small>
                                    <div id="currentImage" class="mt-2"></div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" class="btn btn-warning">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="errorModalBody">
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100" id="successModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="successMessage"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title w-100" id="deleteConfirmModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this carousel entry?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    
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
                    <?php if ($notificationCount > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $notificationCount; ?></span>
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
                <a href="manage_services.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Manage Services</span>
                </a>
                <a href="configuration.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Website Configuration</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

    <script>
    function editText(type) {
        // Get the modal instance
        const editTextModal = new bootstrap.Modal(document.getElementById('editTextModal'));
        const form = document.getElementById('editTextForm');
        const textInput = document.getElementById('newText');
        const textType = document.getElementById('textType');
        
        // Set current text as default value
        textInput.value = document.getElementById(type + 'Text').innerText.trim();
        textType.value = type;
        
        editTextModal.show();
    }

    document.getElementById('editTextForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData();
        formData.append('action', 'update_text');
        formData.append('type', document.getElementById('textType').value);
        formData.append('text', document.getElementById('newText').value);
        
        fetch('update_landing_text.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the text on the page
                const textType = document.getElementById('textType').value;
                const newText = document.getElementById('newText').value;
                document.getElementById(textType + 'Text').innerText = newText;
                
                // Close the edit modal
                const editTextModal = bootstrap.Modal.getInstance(document.getElementById('editTextModal'));
                editTextModal.hide();
                
                // Show success message in modal
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                document.getElementById('successMessage').innerText = 'Text updated successfully!';
                successModal.show();
            } else {
                // Show error in error modal
                document.getElementById('errorModalBody').textContent = 'Error updating text: ' + data.message;
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error in error modal
            document.getElementById('errorModalBody').textContent = 'An error occurred while updating the text.';
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            errorModal.show();
        });
    });

    // Add event listener for modal close
    document.getElementById('editTextModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('editTextForm').reset();
    });

    document.getElementById('successModal').addEventListener('hidden.bs.modal', function () {
        const messageElement = this.querySelector('.modal-body p');
        if (messageElement) messageElement.textContent = '';
    });

    document.getElementById('errorModal').addEventListener('hidden.bs.modal', function () {
        const messageElement = this.querySelector('.modal-body');
        if (messageElement) messageElement.textContent = '';
    });
    </script>

    <script>
    function editCarousel(data) {
        console.log('Edit data:', data);
        const modal = new bootstrap.Modal(document.getElementById('editCarouselModal'));
        
        // Store original values for comparison
        window.originalCarouselData = {
            name: data.barberName,
            desc: data.barbDesc
        };
        
        document.getElementById('carouselId').value = data.barberpicID;
        document.getElementById('barberName').value = data.barberName;
        document.getElementById('barberDesc').value = data.barbDesc;
        
        // Show current image as thumbnail
        const currentImage = document.getElementById('currentImage');
        // Check if barberPic is already base64 encoded
        const imgSrc = data.barberPic.startsWith('data:image') 
            ? data.barberPic 
            : `data:image/jpeg;base64,${data.barberPic}`;
            
        currentImage.innerHTML = `<img src="${imgSrc}" alt="Current Image" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">`;
        
        // Initially disable update button
        const updateButton = document.querySelector('#editCarouselForm button[type="submit"]');
        updateButton.disabled = true;
        
        modal.show();
    }

    // Add change detection to form inputs
    document.getElementById('editCarouselForm').addEventListener('change', checkFormChanges);
    document.getElementById('barberName').addEventListener('input', checkFormChanges);
    document.getElementById('barberDesc').addEventListener('input', checkFormChanges);

    function checkFormChanges() {
        const nameChanged = document.getElementById('barberName').value !== window.originalCarouselData.name;
        const descChanged = document.getElementById('barberDesc').value !== window.originalCarouselData.desc;
        const fileChanged = document.getElementById('barberPic').files.length > 0;
        
        const updateButton = document.querySelector('#editCarouselForm button[type="submit"]');
        updateButton.disabled = !(nameChanged || descChanged || fileChanged);
    }

    // Add form submission handler
    document.getElementById('editCarouselForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('update_carousel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Close the edit modal
            const editModal = bootstrap.Modal.getInstance(document.getElementById('editCarouselModal'));
            editModal.hide();
            
            if (data.success) {
                // Show success message and reload page
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                document.getElementById('successMessage').innerText = 'Updated successfully!';
                successModal.show();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                // Show error
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorModalBody').textContent = 'Error updating image: ' + data.message;
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalBody').textContent = 'An error occurred while updating the image.';
            errorModal.show();
        });
    });

    // Reset form and button state when modal is closed
    document.getElementById('editCarouselModal').addEventListener('hidden.bs.modal', function () {
        document.getElementById('editCarouselForm').reset();
        document.getElementById('currentImage').innerHTML = '';
        const updateButton = document.querySelector('#editCarouselForm button[type="submit"]');
        updateButton.disabled = true;
        window.originalCarouselData = null;
    });
    </script>

    <script>
    function addCarousel() {
        const modal = new bootstrap.Modal(document.getElementById('addCarouselModal'));
        modal.show();
    }

    document.getElementById('addCarouselForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        formData.append('action', 'add_carousel');
        
        // Close the add modal
        const addModal = bootstrap.Modal.getInstance(document.getElementById('addCarouselModal'));
        addModal.hide();
        
        fetch('add_carousel.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message and reload page
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                document.getElementById('successMessage').innerText = 'Added successfully!';
                successModal.show();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                // Show error
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorModalBody').textContent = 'Error adding barber: ' + data.message;
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalBody').textContent = 'An error occurred while adding the barber.';
            errorModal.show();
        });
    });
    </script>

    <script>
    let deleteId = null;

    function deleteCarousel(id) {
        deleteId = id;
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        modal.show();
    }

    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (deleteId === null) return;
        
        fetch('delete_carousel.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                barberId: deleteId
            })
        })
        .then(response => response.json())
        .then(data => {
            // Close the confirmation modal
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal'));
            deleteModal.hide();
            
            if (data.success) {
                // Show success message and reload page
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                document.getElementById('successMessage').innerText = 'Carousel entry deleted successfully!';
                successModal.show();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                // Show error
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorModalBody').textContent = 'Error deleting carousel entry: ' + data.message;
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalBody').textContent = 'An error occurred while deleting the carousel entry.';
            errorModal.show();
        });
    });

    // Reset deleteId when modal is closed
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function () {
        deleteId = null;
    });
    </script>

    <script>
    let editModeEnabled = false;

    function toggleEditMode() {
        editModeEnabled = !editModeEnabled;
        const actionButtons = document.querySelectorAll('.action-buttons');
        const actionHeader = document.querySelector('.actions-column');
        const editButton = document.querySelector('[onclick="toggleEditMode()"]');
        
        actionButtons.forEach(buttons => {
            buttons.style.display = editModeEnabled ? 'block' : 'none';
        });
        
        // Toggle visibility of Actions column header
        if (actionHeader) {
            actionHeader.style.display = editModeEnabled ? 'table-cell' : 'none';
        }
        
        // Update the edit button text
        editButton.innerHTML = editModeEnabled ? 
            '<i class="fas fa-times"></i> Cancel' : 
            '<i class="fas fa-edit"></i> Edit';
            
        editButton.classList.toggle('btn-danger', editModeEnabled);
        editButton.classList.toggle('btn-warning', !editModeEnabled);
    }
    </script>

    <script>
    let textEditModeEnabled = false;
    let originalTexts = {};

    function toggleTextEditMode() {
        textEditModeEnabled = !textEditModeEnabled;
        const editButton = document.querySelector('[onclick="toggleTextEditMode()"]');
        const cancelButton = document.querySelector('[onclick="cancelTextEdit()"]');
        const textDisplays = document.querySelectorAll('#welcomeText, #headingText, #subheadingText');
        const textEdits = document.querySelectorAll('#welcomeTextEdit, #headingTextEdit, #subheadingTextEdit');
        
        if (textEditModeEnabled) {
            // Store original values when entering edit mode
            originalTexts = {
                welcome: document.getElementById('welcomeText').innerText,
                heading: document.getElementById('headingText').innerText,
                subheading: document.getElementById('subheadingText').innerText
            };

            // Add input event listeners when entering edit mode
            document.getElementById('welcomeTextEdit').addEventListener('input', checkTextChanges);
            document.getElementById('headingTextEdit').addEventListener('input', checkTextChanges);
            document.getElementById('subheadingTextEdit').addEventListener('input', checkTextChanges);
        }

        // Toggle visibility of text displays and textareas
        textDisplays.forEach(display => {
            display.style.display = textEditModeEnabled ? 'none' : 'block';
        });
        
        textEdits.forEach(edit => {
            edit.style.display = textEditModeEnabled ? 'block' : 'none';
        });
        
        // Update the edit button text and style
        editButton.innerHTML = textEditModeEnabled ? 
            '<i class="fas fa-check"></i> Save' : 
            '<i class="fas fa-edit"></i> Edit';
            
        editButton.classList.toggle('btn-success', textEditModeEnabled);
        editButton.classList.toggle('btn-warning', !textEditModeEnabled);

        // Show/hide cancel button
        cancelButton.style.display = textEditModeEnabled ? 'block' : 'none';

        // Initially disable save button when entering edit mode
        if (textEditModeEnabled) {
            editButton.disabled = true;
        }

        // If saving (edit mode was enabled and now being disabled)
        if (!textEditModeEnabled) {
            saveTexts();
        }
    }

    function checkTextChanges() {
        const welcomeChanged = document.getElementById('welcomeTextEdit').value !== originalTexts.welcome;
        const headingChanged = document.getElementById('headingTextEdit').value !== originalTexts.heading;
        const subheadingChanged = document.getElementById('subheadingTextEdit').value !== originalTexts.subheading;
        
        const saveButton = document.querySelector('[onclick="toggleTextEditMode()"]');
        saveButton.disabled = !(welcomeChanged || headingChanged || subheadingChanged);
    }

    function cancelTextEdit() {
        // Restore original values
        document.getElementById('welcomeTextEdit').value = originalTexts.welcome;
        document.getElementById('headingTextEdit').value = originalTexts.heading;
        document.getElementById('subheadingTextEdit').value = originalTexts.subheading;

        // Reset display
        const editButton = document.querySelector('[onclick="toggleTextEditMode()"]');
        const cancelButton = document.querySelector('[onclick="cancelTextEdit()"]');
        const textDisplays = document.querySelectorAll('#welcomeText, #headingText, #subheadingText');
        const textEdits = document.querySelectorAll('#welcomeTextEdit, #headingTextEdit, #subheadingTextEdit');

        textDisplays.forEach(display => {
            display.style.display = 'block';
        });
        
        textEdits.forEach(edit => {
            edit.style.display = 'none';
        });

        // Reset button states
        editButton.innerHTML = '<i class="fas fa-edit"></i> Edit';
        editButton.classList.remove('btn-success');
        editButton.classList.add('btn-warning');
        editButton.disabled = false;  // Make sure to re-enable the edit button
        cancelButton.style.display = 'none';

        // Reset edit mode
        textEditModeEnabled = false;
    }

    function saveTexts() {
        const formData = new FormData();
        formData.append('action', 'update_text');
        formData.append('welcomeText', document.getElementById('welcomeTextEdit').value);
        formData.append('headingText', document.getElementById('headingTextEdit').value);
        formData.append('subheadingText', document.getElementById('subheadingTextEdit').value);
        
        fetch('update_landing_text.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the displayed text
                document.getElementById('welcomeText').innerText = document.getElementById('welcomeTextEdit').value;
                document.getElementById('headingText').innerText = document.getElementById('headingTextEdit').value;
                document.getElementById('subheadingText').innerText = document.getElementById('subheadingTextEdit').value;
                
                // Show success message
                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                document.getElementById('successMessage').innerText = 'Text updated successfully!';
                successModal.show();
            } else {
                // Show error
                const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                document.getElementById('errorModalBody').textContent = 'Error updating text: ' + data.message;
                errorModal.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorModalBody').textContent = 'An error occurred while updating the text.';
            errorModal.show();
        });
    }
    </script>

</body>
</html>
