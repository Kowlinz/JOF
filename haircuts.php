<?php
session_start();

// Assume `customerID` is stored in the session after login
$customerID = $_SESSION['customerID'] ?? null;

// Only connect to DB and fetch name if user is logged in
$firstName = null;
if ($customerID) {
    include 'customer/db_connect.php';
    
    // Fetch user's firstName from customer_tbl
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
$sql = "SELECT * FROM haircut_tbl";
$result = $conn->query($sql);
$haircuts = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Ensure the image data is stored as a URL or path if it's a BLOB
        $imagePath = base64_encode($row['hcImage']); // If you're storing images as binary data
        $haircuts[] = [
            'hcID' => $row['hcID'],
            'hcName' => $row['hcName'],
            'hcImage' => $imagePath, // Ensure this is a valid image URL/path
            'hcCategory' => $row['hcCategory'],
        ];
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jack of Fades | Haircuts</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style1.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    <div class="main-page">
        <div class="header">
            <nav class="navbar navbar-expand-lg py-4">
                <div class="container ps-5">
                    <a class="navbar-brand ms-0" href="index.php">
                        <img src="css/images/jof_logo_black.png" alt="logo" width="45" height="45" class="desktop-logo">
                        <img src="css/images/jof_logo_yellow.png" alt="logo" width="45" height="45" class="mobile-logo">
                    </a>

                    <button class="menu-btn d-lg-none" type="button">
                        <i class='bx bx-menu'></i>
                    </button>

                    <div class="menu-dropdown">
                        <div class="menu-header">
                            <button class="menu-close">&times;</button>
                        </div>
                        <div class="menu-links">
                            <a href="index.php" class="menu-link">HOME</a>
                            <a href="haircuts.php" class="menu-link">HAIRCUTS</a>
                            <?php if ($customerID): ?>
                                <a href="customer/appointment.php" class="menu-link">MY APPOINTMENT</a>
                            <?php else: ?>
                                <a href="login.php" class="menu-link">LOGIN</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="navbar-nav mx-auto ps-5">
                        <a class="nav-link mx-4 nav-text fs-5" href="index.php">Home</a>
                        <a class="nav-link mx-4 nav-text fs-5" href="haircuts.php">Haircuts</a>
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
                        <?php endif; ?>
                    </div>
                </div>
            </nav>
        </div>

        <div class="container">
            <h1 class="haircuts-header">Haircuts</h1>
            <div class="d-flex justify-content-center mb-4 gap-3">
                <button class="category-btn active" id="basic-btn" onclick="filterCategory('Basic')">
                    Basic
                </button>
                <button class="category-btn inactive" id="premium-btn" onclick="filterCategory('Premium')">
                    Premium
                </button>
            </div>
            <div class="row g-4" id="gallery-container">
                <!-- Cards will be dynamically inserted here -->
                <?php foreach ($haircuts as $haircut): ?>
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <div class="gallery-item">
                            <img src="data:image/jpeg;base64,<?php echo $haircut['hcImage']; ?>" alt="<?php echo htmlspecialchars($haircut['hcName']); ?>" />
                            <div class="gallery-title"><?php echo htmlspecialchars($haircut['hcName']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <script>
            const haircuts = <?php echo json_encode($haircuts); ?>; // Pass PHP data to JavaScript
            let activeCategory = "Basic";

            function filterCategory(category) {
                activeCategory = category;
                const galleryContainer = document.getElementById("gallery-container");
                galleryContainer.innerHTML = "";

                const basicBtn = document.getElementById("basic-btn");
                const premiumBtn = document.getElementById("premium-btn");

                basicBtn.className = category === "Basic" ? "category-btn active" : "category-btn inactive";
                premiumBtn.className = category === "Premium" ? "category-btn active" : "category-btn inactive";

                haircuts
                    .filter(haircut => haircut.hcCategory === activeCategory)
                    .forEach(haircut => {
                        const col = document.createElement("div");
                        col.className = "col-sm-6 col-md-4 col-lg-3";
                        col.innerHTML = `
                            <div class="gallery-item">
                                <img src="data:image/jpeg;base64,${haircut.hcImage}" alt="${haircut.hcName}" />
                                <div class="gallery-title">${haircut.hcName}</div>
                            </div>
                        `;
                        galleryContainer.appendChild(col);
                    });
            }

            // Initialize gallery
            filterCategory("Basic");
        </script>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const menuBtn = document.querySelector('.menu-btn');
        const menuDropdown = document.querySelector('.menu-dropdown');
        const menuClose = document.querySelector('.menu-close');

        menuBtn.addEventListener('click', function() {
            menuDropdown.classList.add('show');
        });

        menuClose.addEventListener('click', function() {
            menuDropdown.classList.remove('show');
        });
    });
    </script>
</body>
</html>
