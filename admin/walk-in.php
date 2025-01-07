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
                        <div class="mb-3">
                            <span style="color: red;">* </span>
                            <label for="service" class="form-label text-white">Service:</label>
                            <select name="service" id="service" class="form-select text-center">
                                <option value="" selected="selected">Choose Service</option>
                                <?php while ($service = $servicesResult->fetch_assoc()): ?>
                                    <option value="<?= $service['serviceID'] ?>"><?= $service['serviceName'] ?> - <?= $service['servicePrice'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Add-On Dropdown -->
                        <div class="mb-3">
                            <label for="addon" class="form-label text-white">Add-on:</label>
                            <select class="form-select text-center" name="addon" id="addon">
                                <option value="">Select Add-on</option>
                                <?php while ($addon = $addonsResult->fetch_assoc()): ?>
                                    <option value="<?= $addon['addonID'] ?>"><?= $addon['addonName'] ?> - <?= $addon['addonPrice'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="mb-3 required">
                            <span style="color: red;">* </span>
                            <label for="time-slot" class="form-label text-white">Time Slot:</label>
                            <div class="time-slot-dropdown">
                                <!-- Hidden input to store the selected time slot -->
                                <input type="hidden" name="timeSlot" id="selectedTimeSlot">

                                <!-- Dropdown button to display time slot options -->
                                <button type="button" class="form-control text-center" id="time-slot-button">
                                    Choose Preferred Time
                                </button>

                                <!-- Time slot options -->
                                <div class="time-slot-options" style="display: none;">
                                    <div class="time-slot" onclick="selectTimeSlot('8:00 AM')">8:00 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('8:30 AM')">8:30 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('9:00 AM')">9:00 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('9:30 AM')">9:30 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('10:00 AM')">10:00 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('10:30 AM')">10:30 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('11:00 AM')">11:00 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('11:30 AM')">11:30 AM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('12:00 PM')">12:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('12:30 PM')">12:30 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('1:00 PM')">1:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('1:30 PM')">1:30 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('2:00 PM')">2:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('2:30 PM')">2:30 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('3:00 PM')">3:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('3:30 PM')">3:30 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('4:00 PM')">4:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('4:30 PM')">4:30 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('5:00 PM')">5:00 PM</div>
                                    <div class="time-slot" onclick="selectTimeSlot('5:30 PM')">5:30 PM</div>
                                </div>
                            </div>
                        </div>

                        <!-- Haircut Dropdown -->
                        <div class="mb-3">
                            <label for="haircut" class="form-label text-white">Haircut:</label>
                            <select class="form-select text-center" name="haircut" id="haircut">
                                <option value="" selected="selected">Choose Haircut</option>
                                <?php while ($haircut = $haircutsResult->fetch_assoc()): ?>
                                    <option value="<?= $haircut['hcID'] ?>"><?= $haircut['hcName'] ?></option>
                                <?php endwhile; ?>
                            </select>
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

        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="js/booking.js"></script>
</body>
</html>
