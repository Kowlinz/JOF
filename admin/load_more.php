<?php
include 'db_connect.php'; // Include your database connection

$offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$type = isset($_POST['type']) ? $_POST['type'] : 'completed'; // 'completed' or 'cancelled'
$limit = 10; // Number of records per load

if ($type === 'completed') {
    $query = "SELECT 
                a.appointmentID, a.date, a.timeSlot, a.status,
                CASE 
                    WHEN c.customerID IS NOT NULL THEN CONCAT(c.firstName, ' ', c.lastName)
                    ELSE 'Walk In' 
                END AS fullName,
                s.serviceName, 
                b.firstName AS barberFirstName, 
                b.lastName AS barberLastName
              FROM appointment_tbl a
              LEFT JOIN customer_tbl c ON a.customerID = c.customerID
              LEFT JOIN service_tbl s ON a.serviceID = s.serviceID
              LEFT JOIN barb_apps_tbl ba ON a.appointmentID = ba.appointmentID
              LEFT JOIN barbers_tbl b ON b.barberID = ba.barberID
              WHERE a.status = 'Completed'
              ORDER BY a.timeSlot ASC
              LIMIT $limit OFFSET $offset";
} else if ($type === 'cancelled') {
    $query = "SELECT a.*, c.firstName, c.lastName 
              FROM appointment_tbl a
              LEFT JOIN customer_tbl c ON a.customerID = c.customerID
              WHERE a.status = 'Cancelled'
              ORDER BY a.timeSlot ASC
              LIMIT $limit OFFSET $offset";
}

$result = mysqli_query($conn, $query);

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if ($type === 'completed') {
            $formattedDate = date("F d, Y", strtotime($row['date']));
            $isWalkIn = $row['fullName'] === 'Walk In' ? 'true' : 'false';

            echo "<tr>
                    <td>
                        <a href='#' onclick='showAppointmentDetails({$row['appointmentID']}, {$isWalkIn})' 
                        data-bs-toggle='modal' data-bs-target='#appointmentModal' 
                        style='text-decoration: none; color: inherit;'>
                            {$row['fullName']}
                        </a>
                    </td>
                    <td>{$formattedDate}</td>
                    <td>{$row['timeSlot']}</td>
                    <td>{$row['serviceName']}</td>
                    <td>{$row['barberFirstName']} {$row['barberLastName']}</td>
                </tr>";
        } else if ($type === 'cancelled') {
            $formattedDate = date("F d, Y", strtotime($row['date']));
            $firstName = isset($row['firstName']) ? $row['firstName'] : 'Walk';
            $lastName = isset($row['lastName']) ? $row['lastName'] : 'In';
            $fullName = "{$firstName} {$lastName}";
            $isWalkIn = ($row['customerID'] === null) ? 'true' : 'false';

            echo "<tr>
                    <td>
                        <a href='#' onclick='showAppointmentDetails({$row['appointmentID']}, {$isWalkIn})' 
                        data-bs-toggle='modal' data-bs-target='#appointmentModal' 
                        style='text-decoration: none; color: inherit;'>
                        {$fullName}
                        </a>
                    </td>
                    <td>{$formattedDate}</td>
                    <td>{$row['timeSlot']}</td>
                    <td>{$row['reason']}</td>
                </tr>";
        }
    }
} else {
    echo "";
}
?>
