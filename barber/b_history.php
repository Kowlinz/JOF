<?php
session_start();

// Check if the user is logged in as a barber
if (!isset($_SESSION["user"]) || $_SESSION["user"] !== "barber") {
    header("Location: ../login-staff.php"); // Redirect to login if not logged in or not a barber
    exit();
}

// Get the logged-in barber's ID from the session
$barberID = $_SESSION["barberID"];

include 'db_connect.php';

    // Query to get the barber's full name
    $barberQuery = "SELECT firstName, lastName FROM barbers_tbl WHERE barberID = '$barberID'";
    $barberResult = mysqli_query($conn, $barberQuery);
    if (!$barberResult) {
        echo "Error fetching barber's name: " . mysqli_error($conn);
    }
    $barber = mysqli_fetch_assoc($barberResult);
    $barberFullName = $barber ? $barber['firstName'] . ' ' . $barber['lastName'] : 'Unknown Barber'; // Default if no name found

    // Fetch the number of pending appointments and their status
    $notificationQuery = "
    SELECT COUNT(*) AS pending_count 
    FROM barb_apps_tbl AS b 
    JOIN appointment_tbl AS a 
    ON b.appointmentID = a.appointmentID 
    WHERE b.barberID = '$barberID' 
    AND a.status = 'Pending'
    ";
    
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
    <link rel="stylesheet" type="text/css" href="css/table.css">
    <link rel="stylesheet" href="css/sidebar.css">
    <link rel="stylesheet" href="css/barber.css">
    <link rel="icon" href="../css/images/favicon.ico">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Customer History</title>
</head>
<body>
    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <div class="position-relative">
                <h1 class="dashboard mb-5 ms-5">Customer History</h1>
                <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>

            <div class="col-md-12 ms-5">
                <div class="card border-0 rounded-4"> 
                    <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                        <h2 class="fw-bold">Previous Customers</h2>
                        <!-- Date Picker for Filtering -->
                        <div class="mb-0">
                            <input type="date" id="appointmentDate" class="form-control" 
                            value="<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>" 
                            oninput="filterAppointments()">
                        </div>
                    </div>
                    <div class="card-body"> 
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <td>No.</td>
                                        <td>Name</td>
                                        <td>Date</td>
                                        <td>Time</td>
                                        <td>Service</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $selectedDate = isset($_GET['date']) ? $_GET['date'] : null;
                                    // Query to display previous customers for the logged-in barber
                                    $previousQuery = "SELECT 
                                        a.appointmentID,
                                        c.customerID,
                                        CONCAT(c.firstName, ' ', c.lastName) AS fullName,
                                        a.date,
                                        a.timeSlot,
                                        s.serviceName
                                    FROM 
                                        appointment_tbl a
                                    LEFT JOIN 
                                        customer_tbl c ON a.customerID = c.customerID
                                    LEFT JOIN 
                                        barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
                                    LEFT JOIN 
                                        service_tbl s ON a.serviceID = s.serviceID
                                    WHERE 
                                        a.status = 'Completed'
                                        AND ba.barberID = '$barberID'";

                                    // Add date filtering if a date is selected
                                    if (!empty($selectedDate)) {
                                        $previousQuery .= " AND a.date = '" . mysqli_real_escape_string($conn, $selectedDate) . "'";
                                    }

                                    $previousQuery .= " ORDER BY a.timeSlot ASC";                                

                                    $previousResult = mysqli_query($conn, $previousQuery);

                                    // Check if there are any previous results
                                    if (mysqli_num_rows($previousResult) > 0) {
                                        $counter = 1;
                                        while ($row = mysqli_fetch_assoc($previousResult)) {
                                            $fullName = !empty($row['fullName']) ? $row['fullName'] : 'Walk In';
                                            $timeSlot = $row['timeSlot'];
                                            $date = $row['date'];  
                                            $serviceName = !empty($row['serviceName']) ? $row['serviceName'] : 'No Service';
                                            $formattedDate = date("F d, Y", strtotime($row['date']));
                                            $isWalkIn = empty($row['customerID']) ? 'true' : 'false';
                                            echo "<tr>";
                                            echo "<td>{$counter}</td>";
                                            echo "<td><a href='#' onclick='showAppointmentDetails({$row['appointmentID']}, {$isWalkIn})' 
                                                    data-bs-toggle='modal' data-bs-target='#appointmentModal' 
                                                    style='text-decoration: none; color: inherit;'>{$fullName}</a></td>";
                                            echo "<td>{$formattedDate}</td>";
                                            echo "<td>{$timeSlot}</td>";
                                            echo "<td>{$serviceName}</td>";
                                            echo "</tr>";
                                            $counter++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No Previous Customers</td></tr>";
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

    <nav id="sidebarMenu" class="collapse d-lg-block sidebar collapse">
        <div class="position-sticky">
            <div class="list-group list-group-flush mx-3 mt-5">
                <div class="avatar-container text-center">
                    <img src="css/images/jof_logo_black.png" alt="logo" width="55" height="55" class="logo mb-4">
                    <h5 class="mt-3" style="font-weight: bold; font-size: 20px;"><?php echo $barberFullName; ?></h5> <!-- Display Barber's Name -->
                </div>
                <a href="b_dashboard.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-border-all fa-fw me-3"></i><span>Dashboard</span>
                </a>
                <a href="schedule.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-users fa-fw me-3"></i>
                    <span>Schedule</span>
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-danger ms-2"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="b_history.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-clock-rotate-left fa-fw me-3"></i><span>History</span>
                </a>
                <a href="income.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-money-bill-trend-up fa-fw me-3"></i><span>Income</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('show');
        }

        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebarMenu');
            const toggle = document.querySelector('.mobile-toggle');
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
    <script src="js/calendar.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let appointmentDates = <?php echo json_encode($appointmentDates); ?>;
        
        document.querySelectorAll('.calendar-day').forEach(day => {
            let date = day.getAttribute('data-date'); // Assuming your calendar days have `data-date`
            if (appointmentDates.includes(date)) {
                day.innerHTML += `<span class="badge bg-danger ms-1">!</span>`;
            }
        });
    });
    </script>

    <script>
    let timeout = null;

    function filterAppointments() {
        clearTimeout(timeout); // Clear previous timeout to avoid multiple reloads
        timeout = setTimeout(() => {
            let selectedDate = document.getElementById("appointmentDate").value;
            if (selectedDate.length === 10) { // Ensure full date is entered
                window.location.href = "b_history.php?date=" + selectedDate;
            }
        }, 800); // Delay execution to allow typing
    }
    </script>

    <!-- Modal for additional details-->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel">Other Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Add-on Name:</strong> <span id="addonName"></span></p>
                    <p><strong>Haircut Name:</strong> <span id="hcName"></span></p>
                    <p><strong>Remarks:</strong> <span id="remarks"></span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function showAppointmentDetails(appointmentID, isWalkIn) {
        fetch('../admin/fetch_appointment_details.php?appointmentID=' + appointmentID)
            .then(response => response.json())
            .then(data => {
                document.getElementById('addonName').innerText = data.addonName || 'N/A';
                if (!isWalkIn) {
                    document.getElementById('hcName').innerText = data.hcName || 'N/A';
                    document.getElementById('remarks').innerText = data.remarks || 'N/A';
                    document.getElementById('hcName').parentElement.style.display = 'block';
                    document.getElementById('remarks').parentElement.style.display = 'block';
                } else {
                    document.getElementById('addonName').innerText = data.addonName || 'N/A';
                    document.getElementById('hcName').parentElement.style.display = 'none';
                    document.getElementById('remarks').parentElement.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching details:', error));
    }
    </script>

    <script>
        // Initialize Bootstrap dropdowns
        document.addEventListener('DOMContentLoaded', function() {
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Prevent dropdown from closing when clicking inside calendar
            document.querySelector('.calendar-container').addEventListener('click', function(e) {
                e.stopPropagation();
            });
        });
    </script>
</body>
</html>
