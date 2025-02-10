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
	<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
	<link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
	<style>
		body {
			font-family: 'Lexend', sans-serif;
		}
		
		/* Navbar animation */
		.header {
			opacity: 0;
			transform: translateY(-20px);
			animation: navSlideDown 0.8s ease forwards;
			position: relative;
			z-index: 1000;
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

		@media screen and (max-width: 1321px) {
			.homepage-logo {
				display: none;
			}
		}
		
		/* New CSS for fade-in animation */
		.fade-in {
			opacity: 0;
			animation: fadeIn ease-in 1s forwards;
		}

		@keyframes fadeIn {
			from {
				opacity: 0;
			}
			to {
				opacity: 1;
			}
		}

		.carousel-container {
			position: fixed;
			right: 0;
			top: 50%;
			transform: translateY(-45%);
			width: 650px;
			padding: 20px;
			padding-right: 80px;
			opacity: 0;
			animation: carouselFadeIn 1s ease forwards;
			animation-delay: 0.5s;
			z-index: 1;
		}
		
		.swiper {
			width: 100%;
			height: 760px;
		}

		.carousel-slide {
			position: relative;
			aspect-ratio: 3/4;
			border-radius: 8px;
			overflow: hidden;
		}

		.carousel-image {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}

		.carousel-content {
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			padding: 24px;
			background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
			color: white;
		}

		.carousel-title {
			font-size: 2rem;
			font-weight: bold;
			margin-bottom: 8px;
			color: #FFDE59;
		}

		.carousel-description {
			font-size: 1.1rem;
			color: #e5e5e5;
			line-height: 1.5;
		}

		.swiper-button-next,
		.swiper-button-prev {
			display: none;  /* Hide the navigation arrows */
		}

		// Add these media queries for responsiveness
		@media screen and (max-width: 1400px) {
			.carousel-container {
				width: 450px;
				padding-right: 60px;
			}
		}

		@media screen and (max-width: 1200px) {
			.carousel-container {
				width: 400px;
				padding-right: 45px;
			}
			
			.swiper {
				height: 650px;
			}
		}

		@media screen and (max-width: 1460px) {
			.carousel-container {
				position: relative;
				transform: none;
				width: 100%;
				top: auto;
				margin: 80px auto;
				padding-bottom: 80px;
				/* Adjust animation for responsive layout */
				animation: carouselFadeInMobile 1s ease forwards;
				animation-delay: 0.5s;
			}

			.swiper {
				max-width: 500px;
				margin: 0 auto;
				height: 650px;
			}
		}

		@media screen and (max-width: 992px) {
			.carousel-container {
				position: relative;
				transform: none;
				width: 100%;
				top: auto;
				margin: 80px auto;
				padding-bottom: 80px;
			}

			.swiper {
				max-width: 500px;
				margin: 0 auto;
				height: 650px;
			}
		}

		@media screen and (max-width: 576px) {
			.carousel-container {
				padding: 10px;
				margin: 60px auto;
				padding-bottom: 60px;
			}

			.swiper {
				max-width: 100%;
				height: 600px;
			}

			.carousel-content {
				padding: 20px;
			}

			.carousel-title {
				font-size: 1.75rem;
			}

			.carousel-description {
				font-size: 1rem;
			}
		}

		/* Add the carousel fade-in animation */
		@keyframes carouselFadeIn {
			from {
				opacity: 0;
				transform: translateY(-45%) translateX(20px);
			}
			to {
				opacity: 1;
				transform: translateY(-45%) translateX(0);
			}
		}

		/* Add mobile animation */
		@keyframes carouselFadeInMobile {
			from {
				opacity: 0;
				transform: translateY(20px);
			}
			to {
				opacity: 1;
				transform: translateY(0);
			}
		}

		/* Add new media query for short screens */
		@media screen and (max-height: 930px) {
			.carousel-container {
				width: 550px;
				padding-right: 110px;
			}

			.swiper {
				height: 700px;
			}
		}

		/* Add new media query for very short screens */
		@media screen and (max-height: 865px) {
			.carousel-container {
				top: 55%;  /* Move container down by increasing top percentage */
			}
		}

		/* Add new media query for extremely short screens */
		@media screen and (max-height: 780px) {
			.carousel-container {
				top: 60%;  /* Further increase top percentage for more space */
			}
		}
	</style>
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
				<!-- Slides -->
				<div class="swiper-slide">
					<div class="carousel-slide">
						<img src="css/images/portraits/jake.jpg" 
							 alt="Barber working on client's hair"
							 class="carousel-image">
						<div class="carousel-content">
							<h3 class="carousel-title">JAKE CLARENCE</h3>
							<p class="carousel-description">
							Skilled barber with expertise in Jack's Signature styles. His specialties include the Burst Fade and Taper Fade, leaving each client with a precise and stylish look.
							</p>
						</div>
					</div>
				</div>
				<div class="swiper-slide">
					<div class="carousel-slide">
						<img src="css/images/portraits/jhun.jpg" 
							 alt="Barber shop interior"
							 class="carousel-image">
						<div class="carousel-content">
							<h3 class="carousel-title">JHUN</h3>
							<p class="carousel-description">
							Talented barber with expertise in Jack's Haircut, Signature, and Premium Perm. He specializes in Fades and the Mullet style, providing each client with a unique and polished appearance.
							</p>
						</div>
					</div>
				</div>
				<div class="swiper-slide">
					<div class="carousel-slide">
						<img src="css/images/portraits/joshua.jpg" 
							 alt="Barber shop interior"
							 class="carousel-image">
						<div class="carousel-content">
							<h3 class="carousel-title">JOSHUA</h3>
							<p class="carousel-description">
							A stylist with expertise in Jack's Haircut, Signature, and Facial services. He specializes in Fades and Modern Haircuts, delivering a fresh and contemporary look to each client.
							</p>
						</div>
					</div>
				</div>
			</div>
			<!-- Navigation buttons -->
			<div class="swiper-button-prev"></div>
			<div class="swiper-button-next"></div>
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

	<!-- Add Swiper JS -->
	<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
	<script>
		const swiper = new Swiper('.swiper', {
			// Optional parameters
			direction: 'horizontal',
			loop: true,

			// Add autoplay
			autoplay: {
				delay: 5000, // 5000ms = 5 seconds
				disableOnInteraction: false, // Continue autoplay after user interaction
			},

			// Navigation arrows
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
		});
	</script>
</body>
</html>
