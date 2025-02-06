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
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-3 ms-5">Edit Landing Page Text</h1>
                <button class="mobile-toggle d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            <div class="ms-5">
                <div class="services-container">
                    <div class="card border-0 rounded-4">
                        <div class="card-body p-4">
                            <!-- Welcome Message -->
                            <div class="mb-4">
                                <h5>Welcome Message</h5>
                                <div class="d-flex align-items-center">
                                    <span id="welcomeText" class="me-3">
                                        <?php include 'landing_text.php'; echo htmlspecialchars($welcomeText); ?>
                                    </span>
                                    <button class="btn btn-warning btn-sm" onclick="editText('welcome')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>

                            <!-- Main Heading -->
                            <div class="mb-4">
                                <h5>Main Heading</h5>
                                <div class="d-flex align-items-center">
                                    <span id="headingText" class="me-3">
                                        <?php echo htmlspecialchars($headingText); ?>
                                    </span>
                                    <button class="btn btn-warning btn-sm" onclick="editText('heading')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
                            </div>

                            <!-- Subheading -->
                            <div class="mb-4">
                                <h5>Subheading</h5>
                                <div class="d-flex align-items-center">
                                    <span id="subheadingText" class="me-3">
                                        <?php echo htmlspecialchars($subheadingText); ?>
                                    </span>
                                    <button class="btn btn-warning btn-sm" onclick="editText('subheading')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                </div>
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

</body>
</html>
