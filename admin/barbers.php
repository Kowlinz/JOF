<?php
session_start();
if (!isset($_SESSION["user"])) {
    header("Location: ../login-staff.php");
}

include 'db_connect.php';

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
    <link rel="icon" type="image/png" href="css/images/favicon-32x32.png">
    <link rel="stylesheet" href="css/calendar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <title>Barbers</title>
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
        /* Responsive table styles */
        @media screen and (max-width: 712px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }

            .card {
                margin: 0 -15px;
                border-radius: 0 !important;
            }

            .card-body {
                padding: 10px;
            }

            table {
                white-space: nowrap;
                font-size: 14px;
            }

            /* Adjust column widths for better mobile view */
            table th, table td {
                min-width: 100px;
                padding: 8px !important;
            }

            /* Specific column widths for barbers table */
            table th:first-child, 
            table td:first-child {
                min-width: 60px; /* No. column */
            }
            
            table th:nth-child(3), 
            table td:nth-child(3) {
                min-width: 180px; /* Email column */
            }
            
            table th:nth-child(4), 
            table td:nth-child(4) {
                min-width: 120px; /* Phone number column */
            }
        }
    </style>
</head>
<body>
    <?php include 'db_connect.php'; ?>

    <!-- Add the mobile toggle button -->
    <button class="mobile-toggle d-lg-none" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>

    <div class="body d-flex py-3 mt-5">
        <div class="container-xxl">
            <h1 class="dashboard mb-5 ms-5">Barbers</h1>
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
                                                echo "<form method='POST' action='update_availability.php'>";
                                                echo "<input type='hidden' name='barberID' value='" . $row['barberID'] . "'>";
                                                echo "<select name='availability' class='form-select' onchange='this.form.submit()'>";
                                                echo "<option value='Available'" . ($row['availability'] === 'Available' ? " selected" : "") . ">Available</option>";
                                                echo "<option value='Unavailable'" . ($row['availability'] === 'Unavailable' ? " selected" : "") . ">Unavailable</option>";
                                                echo "</select>";
                                                echo "</form>";
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
                <a href="barbers.php" class="list-group-item list-group-item-action py-2 ripple active">
                    <i class="fa-solid fa-scissors fa-fw me-3"></i><span>Barbers</span>
                </a>
                <a href="options.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-gear fa-fw me-3"></i><span>Options</span>
                </a>
                <a href="../logout-staff.php" class="list-group-item list-group-item-action py-2 ripple">
                    <i class="fa-solid fa-right-from-bracket fa-fw me-3"></i><span>Log Out</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Add necessary scripts -->
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
</body>
</html>

