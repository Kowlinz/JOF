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

// Query to fetch barber data
$sql = "SELECT * FROM barbers_tbl";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Barbers</title>
</head>
<body>
    <?php include 'db_connect.php'; ?>

    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-5 ms-5">Barbers</h1>
                <button class="mobile-toggle d-lg-none">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <!-- Add Barber Button -->
            <div class="row ms-5 mb-4">
                <div class="col-12">
                    <button class="btn btn-warning" onclick="window.location.href='registration.php'">
                        + Add Barber
                    </button>
                </div>
            </div>

            <!-- Bottom Section: Barbers Table -->
            <div class="row ms-5">
                <div class="col-12">
                    <div class="card border-0 rounded-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone Number</th>
                                            <th>Availability</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        if ($result->num_rows > 0) {
                                            $index = 1; // Counter for table row numbers
                                            while ($row = $result->fetch_assoc()) {
                                                echo "<tr>";
                                                echo "<td>" . $index++ . "</td>";
                                                echo "<td>" . htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                                echo "<td>" . htmlspecialchars($row['contactNum']) . "</td>";

                                                // Availability dropdown with form
                                                echo "<td>";
                                                echo "<form method='POST' action='update_availability.php' class='availability-form'>";
                                                echo "<input type='hidden' name='barberID' value='" . $row['barberID'] . "'>";
                                                echo "<select name='availability' class='form-select availability-select' data-original='" . $row['availability'] . "'>";
                                                echo "<option value='Available'" . ($row['availability'] === 'Available' ? " selected" : "") . ">Available</option>";
                                                echo "<option value='Unavailable'" . ($row['availability'] === 'Unavailable' ? " selected" : "") . ">Unavailable</option>";
                                                echo "</select>";
                                                echo "</form>";
                                                echo "</td>";

                                                // Delete Icon Form
                                                echo "<td>";
                                                echo "<button type='button' class='btn btn-delete delete-barber-btn' 
                                                        data-id='" . $row['barberID'] . "' 
                                                        >";
                                                echo "<i class='fa-solid fa-trash-alt fa-lg'></i>";
                                                echo "</button>";
                                                echo "</td>";

                                                echo "</tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5'>No barbers found</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
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
                <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-scissors fa-fw me-3"></i><span>Barbers</span>
                </a>
                <a href="manage_services.php" class="list-group-item list-group-item-action py-2 ripple">
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

    <!-- Add necessary scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>

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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Delete barber functionality
            document.querySelectorAll('.delete-barber-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const barberID = this.dataset.id;
                    const row = this.closest('tr');
                    
                    // Show confirmation modal
                    const deleteModal = new bootstrap.Modal(document.getElementById('deleteBarberModal'));
                    deleteModal.show();

                    // Handle delete confirmation
                    document.getElementById('confirmBarberDelete').onclick = function() {
                        const formData = new FormData();
                        formData.append('barberID', barberID);

                        fetch('delete_barber.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            deleteModal.hide();
                            if (data.success) {
                                row.remove();
                                // Show success message
                                const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                                document.getElementById('successMessage').innerText = 'Barber deleted successfully!';
                                successModal.show();
                            } else {
                                alert(data.message || 'Error deleting barber');
                            }
                        })
                        .catch(error => {
                            deleteModal.hide();
                            console.error('Error:', error);
                            alert('An error occurred while deleting the barber');
                        });
                    };
                });
            });
        });
    </script>

    <!-- Delete Barber Confirmation Modal -->
    <div class="modal fade" id="deleteBarberModal" tabindex="-1" aria-labelledby="deleteBarberModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBarberModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this barber?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border: none;">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmBarberDelete">Delete</button>
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

    <!-- Add this modal for availability change confirmation -->
    <div class="modal fade" id="availabilityModal" tabindex="-1" aria-labelledby="availabilityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="availabilityModalLabel">Confirm Availability Change</h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to change this barber's availability?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" id="confirmAvailability">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Update the availability success modal style -->
    <style>
        /* Modal styles */
        .modal-content {
            background-color: #1f1f1f;
            color: #ffffff;
        }

        .modal-header {
            border-bottom: none;
            justify-content: center;
        }

        .modal-header .modal-title {
            font-weight: bold;
            width: 100%;
            text-align: center;
        }

        .modal-footer {
            border-top: none;
        }

        /* Button styles */
        .modal-footer .btn {
            min-width: 120px;
            padding: 8px 20px;
            width: 120px;
            font-size: 1rem;
        }

        .btn-secondary {
            background-color: #333333;
            color: #ffffff;
            font-weight: bold;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .btn-secondary:hover {
            background-color: #444444;
            color: #ffffff;
        }

        .btn-secondary:focus {
            box-shadow: none !important;
        }

        /* Modal animations */
        .modal.fade .modal-dialog {
            transform: scale(0.7);
            opacity: 0;
            transition: all 0.3s ease-in-out;
        }

        .modal.show .modal-dialog {
            animation: modalPop 0.3s ease-out forwards;
        }

        @keyframes modalPop {
            0% {
                transform: scale(0.7);
                opacity: 0;
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>

    <!-- Update the availability success modal structure -->
    <div class="modal fade" id="availabilitySuccessModal" tabindex="-1" aria-labelledby="availabilitySuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="availabilitySuccessModalLabel">Success</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Barber availability has been updated successfully!</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Add these styles for the X button */
        .btn-close {
            color: #ffffff;
            filter: invert(1) grayscale(100%) brightness(200%);
            opacity: 1;
        }

        .btn-close:hover {
            opacity: 0.75;
        }

        /* Update modal header to accommodate X button */
        .modal-header {
            border-bottom: none;
            justify-content: center;
            position: relative;
            padding: 1rem;
        }

        .modal-header .btn-close {
            position: absolute;
            right: 1rem;
            padding: calc(1rem * .5);
            margin: calc(-.5 * 1rem) calc(-.5 * 1rem) calc(-.5 * 1rem) auto;
        }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle availability change
        document.querySelectorAll('.availability-select').forEach(select => {
            select.addEventListener('change', function(e) {
                const form = this.closest('form');
                const newValue = this.value;

                // Submit form via AJAX
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the data-original attribute
                        this.setAttribute('data-original', newValue);

                        // Show success modal
                        const successModal = new bootstrap.Modal(document.getElementById('availabilitySuccessModal'));
                        successModal.show();
                    } else {
                        alert('Error updating availability');
                        this.value = this.getAttribute('data-original');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while updating availability');
                    this.value = this.getAttribute('data-original');
                });
            });
        });
    });
    </script>
</body>
</html>

