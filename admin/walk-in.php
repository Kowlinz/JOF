<?php
// Start the session
session_start();

// Check if the user is logged in as an admin
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "admin") {
    header("Location: ../login.php"); // Redirect to login if not logged in or not an admin
    exit();
}

// Include the database connection file
include 'db_connect.php';

// Fetch services from the database
$servicesQuery = "SELECT * FROM service_tbl";
$servicesResult = $conn->query($servicesQuery);

// Fetch add-ons from the database
$addonsQuery = "SELECT * FROM addon_tbl";
$addonsResult = $conn->query($addonsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Booking</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style1.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Add these to your existing styles */
        .time-slots-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        padding: 15px;
    }

    .time-slot-btn {
        background-color: #FFDE59;
        border: none;
        padding: 10px;
        border-radius: 20px;
        color: black;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .time-slot-btn.selected {
        background-color: black;
        color: #FFDE59;
    }

    .time-slot-btn.booked {
        background-color: #D3D3D3;
        cursor: not-allowed;
        opacity: 0.7;
    }

    .time-slot-btn.booked:hover {
        background-color: #D3D3D3;
    }

    .available {
        background-color: #FFDE59; /* Green for available */
        color: black;
    }

    .unavailable {
        background-color: #d6d6d6; /* Light gray for unavailable */
        color: #555;
        cursor: not-allowed;
    }
    </style>
</head>

<body>
    <div class="container mt-5">
        <!-- Back Button -->
        <div class="d-flex justify-content-start mb-4">
            <a href="appointments.php" class="btn btn-warning text-dark fw-bold">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>

        <div class="container mt-5">
            <div class="text-center mb-4">
                <img src="css/images/jof_logo_yellow.png" alt="logo-1" width="90" height="120" class="mt-3">
            </div>
            <h2 class="text-center mb-5" style="color: #FFDF60;">Walk-In Customer</h2>

            <form name="form1" id="form1" action="submit_booking.php" method="POST" class="row g-3 mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="mb-3 required">
                            <span style="color: red;">* </span>
                            <label for="date" class="form-label text-white">Date:</label>
                            <input name="date" id="date" class="form-control text-center" placeholder="Choose Date">
                        </div>

                        <!-- Service Dropdown -->
                        <div class="mb-3" required>
                            <span style="color: red;">* </span>
                            <label for="service" class="form-label text-white">Service:</label>
                            <button type="button" class="form-control text-center" id="service-button" data-bs-toggle="modal" data-bs-target="#servicesModal">
                                Choose Service
                            </button>
                            <input type="hidden" name="service" id="service" value="">
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3 required">
                            <span style="color: red;">* </span>
                            <label for="time-slot" class="form-label text-white">Time Slot:</label>
                            <button type="button" class="form-control text-center" id="time-slot-button" data-bs-toggle="modal" data-bs-target="#timeSlotModal">
                                Choose Preferred Time
                            </button>
                            <input type="hidden" name="timeSlot" id="selectedTimeSlot">
                        </div>
                        <!-- Add-On Dropdown -->
                        <div class="mb-3">
                            <label for="addon" class="form-label text-white">Add-on:</label>
                            <button type="button" class="form-control text-center" id="addon-button" data-bs-toggle="modal" data-bs-target="#addonsModal">
                                Choose Add-on
                            </button>
                            <input type="hidden" name="addon" id="addon" value="">
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-warning text-dark fw-bold">Add Walk-in</button>
                </div>
            </form>
        </div>

        <!-- Error Modal -->
        <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="errorModalLabel">Error</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p id="errorMessage"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="window.history.back();">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Modal -->
    <div class="modal fade" id="servicesModal" tabindex="-1" aria-labelledby="servicesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="servicesModalLabel">Offered Services</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="services-list">
                        <?php 
                        // Reset the services result pointer
                        $servicesResult->data_seek(0);
                        while ($service = $servicesResult->fetch_assoc()): 
                        ?>
                            <div class="service-item" 
                                 role="button"
                                 data-service-id="<?= $service['serviceID'] ?>"
                                 onclick="selectService(<?= $service['serviceID'] ?>, '<?= addslashes($service['serviceName']) ?>', <?= $service['servicePrice'] ?>)">
                                <div class="service-name"><?= $service['serviceName'] ?></div>
                                <div class="service-price"><?= number_format($service['servicePrice'], 0) ?> PHP</div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add-ons Modal -->
    <div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addonsModalLabel">Choose Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="services-list">
                        <div class="service-item" 
                             role="button"
                             data-addon-id=""
                             onclick="selectAddon('', 'No Add-on', 0)">
                            <div class="service-name">No Add-on</div>
                            <div class="service-price">0 PHP</div>
                        </div>
                        <?php 
                        $addonsResult->data_seek(0);
                        while ($addon = $addonsResult->fetch_assoc()): 
                        ?>
                            <div class="service-item" 
                                 role="button"
                                 data-addon-id="<?= $addon['addonID'] ?>"
                                 onclick="selectAddon(<?= $addon['addonID'] ?>, '<?= addslashes($addon['addonName']) ?>', <?= $addon['addonPrice'] ?>)">
                                <div class="service-name"><?= $addon['addonName'] ?></div>
                                <div class="service-price"><?= number_format($addon['addonPrice'], 0) ?> PHP</div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Slots Modal -->
    <div class="modal fade" id="timeSlotModal" tabindex="-1" aria-labelledby="timeSlotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeSlotModalLabel">Choose Time Slot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="time-slots-grid">
                    <?php 
                    // Database connection
                    $conn = new mysqli("localhost", "root", "", "jof_db");

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Define time slots
                    $timeSlots = [
                        '10:00 AM', '10:40 AM', '11:20 AM', '12:00 PM',
                        '12:40 PM', '1:20 PM', '2:00 PM', '2:40 PM',
                        '3:20 PM', '4:00 PM', '4:40 PM', '5:20 PM',
                        '6:00 PM', '6:40 PM', '7:20 PM', '8:00 PM',
                    ];

                    // Get the count of available barbers
                    $barbersQuery = "SELECT COUNT(*) AS total_barbers FROM barbers_tbl WHERE availability = 'available'";
                    $barbersResult = $conn->query($barbersQuery);
                    $barbersRow = $barbersResult->fetch_assoc();
                    $totalBarbers = $barbersRow['total_barbers'];

                    // Get the current time
                    date_default_timezone_set('Asia/Manila'); // Set to your timezone
                    $currentTime = date("H:i");

                    foreach ($timeSlots as $time): 
                        // Convert the slot time to 24-hour format
                        $slotTime = date("H:i", strtotime($time));

                        // Count how many barbers are already booked at this time
                        $appointmentQuery = "SELECT COUNT(*) AS booked FROM appointment_tbl WHERE timeSlot = '$time'";
                        $appointmentResult = $conn->query($appointmentQuery);
                        $appointmentRow = $appointmentResult->fetch_assoc();
                        $bookedBarbers = $appointmentRow['booked'];

                        // Calculate remaining slots for this time
                        $remainingSlots = $totalBarbers - $bookedBarbers;
                        $isAvailable = ($remainingSlots > 0) && ($slotTime > $currentTime); // Ensure time slot is in the future
                    ?>
                        <button class="time-slot-btn <?= $isAvailable ? 'available' : 'unavailable' ?>"
                                role="button"
                                data-time="<?= $time ?>"
                                <?= $isAvailable ? "onclick=\"selectTimeSlot('$time')\"" : "disabled"; ?>>
                            <?= $time ?>
                        </button>
                    <?php endforeach; ?>

                    <?php $conn->close(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/booking.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize flatpickr for the date input
            flatpickr("#date", {
                dateFormat: "Y-m-d",
                minDate: "today",
                onChange: function(selectedDates, dateStr) {
                    // When date is selected, update time slots
                    updateTimeSlots();
                }
            });

            // Form submission handling
            document.getElementById('form1').addEventListener('submit', function(event) {
                const date = document.getElementById('date').value;
                const timeSlot = document.getElementById('selectedTimeSlot').value;
                const service = document.getElementById('service').value;

                // Validate required fields
                if (!date || !timeSlot || !service) {
                    // Show error modal instead of alert
                    const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                    errorModal.show();
                    event.preventDefault(); // Prevent form submission
                }
            });

            function updateTimeSlots() {
                const timeSlotBtns = document.querySelectorAll('.time-slot');
                const currentDate = new Date();
                const selectedDate = new Date(document.getElementById('date').value);
                const isToday = selectedDate.toDateString() === currentDate.toDateString();

                timeSlotBtns.forEach(btn => {
                    const time = btn.textContent.trim();
                    const [hours, minutes] = time.split(' ');
                    const [hour, minute] = hours.split(':');
                    const timeSlotDate = new Date(selectedDate);
                    timeSlotDate.setHours(hour === '12' ? 12 : (parseInt(hour) + (minutes.endsWith('PM') ? 12 : 0))); // Adjust for AM/PM
                    timeSlotDate.setMinutes(minutes.endsWith('PM') ? parseInt(minute) : parseInt(minute)); // Set minutes

                    // Compare the time slot with the current time
                    if (isToday && timeSlotDate <= currentDate) {
                        btn.classList.add('booked');
                        btn.style.pointerEvents = 'none'; // Disable click
                        btn.style.opacity = '0.5'; // Make it look disabled
                    } else {
                        btn.classList.remove('booked');
                        btn.style.pointerEvents = 'auto'; // Enable click
                        btn.style.opacity = '1'; // Reset opacity
                    }
                });
            }
        });

        // Check for error message in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error')) {
            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
            document.getElementById('errorMessage').innerText = decodeURIComponent(urlParams.get('error'));
            errorModal.show();
        }

        function selectService(serviceId, serviceName, servicePrice) {
            document.getElementById('service').value = serviceId;
            document.getElementById('service-button').textContent = `${serviceName} - ${servicePrice} PHP`;
            const modal = bootstrap.Modal.getInstance(document.getElementById('servicesModal'));
            modal.hide();
        }

        function selectAddon(addonId, addonName, addonPrice) {
            document.getElementById('addon').value = addonId;
            document.getElementById('addon-button').textContent = addonId ? `${addonName} - ${addonPrice} PHP` : 'No Add-on';
            const modal = bootstrap.Modal.getInstance(document.getElementById('addonsModal'));
            modal.hide();
        }

        function selectTimeSlot(time) {
            const btn = document.querySelector(`.time-slot-btn[data-time="${time}"]`);
            if (btn && !btn.classList.contains('booked')) {
                document.getElementById('selectedTimeSlot').value = time;
                document.getElementById('time-slot-button').textContent = time;
                
                // Remove selected class from all buttons
                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                    btn.classList.remove('selected');
                });
                
                // Add selected class to clicked button
                btn.classList.add('selected');
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('timeSlotModal'));
                if (modal) {
                    modal.hide();
                }
            }
        }
    </script>
</body>
</html>
