<?php
// delete.php
require_once 'config/database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$conn = getConnection();
$id = $_GET['id'];

// Get student image before deletion
$sql = "SELECT image FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student = $result->fetch_assoc();
    
    // Delete student from database
    $delete_sql = "DELETE FROM students WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $id);
    
    if ($delete_stmt->execute()) {
        // Delete image file if exists
        if ($student['image'] && file_exists('uploads/' . $student['image'])) {
            unlink('uploads/' . $student['image']);
        }
    }
}

$conn->close();
header("Location: index.php");
exit;
?>