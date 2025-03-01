<?php
session_start();
require 'database.php';

// Assume `customerID` is stored in the session after login
$customerID = $_SESSION['customerID'] ?? null;

// Only connect to DB and fetch name if user is logged in
$firstName = null;
if ($customerID) {
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
}

// Include the landing text file
include 'admin/landing_text.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Jack of Fades</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
	<link rel="icon" href="css/images/favicon.ico">
	<link rel="stylesheet" href="css/style1.css">
	<link rel="stylesheet" href="css/index.css">
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
</head>
<body>
	<div class="main-page">
  		<div class="header">
			<nav class="navbar navbar-expand-lg py-4">
				<div class="container ps-5">
					<a class="navbar-brand ms-0" href="#">
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
							<?php if ($customerID): ?>
								<a href="haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
								<a href="customer/appointment.php" class="menu-link">MY APPOINTMENT</a>
								<a href="logout.php" class="menu-link">LOGOUT</a>
							<?php else: ?>
								<a href="haircuts.php" class="menu-link">HAIRCUTS & SERVICES</a>
								<a href="login.php" class="menu-link">LOGIN</a>
							<?php endif; ?>
						</div>
					</div>

					<div class="navbar-nav mx-auto ps-5">
						<a class="nav-link mx-4 nav-text fs-5" href="index.php">Home</a>
						<?php if ($customerID): ?>
							<a class="nav-link mx-4 nav-text fs-5" href="haircuts.php">HAIRCUTS & SERVICES</a>
							<a class="nav-link mx-4 nav-text fs-5" href="customer/appointment.php">My Appointment</a>
						<?php else: ?>
							<a class="nav-link mx-4 nav-text fs-5" href="haircuts.php">HAIRCUTS & SERVICES</a>
							<a class="nav-link mx-4 nav-text fs-5" href="login.php">Login</a>
						<?php endif; ?>
					</div>

					<div class="navbar-nav pe-5 me-4">
						<?php if ($customerID): ?>
							<button class="btn btn-dark me-2 px-4" 
								onclick="document.location='customer/booking.php'" 
								type="button" 
								style="background-color: #000000; color: #FFDE59; border-radius: 12px;">Book Now
							</button>
							
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
									<a href="customer/account.php" class="dropdown-item">Account</a>
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

		<div class="content">
			<div style="display: flex; justify-content: space-between; align-items: center;">
				<div>
					<h4 class="mb-4"><?php echo htmlspecialchars($welcomeText); ?></h4>
					<h1 class="mb-4"><?php echo htmlspecialchars($headingText); ?></h1>
					<h4 class="mb-5"><?php echo htmlspecialchars($subheadingText); ?></h4>
					<button class="mt-3" onclick="document.location='<?php echo $customerID ? 'customer/booking.php' : 'login.php'; ?>'" type="button">Book Now</button>
					
					<div class="Follow mt-5">
						<a href="https://www.facebook.com/jackoffades1?mibextid=ZbWKwL" target="_blank"><i class='bx bxl-facebook-circle'></i></a>
						<a href="https://www.instagram.com/jack_fades?igsh=cTl3MWF2dmZ1ZThm" target="_blank"><i class='bx bxl-instagram'></i></a>
						<a href="https://www.tiktok.com/@jackoffades_barbershop?_t=8rkR3rPGF4T&_r=1" target="_blank"><i class='bx bxl-tiktok'></i></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="carousel-container">
		<!-- Slider main container -->
		<div class="swiper">
			<!-- Additional required wrapper -->
			<div class="swiper-wrapper">
				<?php
				// Create a new connection for the carousel
				include 'database.php';
				
				$carouselQuery = "SELECT * FROM barberpic_tbl";
				$carouselResult = mysqli_query($conn, $carouselQuery);
				
				if (!$carouselResult) {
					echo "Error: " . mysqli_error($conn);
				} else {
					while ($row = mysqli_fetch_assoc($carouselResult)) {
						echo '<div class="swiper-slide">
								<div class="carousel-slide">
									<img src="data:image/jpeg;base64,' . base64_encode($row['barberPic']) . '" 
										 alt="' . htmlspecialchars($row['barberName']) . '"
										 class="carousel-image">
									<div class="carousel-content">
										<h3 class="carousel-title">' . htmlspecialchars($row['barberName']) . '</h3>
										<p class="carousel-description">' . htmlspecialchars($row['barbDesc']) . '</p>
									</div>
								</div>
							</div>';
					}
				}
				mysqli_close($conn);
				?>
			</div>
		</div>
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

	<!-- Move scripts right before closing body tag -->
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			// Initialize Swiper
			const swiper = new Swiper('.swiper', {
				direction: 'horizontal',
				loop: true,
				autoplay: {
					delay: 5000,
					disableOnInteraction: false,
				},
				breakpoints: {
					320: {
						slidesPerView: 1,
						spaceBetween: 20
					},
					768: {
						slidesPerView: 1,
						spaceBetween: 30
					}
				}
			});
		});
	</script>
</body>
</html>
