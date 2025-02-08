<?php
include 'db_connect.php'; // Ensure this is your correct DB connection file

if (isset($_GET['appointmentID'])) {
    $appointmentID = intval($_GET['appointmentID']);

    $query = "
    SELECT 
        a.appointmentID, 
        COALESCE(ad.addonName, 'N/A') AS addonName, 
        COALESCE(hc.hcName, 'N/A') AS hcName, 
        COALESCE(a.remarks, 'N/A') AS remarks
    FROM appointment_tbl a
    LEFT JOIN addon_tbl ad ON a.addonID = ad.addonID
    LEFT JOIN haircut_tbl hc ON a.hcID = hc.hcID
    WHERE a.appointmentID = $appointmentID
";


    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["addonName" => "", "hcName" => "", "remarks" => ""]);
    }
} else {
    echo json_encode(["error" => "Invalid request"]);
}
?>
