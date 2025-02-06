<?php
session_start();

// Assume `customerID` is stored in the session after login
$customerID = $_SESSION['customerID'] ?? null;

// Fetch user's firstName if logged in
$firstName = null;
if ($customerID) {
    include 'customer/db_connect.php';

    $sql = "SELECT firstName FROM customer_tbl WHERE customerID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customerID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $firstName = $row['firstName'];
    }
    $stmt->close();
    $conn->close();
}

// Fetch haircuts from the database
include 'customer/db_connect.php'; // Reconnect to the database
$sql = "SELECT hcID, hcName, hcImage, hcCategory FROM haircut_tbl";
$result = $conn->query($sql);
$haircuts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $imagePath = base64_encode($row['hcImage']);
        $haircuts[] = [
            'hcID' => $row['hcID'],
            'hcName' => $row['hcName'],
            'hcImage' => $imagePath,
            'hcCategory' => $row['hcCategory'],
        ];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Haircuts</title>
    <link rel="icon" href="css/images/favicon.ico">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Lexend', sans-serif;
        }

        /* Navbar animation */
        .header {
            opacity: 0;
            transform: translateY(-20px);
            animation: navSlideDown 0.8s ease forwards;
        }

        @keyframes navSlideDown {
            0% {
                opacity: 0;
                transform: translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Adjust other animations to start after navbar */
        .fade-in {
            animation-delay: 0.3s; /* Start after navbar animation */
        }

        .container.fade-in:nth-child(3) {
            animation-delay: 0.5s; /* Further delay for container */
        }

        /* Gallery item styles */
        .gallery-item {
            opacity: 0; /* Start hidden */
            transform: translateY(20px); /* Start slightly below */
            animation: fadeInItem 0.5s ease forwards; /* Fade in animation */
        }

        @keyframes fadeInItem {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Ensure items are fully visible when displayed */
        .gallery-item.show {
            opacity: 1; /* Fully visible */
            transform: translateY(0); /* Reset translation */
        }

        /* Services section styling */
        .services-section {
            padding-top: 0;
        }

        .services-container {
            margin-bottom: 30px;
        }

        .haircuts-header {
            margin-bottom: 15px;
        }

        /* Container adjustments */
        .container.fade-in {
            margin-bottom: 0;
        }

        /* Table container adjustments */
        .card-body {
            padding: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 991px) {
            .services-section {
                padding-top: 15px;
            }

            .haircuts-header {
                margin-bottom: 15px;
                text-align: center;
            }

            .services-container {
                margin-top: 0;
                padding: 0 15px;
            }

            #gallery-container {
                margin-bottom: 0;
            }
        }

        @media (max-width: 768px) {
            .services-section {
                padding-top: 10px;
            }

            .haircuts-header {
                font-size: 24px;
            }

            .services-container {
                padding: 0 10px;
            }
        }

        /* Gallery layout for tablets */
        @media (min-width: 576px) and (max-width: 920px) {
            #gallery-container {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
            }
            #gallery-container > div {
                width: 80%;
                max-width: 500px;
                flex: 0 0 auto;
            }
            .gallery-item {
                width: 100%;
                margin: 0 auto;
            }
            .gallery-item img {
                width: 100%;
                height: auto;
                object-fit: cover;
            }
        }
    </style>
</head>

<body>
    <div class="main-page">
        <!-- Header -->
        <div class="header">
            <nav class="navbar navbar-expand-lg py-4">
                <div class="container ps-5">
                    <a class="navbar-brand ms-0" href="index.php">
                        <img src="css/images/jof_logo_black.png" alt="logo" width="45" height="45" class="desktop-logo">
                        <img src="css/images/jof_logo_yellow.png" alt="logo" width="45" height="45" class="mobile-logo">
                    </a>
                    <button class="menu-btn d-lg-none" type="button" id="menuBtn">
                        <i class='bx bx-menu'></i>
                    </button>
                    <div class="menu-dropdown" id="menuDropdown">
                        <div class="menu-header">
                            <button class="menu-close" id="menuClose">&times;</button>
                        </div>
                        <div class="menu-links">
                            <a href="index.php" class="menu-link">HOME</a>
                            <a href="haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
                            <?php if ($customerID): ?>
                                <a href="customer/appointment.php" class="menu-link">MY APPOINTMENT</a>
                                <a href="logout.php" class="menu-link">LOGOUT</a>
                            <?php else: ?>
                                <a href="login.php" class="menu-link">LOGIN</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="navbar-nav mx-auto ps-5">
                        <a class="nav-link mx-4 nav-text fs-5" href="index.php">Home</a>
                        <a class="nav-link mx-4 nav-text fs-5" href="haircuts.php">HAIRCUTS & SERVICES</a>
                        <?php if ($customerID): ?>
                            <a class="nav-link mx-4 nav-text fs-5" href="customer/appointment.php">My Appointment</a>
                        <?php else: ?>
                            <a class="nav-link mx-4 nav-text fs-5" href="login.php">Login</a>
                        <?php endif; ?>
                    </div>
                    <div class="navbar-nav pe-5 me-4">
                        <?php if ($customerID): ?>
                            <button class="btn btn-dark me-2 px-4" 
                                onclick="document.location='customer/booking.php'" 
                                type="button" 
                                style="background-color: #000000; color: #FFDE59; border-radius: 12px;">Book Now</button>
                            <div class="dropdown">
                                <div class="user-header d-flex align-items-center" id="userDropdown">
                                    <div class="user-icon">
                                        <i class='bx bxs-user'></i>
                                    </div>
                                    <div class="user-greeting">
                                        <span class="user-name"><?php echo htmlspecialchars($firstName); ?></span>
                                    </div>
                                </div>
                                <div class="dropdown-menu" id="dropdownMenu">
                                    <a href="logout.php" class="dropdown-item">Logout</a>
                                </div>
                            </div>
                            <script>
                                const dropdownToggle = document.getElementById('userDropdown');
                                const dropdownMenu = document.getElementById('dropdownMenu');

                                dropdownToggle.addEventListener('click', function () {
                                    dropdownMenu.classList.toggle('show');
                                });

                                document.addEventListener('click', function (event) {
                                    if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                                        dropdownMenu.classList.remove('show');
                                    }
                                });
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Haircuts -->
        <div class="container fade-in">
            <h1 class="haircuts-header">Haircuts</h1>
            <div class="d-flex justify-content-center mb-4 gap-3">
                <button class="category-btn active" id="basic-btn" onclick="filterCategory('Basic')">Basic</button>
                <button class="category-btn inactive" id="premium-btn" onclick="filterCategory('Premium')">Premium</button>
            </div>
            <div class="row g-4 justify-content-center" id="gallery-container">
                <?php foreach ($haircuts as $haircut): ?>
                    <div class="col-12 col-sm-10 col-md-6 col-lg-3">
                        <div class="gallery-item">
                            <img src="data:image/jpeg;base64,<?php echo $haircut['hcImage']; ?>" alt="<?php echo htmlspecialchars($haircut['hcName']); ?>" />
                            <div class="gallery-title"><?php echo htmlspecialchars($haircut['hcName']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Services -->
        <div class="container fade-in services-section">
            <h1 class="haircuts-header">Services</h1>
            <div class="card-body p-0">
                <div class="container services-container" style="max-width: 800px;">
                    <table class="table" style="background-color: #FFDE59; border-radius: 15px; overflow: hidden;">
                        <thead>
                            <tr style="background-color: #000000; color: #FFDE59;">
                                <th style="font-size: 1.2rem; padding: 15px 15px 15px 25px; width: 25%;">Service Name</th>
                                <th style="font-size: 1.2rem; padding: 15px 15px 15px 25px; width: 55%;">Description</th>
                                <th style="font-size: 1.2rem; padding: 15px; width: 20%; text-align: center;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'customer/db_connect.php';
                            $query = "SELECT serviceName, servicePrice, serviceDesc FROM service_tbl";
                            $result = $conn->query($query);

                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td style='padding-left: 25px;'>" . htmlspecialchars($row['serviceName']) . "</td>";
                                    echo "<td style='padding-left: 25px;'>" . htmlspecialchars($row['serviceDesc']) . "</td>";
                                    echo "<td style='text-align: center; font-size: 1.2rem; font-weight: bold;'>₱" . htmlspecialchars($row['servicePrice']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3' class='text-center'>No services found.</td></tr>";
                            }
                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const menuBtn = document.getElementById('menuBtn');
                const menuClose = document.getElementById('menuClose');
                const menuDropdown = document.getElementById('menuDropdown');

                menuBtn.addEventListener('click', function() {
                    menuDropdown.classList.add('show');
                });

                menuClose.addEventListener('click', function() {
                    menuDropdown.classList.remove('show');
                });

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!menuDropdown.contains(event.target) && !menuBtn.contains(event.target)) {
                        menuDropdown.classList.remove('show');
                    }
                });
            });

            const haircuts = <?php echo json_encode($haircuts); ?>;
            function filterCategory(category) {
                const galleryContainer = document.getElementById("gallery-container");
                
                // Fade out current items
                const currentItems = galleryContainer.children;
                Array.from(currentItems).forEach(item => {
                    item.style.opacity = '0';
                    item.style.transform = 'translateY(20px)';
                });

                // Short delay before showing new items
                setTimeout(() => {
                    galleryContainer.innerHTML = "";

                    // Update button states
                    document.getElementById("basic-btn").classList.remove("active", "inactive");
                    document.getElementById("premium-btn").classList.remove("active", "inactive");
                    
                    document.getElementById("basic-btn").classList.add(category === "Basic" ? "active" : "inactive");
                    document.getElementById("premium-btn").classList.add(category === "Premium" ? "active" : "inactive");

                    haircuts
                        .filter(haircut => haircut.hcCategory === category)
                        .forEach((haircut, index) => {
                            galleryContainer.innerHTML += `
                                <div class="col-12 col-sm-10 col-md-6 col-lg-3">
                                    <div class="gallery-item" style="animation-delay: ${index * 0.1}s">
                                        <img src="data:image/jpeg;base64,${haircut.hcImage}" alt="${haircut.hcName}" />
                                        <div class="gallery-title">${haircut.hcName}</div>
                                    </div>
                                </div>`;
                        });
                }, 200); // 200ms delay before showing new items
            }
            filterCategory("Basic");
        </script>
    </div>
</body>
</html>
