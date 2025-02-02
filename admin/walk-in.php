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

// Fetch haircuts from the database
$haircutsQuery = "SELECT * FROM haircut_tbl";
$haircutsResult = $conn->query($haircutsQuery);

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
            transition: background-color 0.3s;
        }

        .time-slot-btn:hover {
            background-color: #FFD700;
        }

        .time-slot-btn.selected {
            background-color: #D3D3D3;
            color: black;
        }

        .time-slot-btn.booked {
            background-color: #D3D3D3;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .service-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .service-item:hover {
            background-color: #f8f9fa;
        }

        .service-item.selected {
            background-color: #FFDE59;
        }

        .service-name {
            font-weight: bold;
        }

        .service-price {
            color: #666;
            margin-top: 5px;
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

                        <!-- Add-On Dropdown -->
                        <div class="mb-3">
                            <label for="addon" class="form-label text-white">Add-on:</label>
                            <button type="button" class="form-control text-center" id="addon-button" data-bs-toggle="modal" data-bs-target="#addonsModal">
                                Choose Add-on
                            </button>
                            <input type="hidden" name="addon" id="addon" value="">
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

                        <!-- Haircut Dropdown -->
                        <div class="mb-3">
                            <label for="haircut" class="form-label text-white">Haircut:</label>
                            <button type="button" class="form-control text-center" id="haircut-button" data-bs-toggle="modal" data-bs-target="#haircutsModal">
                                Choose Haircut
                            </button>
                            <input type="hidden" name="haircut" id="haircut" value="">
                        </div>
                        <!-- Remarks Dropdown -->
                        <div class="mb-3">
                            <label for="remarks" class="form-label text-white">Remarks:</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="1" placeholder="ex. I prefer Jonathan as my Barber."></textarea>
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

    <!-- Haircuts Modal -->
    <div class="modal fade" id="haircutsModal" tabindex="-1" aria-labelledby="haircutsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="haircutsModalLabel">Choose Haircut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="services-list">
                        <?php 
                        $haircutsResult->data_seek(0);
                        while ($haircut = $haircutsResult->fetch_assoc()): 
                        ?>
                            <div class="service-item" 
                                 role="button"
                                 data-haircut-id="<?= $haircut['hcID'] ?>"
                                 onclick="selectHaircut(<?= $haircut['hcID'] ?>, '<?= addslashes($haircut['hcName']) ?>')">
                                <div class="service-name"><?= $haircut['hcName'] ?></div>
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
                        $timeSlots = [
                            '8:00 AM', '8:30 AM', '9:00 AM', '9:30 AM',
                            '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM',
                            '12:00 PM', '12:30 PM', '1:00 PM', '1:30 PM',
                            '2:00 PM', '2:30 PM', '3:00 PM', '3:30 PM',
                            '4:00 PM', '4:30 PM', '5:00 PM', '5:30 PM'
                        ];
                        foreach ($timeSlots as $time): 
                        ?>
                            <button class="time-slot-btn" 
                                    role="button"
                                    data-time="<?= $time ?>"
                                    onclick="selectTimeSlot('<?= $time ?>')">
                                <?= $time ?>
                            </button>
                        <?php endforeach; ?>
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

        function selectHaircut(haircutId, haircutName) {
            document.getElementById('haircut').value = haircutId;
            document.getElementById('haircut-button').textContent = haircutName;
            const modal = bootstrap.Modal.getInstance(document.getElementById('haircutsModal'));
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
