-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2025 at 04:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jof_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `addon_tbl`
--

CREATE TABLE `addon_tbl` (
  `addonID` int(11) NOT NULL,
  `addonName` varchar(50) DEFAULT NULL,
  `addonPrice` varchar(50) DEFAULT NULL,
  `addonDesc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_tbl`
--

CREATE TABLE `admin_tbl` (
  `adminID` int(11) NOT NULL,
  `earningsID` int(11) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `dateOfBirth` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contactNum` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `appointment_tbl`
--

CREATE TABLE `appointment_tbl` (
  `appointmentID` int(11) NOT NULL,
  `customerID` int(11) DEFAULT NULL,
  `adminID` int(11) DEFAULT NULL,
  `addonID` int(11) DEFAULT NULL,
  `hcID` int(11) DEFAULT NULL,
  `serviceID` int(11) NOT NULL,
  `status` varchar(255) DEFAULT NULL,
  `date` date NOT NULL,
  `timeSlot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `payment_status` enum('pending','partial','paid') DEFAULT 'pending',
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `gcash_reference` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barberpic_tbl`
--

CREATE TABLE `barberpic_tbl` (
  `barberpicID` int(11) NOT NULL,
  `barberName` varchar(100) NOT NULL,
  `barbDesc` varchar(255) NOT NULL,
  `barberPic` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barbers_tbl`
--

CREATE TABLE `barbers_tbl` (
  `barberID` int(11) NOT NULL,
  `barbappsID` int(11) DEFAULT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) DEFAULT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contactNum` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `availability` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barb_apps_tbl`
--

CREATE TABLE `barb_apps_tbl` (
  `barbappsID` int(11) NOT NULL,
  `appointmentID` int(11) NOT NULL,
  `adminID` int(11) DEFAULT NULL,
  `barberID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_tbl`
--

CREATE TABLE `customer_tbl` (
  `customerID` int(11) NOT NULL,
  `firstName` varchar(255) DEFAULT NULL,
  `middleName` varchar(255) NOT NULL,
  `lastName` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `contactNum` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `verify_token` varchar(255) NOT NULL,
  `verify_status` tinyint(2) NOT NULL DEFAULT 0 COMMENT '0=no, 1=yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `earnings_tbl`
--

CREATE TABLE `earnings_tbl` (
  `earningsID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL,
  `appointmentID` int(11) NOT NULL,
  `barberID` int(11) NOT NULL,
  `adminEarnings` float DEFAULT NULL,
  `barberEarnings` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `haircut_tbl`
--

CREATE TABLE `haircut_tbl` (
  `hcID` int(11) NOT NULL,
  `hcName` varchar(55) DEFAULT NULL,
  `hcImage` longblob DEFAULT NULL,
  `hcCategory` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service_tbl`
--

CREATE TABLE `service_tbl` (
  `serviceID` int(11) NOT NULL,
  `serviceName` varchar(50) DEFAULT NULL,
  `servicePrice` varchar(50) DEFAULT NULL,
  `serviceDesc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addon_tbl`
--
ALTER TABLE `addon_tbl`
  ADD PRIMARY KEY (`addonID`);

--
-- Indexes for table `admin_tbl`
--
ALTER TABLE `admin_tbl`
  ADD PRIMARY KEY (`adminID`);

--
-- Indexes for table `appointment_tbl`
--
ALTER TABLE `appointment_tbl`
  ADD PRIMARY KEY (`appointmentID`),
  ADD KEY `customerID` (`customerID`),
  ADD KEY `addonID` (`addonID`),
  ADD KEY `serviceID` (`serviceID`),
  ADD KEY `haircutID` (`hcID`),
  ADD KEY `appointment_tbl_ibfk_5` (`adminID`);

--
-- Indexes for table `barberpic_tbl`
--
ALTER TABLE `barberpic_tbl`
  ADD PRIMARY KEY (`barberpicID`);

--
-- Indexes for table `barbers_tbl`
--
ALTER TABLE `barbers_tbl`
  ADD PRIMARY KEY (`barberID`);

--
-- Indexes for table `barb_apps_tbl`
--
ALTER TABLE `barb_apps_tbl`
  ADD PRIMARY KEY (`barbappsID`),
  ADD KEY `appointmentID` (`appointmentID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `barb_apps_tbl_ibfk_3` (`barberID`);

--
-- Indexes for table `customer_tbl`
--
ALTER TABLE `customer_tbl`
  ADD PRIMARY KEY (`customerID`);

--
-- Indexes for table `earnings_tbl`
--
ALTER TABLE `earnings_tbl`
  ADD PRIMARY KEY (`earningsID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `appointmentID` (`appointmentID`),
  ADD KEY `barberID` (`barberID`);

--
-- Indexes for table `haircut_tbl`
--
ALTER TABLE `haircut_tbl`
  ADD PRIMARY KEY (`hcID`);

--
-- Indexes for table `service_tbl`
--
ALTER TABLE `service_tbl`
  ADD PRIMARY KEY (`serviceID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addon_tbl`
--
ALTER TABLE `addon_tbl`
  MODIFY `addonID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_tbl`
--
ALTER TABLE `admin_tbl`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointment_tbl`
--
ALTER TABLE `appointment_tbl`
  MODIFY `appointmentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=207;

--
-- AUTO_INCREMENT for table `barberpic_tbl`
--
ALTER TABLE `barberpic_tbl`
  MODIFY `barberpicID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barbers_tbl`
--
ALTER TABLE `barbers_tbl`
  MODIFY `barberID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `barb_apps_tbl`
--
ALTER TABLE `barb_apps_tbl`
  MODIFY `barbappsID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `customer_tbl`
--
ALTER TABLE `customer_tbl`
  MODIFY `customerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `earnings_tbl`
--
ALTER TABLE `earnings_tbl`
  MODIFY `earningsID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `haircut_tbl`
--
ALTER TABLE `haircut_tbl`
  MODIFY `hcID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `service_tbl`
--
ALTER TABLE `service_tbl`
  MODIFY `serviceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment_tbl`
--
ALTER TABLE `appointment_tbl`
  ADD CONSTRAINT `appointment_tbl_ibfk_1` FOREIGN KEY (`customerID`) REFERENCES `customer_tbl` (`customerID`),
  ADD CONSTRAINT `appointment_tbl_ibfk_2` FOREIGN KEY (`addonID`) REFERENCES `addon_tbl` (`addonID`),
  ADD CONSTRAINT `appointment_tbl_ibfk_3` FOREIGN KEY (`serviceID`) REFERENCES `service_tbl` (`serviceID`),
  ADD CONSTRAINT `appointment_tbl_ibfk_4` FOREIGN KEY (`hcID`) REFERENCES `haircut_tbl` (`hcID`),
  ADD CONSTRAINT `appointment_tbl_ibfk_5` FOREIGN KEY (`adminID`) REFERENCES `admin_tbl` (`adminID`);

--
-- Constraints for table `barb_apps_tbl`
--
ALTER TABLE `barb_apps_tbl`
  ADD CONSTRAINT `barb_apps_tbl_ibfk_1` FOREIGN KEY (`appointmentID`) REFERENCES `appointment_tbl` (`appointmentID`),
  ADD CONSTRAINT `barb_apps_tbl_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin_tbl` (`adminID`),
  ADD CONSTRAINT `barb_apps_tbl_ibfk_3` FOREIGN KEY (`barberID`) REFERENCES `barbers_tbl` (`barberID`);

--
-- Constraints for table `earnings_tbl`
--
ALTER TABLE `earnings_tbl`
  ADD CONSTRAINT `earnings_tbl_ibfk_1` FOREIGN KEY (`adminID`) REFERENCES `admin_tbl` (`adminID`),
  ADD CONSTRAINT `earnings_tbl_ibfk_2` FOREIGN KEY (`appointmentID`) REFERENCES `appointment_tbl` (`appointmentID`),
  ADD CONSTRAINT `earnings_tbl_ibfk_3` FOREIGN KEY (`barberID`) REFERENCES `barbers_tbl` (`barberID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
