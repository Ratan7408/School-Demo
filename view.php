

<?php
// view.php
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$sql = "SELECT s.*, c.name as class_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        WHERE s.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$student = $result->fetch_assoc();
?>

<div class="row">
    <div class="col-md-8 offset-md-2">
        <h2>Student Details</h2>
        
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <?php if($student['image']): ?>
                        <img src="uploads/<?php echo htmlspecialchars($student['image']); ?>" class="student-image-large mb-3" alt="Student Image">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/300" class="student-image-large mb-3" alt="No Image">
                    <?php endif; ?>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Name:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($student['name']); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Email:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($student['email']); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Address:</div>
                    <div class="col-md-9"><?php echo nl2br(htmlspecialchars($student['address'])); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Class:</div>
                    <div class="col-md-9"><?php echo htmlspecialchars($student['class_name'] ?? 'No Class'); ?></div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-3 fw-bold">Created At:</div>
                    <div class="col-md-9"><?php echo date('F j, Y H:i:s', strtotime($student['created_at'])); ?></div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="edit.php?id=<?php echo $student['id']; ?>" class="btn btn-warning">Edit</a>
                    <a href="delete.php?id=<?php echo $student['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>