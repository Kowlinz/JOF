<?php
session_start();

// Check if the user is logged in as a customer
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "customer") {
    header("Location: ../login.php"); // Redirect to login if not logged in or not a customer
    exit();
}

// Get the logged-in customer's ID from the session
$customerID = $_SESSION["customerID"];

// Database connection
include 'db_connect.php';

// Create a function to get booked time slots for a specific date
function getBookedTimeSlots($conn, $date) {
    $sql = "SELECT timeSlot FROM appointment_tbl WHERE date = ? AND status != 'Cancelled'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookedSlots = array();
    while($row = $result->fetch_assoc()) {
        $bookedSlots[] = $row['timeSlot'];
    }
    
    return $bookedSlots;
}

// Fetch customer's firstName if not in session
if (!isset($_SESSION['firstName'])) {
    $sql = "SELECT firstName FROM customer_tbl WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['firstName'] = $row['firstName'];
    }
    $stmt->close();
}

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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="../css/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
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
    <div class="header">
        <nav class="navbar navbar-expand-lg py-4">
            <div class="container ps-5">
                <a class="navbar-brand ms-0" href="../index.php">
                    <img src="../css/images/jof_logo_black.png" alt="logo" width="45" height="45" class="desktop-logo">
                    <img src="../css/images/jof_logo_yellow.png" alt="logo" width="45" height="45" class="mobile-logo">
                </a>

                <button class="menu-btn d-lg-none" type="button" id="menuBtn">
                    <i class='bx bx-menu'></i>
                </button>

                <div class="menu-dropdown" id="menuDropdown">
                    <div class="menu-header">
                        <button class="menu-close" id="menuClose">&times;</button>
                    </div>
                    <div class="menu-links">
                        <a href="../index.php" class="menu-link">HOME</a>
                        <a href="../haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
                        <a href="appointment.php" class="menu-link">MY APPOINTMENT</a>
                        <a href="../logout.php" class="menu-link">LOGOUT</a>
                    </div>
                </div>

                <!-- Rest of the navbar content -->
                <div class="navbar-nav mx-auto ps-5">
                    <a class="nav-link mx-4 nav-text fs-5" href="../index.php">Home</a>
                    <a class="nav-link mx-4 nav-text fs-5" href="../haircuts.php">HAIRCUTS & SERVICES</a>
                    <a class="nav-link mx-4 nav-text fs-5" href="appointment.php">My Appointment</a>
                </div>
                <div class="navbar-nav pe-5 me-4">
                    <button class="btn btn-dark me-2 px-4" 
                        onclick="document.location='booking.php'" 
                        type="button" 
                        style="background-color: #000000; color: #FFDE59; border-radius: 12px;">Book Now</button>
                        
                    <div class="dropdown">
                        <div class="user-header d-flex align-items-center" id="userDropdown">
                            <div class="user-icon">
                                <i class='bx bxs-user'></i>
                            </div>
                            <div class="user-greeting">
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION['firstName'] ?? ''); ?></span>
                            </div>
                        </div>

                        <div class="dropdown-menu" id="dropdownMenu">
                            <a href="../logout.php" class="dropdown-item">Logout</a>
                        </div>
                    </div>
                    
                    <script>
                        // JavaScript to toggle dropdown visibility
                        const dropdownToggle = document.getElementById('userDropdown');
                        const dropdownMenu = document.getElementById('dropdownMenu');

                        dropdownToggle.addEventListener('click', function () {
                            dropdownMenu.classList.toggle('show');
                        });

                        // Close dropdown when clicking outside
                        document.addEventListener('click', function (event) {
                            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                                dropdownMenu.classList.remove('show');
                            }
                        });
                    </script>
                </div>
            </div>
        </nav>
    </div>

    <div class="container mt-5">
        <div class="text-center mb-4 d-none d-lg-block">
            <img src="css/images/JOF-Logo.png" alt="logo-1" width="90" height="120" class="mt-3">
        </div>
        <h2 class="text-center mb-5" style="color: #FFDF60;">Make an Appointment</h2>

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
                        <label for="time-slot" class="form-label text-white">Preferred Time Slot:</label>
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
            <div class="col-12 text-center mt-4 mb-5">
                <button type="submit" class="btn text-dark fw-bold btn-book-appointment" style="background-color: #F3CD32;">Book Appointment</button>
            </div>
        </form>
    </div>

                <!-- Confirmation Modal -->
                <div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="confirmationModalLabel">Appointment Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p><strong>Date:</strong> <span id="confirmDate"></span></p>
                                <p><strong>Time:</strong> <span id="confirmTimeSlot"></span></p>
                                <p><strong>Service:</strong> <span id="confirmService"></span></p>
                                <p><strong>Add-on:</strong> <span id="confirmAddon"></span></p>
                                <p><strong>Haircut:</strong> <span id="confirmHaircut"></span></p>
                                <p><strong>Remarks:</strong> <span id="confirmRemarks"></span></p>
                                <p><strong>Total Price:</strong> <span id="confirmTotalPrice">319 PHP</span></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="confirmBooking" class="btn btn-confirm">Confirm</button>
                            </div>
                        </div>
                    </div>
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
                                <p>Please fill in all required fields (Date, Time Slot, and Service).</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Update the form submission handling
                    document.getElementById('form1').addEventListener('submit', function(event) {
                        event.preventDefault();

                        // Get form values
                        const date = document.getElementById('date').value;
                        const timeSlot = document.getElementById('selectedTimeSlot').value;
                        const service = document.getElementById('service-button').textContent;
                        const addon = document.getElementById('addon-button').textContent;
                        const haircut = document.getElementById('haircut-button').textContent;
                        const remarks = document.getElementById('remarks').value;

                        // Calculate total price
                        let totalPrice = 0;

                        // Extract service price
                        const serviceMatch = service.match(/(\d+) PHP/);
                        if (serviceMatch) {
                            totalPrice += parseInt(serviceMatch[1]);
                        }

                        // Extract addon price
                        const addonMatch = addon.match(/(\d+) PHP/);
                        if (addonMatch) {
                            totalPrice += parseInt(addonMatch[1]);
                        }

                        // Validate required fields
                        if (!date || !timeSlot || !document.getElementById('service').value) {
                            // Show error modal instead of alert
                            const errorModal = new bootstrap.Modal(document.getElementById('errorModal'));
                            errorModal.show();
                            return;
                        }

                        // Format the date nicely
                        const formattedDate = new Date(date).toLocaleDateString('en-US', {
                            month: 'long',
                            day: 'numeric',
                            year: 'numeric'
                        });

                        // Populate modal with values
                        document.getElementById('confirmDate').innerText = formattedDate;
                        document.getElementById('confirmTimeSlot').innerText = timeSlot;
                        document.getElementById('confirmService').innerText = service;
                        document.getElementById('confirmAddon').innerText = addon === 'Choose Add-on' ? 'None' : addon;
                        document.getElementById('confirmHaircut').innerText = haircut === 'Choose Haircut' ? 'None' : haircut;
                        document.getElementById('confirmRemarks').innerText = remarks || 'None';
                        document.getElementById('confirmTotalPrice').innerText = `${totalPrice} PHP`;

                        // Show the modal
                        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                        confirmationModal.show();
                    });

                    // Update the confirm booking button handler
                    document.getElementById('confirmBooking').addEventListener('click', function() {
                        // Submit the form
                        document.getElementById('form1').submit();
                    });

                    // Add this to initialize the confirmation modal
                    document.addEventListener('DOMContentLoaded', function() {
                        const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
                    });

                    document.addEventListener('DOMContentLoaded', function() {
                        // Initialize flatpickr
                        const dateInput = flatpickr("#date", {
                            dateFormat: "Y-m-d",
                            minDate: "today",
                            onChange: function(selectedDates, dateStr) {
                                // When date is selected, fetch booked slots
                                fetchBookedSlots(dateStr);
                            }
                        });

                        function fetchBookedSlots(date) {
                            fetch(`get_booked_slots.php?date=${date}`)
                                .then(response => response.json())
                                .then(bookedSlots => {
                                    updateTimeSlots(bookedSlots);
                                })
                                .catch(error => console.error('Error:', error));
                        }

                        function updateTimeSlots(bookedSlots) {
                            const timeSlotBtns = document.querySelectorAll('.time-slot-btn');
                            const currentDate = new Date();
                            const selectedDate = new Date(document.getElementById('date').value);
                            const isToday = selectedDate.toDateString() === currentDate.toDateString();

                            timeSlotBtns.forEach(btn => {
                                const time = btn.getAttribute('data-time');
                                const [hours, minutes] = time.split(':');
                                const timeSlotDate = new Date(selectedDate);
                                timeSlotDate.setHours(hours === '12' ? 12 : (parseInt(hours) + (minutes.endsWith('PM') ? 12 : 0))); // Adjust for AM/PM
                                timeSlotDate.setMinutes(minutes.endsWith('PM') ? parseInt(minutes) : parseInt(minutes)); // Set minutes

                                // Compare the time slot with the current time
                                if (bookedSlots.includes(time) || (isToday && timeSlotDate <= currentDate)) {
                                    btn.classList.add('booked');
                                    btn.disabled = true;
                                } else {
                                    btn.classList.remove('booked');
                                    btn.disabled = false;
                                }
                            });
                        }
                    });

                    function selectTimeSlot(time) {
                        const btn = document.querySelector(`.time-slot-btn[data-time="${time}"]`);
                        if (btn && btn.classList.contains('booked')) {
                            return; // Don't allow selection of booked slots
                        }
                        
                        // Rest of your existing selectTimeSlot function...
                    }
                </script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/booking.js"></script>

    <!-- Update the modal structure -->
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
                        // Reset the haircuts result pointer
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

    <div class="modal fade" id="addonsModal" tabindex="-1" aria-labelledby="addonsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addonsModalLabel">Choose Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="services-list">
                        <!-- Add "No Add-on" option -->
                        <div class="service-item" 
                             role="button"
                             data-addon-id=""
                             onclick="selectAddon('', 'No Add-on', 0)">
                            <div class="service-name">No Add-on</div>
                            <div class="service-price">0 PHP</div>
                        </div>
                        <?php 
                        // Reset the addons result pointer
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



    <script>
    function selectService(serviceId, serviceName, servicePrice) {
        // Update the hidden input
        document.getElementById('service').value = serviceId;
        
        // Update the button text
        document.getElementById('service-button').textContent = `${serviceName} - ${servicePrice} PHP`;
        
        // Remove selected class from all items
        document.querySelectorAll('.service-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selected class to clicked item using data attribute
        const selectedItem = document.querySelector(`.service-item[data-service-id="${serviceId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
        }
        
        // Close the modal
        const servicesModal = bootstrap.Modal.getInstance(document.getElementById('servicesModal'));
        servicesModal.hide();
    }

    // Initialize the modal when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the modal
        const servicesModal = new bootstrap.Modal(document.getElementById('servicesModal'));
        
        // Add click handler for close button
        const closeButton = document.querySelector('#servicesModal .btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                servicesModal.hide();
            });
        }
    });

    // Clear selection when modal is closed
    document.getElementById('servicesModal').addEventListener('hidden.bs.modal', function () {
        if (!document.getElementById('service').value) {
            document.getElementById('service-button').textContent = 'Choose Service';
        }
    });

    function selectHaircut(haircutId, haircutName) {
        // Update the hidden input
        document.getElementById('haircut').value = haircutId;
        
        // Update the button text
        document.getElementById('haircut-button').textContent = haircutName;
        
        // Remove selected class from all items
        document.querySelectorAll('#haircutsModal .service-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selected class to clicked item
        const selectedItem = document.querySelector(`#haircutsModal .service-item[data-haircut-id="${haircutId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
        }
        
        // Close the modal
        const haircutsModal = bootstrap.Modal.getInstance(document.getElementById('haircutsModal'));
        haircutsModal.hide();
    }

    // Initialize the haircuts modal when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the modal
        const haircutsModal = new bootstrap.Modal(document.getElementById('haircutsModal'));
        
        // Add click handler for close button
        const closeButton = document.querySelector('#haircutsModal .btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                haircutsModal.hide();
            });
        }
    });

    // Clear selection when modal is closed
    document.getElementById('haircutsModal').addEventListener('hidden.bs.modal', function () {
        if (!document.getElementById('haircut').value) {
            document.getElementById('haircut-button').textContent = 'Choose Haircut';
        }
    });

    function selectAddon(addonId, addonName, addonPrice) {
        // Update the hidden input
        document.getElementById('addon').value = addonId;
        
        // Update the button text
        if (addonId === '') {
            document.getElementById('addon-button').textContent = 'No Add-on';
        } else {
            document.getElementById('addon-button').textContent = `${addonName} - ${addonPrice} PHP`;
        }
        
        // Remove selected class from all items
        document.querySelectorAll('#addonsModal .service-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selected class to clicked item
        const selectedItem = document.querySelector(`#addonsModal .service-item[data-addon-id="${addonId}"]`);
        if (selectedItem) {
            selectedItem.classList.add('selected');
        }
        
        // Close the modal
        const addonsModal = bootstrap.Modal.getInstance(document.getElementById('addonsModal'));
        addonsModal.hide();
    }

    // Initialize the addons modal when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the modal
        const addonsModal = new bootstrap.Modal(document.getElementById('addonsModal'));
        
        // Add click handler for close button
        const closeButton = document.querySelector('#addonsModal .btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                addonsModal.hide();
            });
        }
    });

    // Clear selection when modal is closed
    document.getElementById('addonsModal').addEventListener('hidden.bs.modal', function () {
        if (!document.getElementById('addon').value) {
            document.getElementById('addon-button').textContent = 'Choose Add-on';
        }
    });

    function selectTimeSlot(time) {
        const btn = document.querySelector(`.time-slot-btn[data-time="${time}"]`);
        if (btn && btn.classList.contains('booked')) {
            return; // Don't allow selection of booked slots
        }
        
        // Update the hidden input
        document.getElementById('selectedTimeSlot').value = time;
        
        // Update the button text
        document.getElementById('time-slot-button').textContent = time;
        
        // Remove selected class from all buttons
        document.querySelectorAll('.time-slot-btn').forEach(btn => {
            btn.classList.remove('selected');
        });
        
        // Add selected class to clicked button
        const selectedBtn = document.querySelector(`.time-slot-btn[data-time="${time}"]`);
        if (selectedBtn) {
            selectedBtn.classList.add('selected');
        }
        
        // Close the modal
        const timeSlotModal = bootstrap.Modal.getInstance(document.getElementById('timeSlotModal'));
        timeSlotModal.hide();
    }

    // Initialize the time slot modal when the document is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize the modal
        const timeSlotModal = new bootstrap.Modal(document.getElementById('timeSlotModal'));
        
        // Add click handler for close button
        const closeButton = document.querySelector('#timeSlotModal .btn-close');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                timeSlotModal.hide();
            });
        }
    });

    // Clear selection when modal is closed
    document.getElementById('timeSlotModal').addEventListener('hidden.bs.modal', function () {
        if (!document.getElementById('selectedTimeSlot').value) {
            document.getElementById('time-slot-button').textContent = 'Choose Preferred Time';
        }
    });

    // JavaScript to toggle mobile menu
    const menuBtn = document.getElementById('menuBtn');
    const menuDropdown = document.getElementById('menuDropdown');
    const menuClose = document.getElementById('menuClose');

    menuBtn.addEventListener('click', function () {
        menuDropdown.classList.toggle('show');
    });

    menuClose.addEventListener('click', function () {
        menuDropdown.classList.remove('show');
    });

    // Close menu when clicking outside
    document.addEventListener('click', function (event) {
        if (!menuBtn.contains(event.target) && !menuDropdown.contains(event.target)) {
            menuDropdown.classList.remove('show');
        }
    });
    </script>
</body>
</html>
