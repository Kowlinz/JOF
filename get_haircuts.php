<?php
include 'db_connect.php';

$category = $_GET['category'] ?? 'Basic';
$sql = "SELECT * FROM haircut_tbl WHERE hcCategory = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $category);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    ?>
    <div class="haircut-item" data-category="<?php echo $row['hcCategory']; ?>" data-id="<?php echo $row['hcID']; ?>">
        <div class="position-relative">
            <img src="data:image/jpeg;base64,<?php echo base64_encode($row['hcImage']); ?>" 
                 alt="<?php echo $row['hcName']; ?>">
            <button class="delete-btn btn btn-danger" style="display: none;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <p class="text-center mt-2"><?php echo $row['hcName']; ?></p>
    </div>
    <?php
}

$stmt->close();
$conn->close();
?> 