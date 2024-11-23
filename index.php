<?php
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();
$sql = "SELECT s.*, c.name as class_name 
        FROM students s 
        LEFT JOIN classes c ON s.class_id = c.class_id 
        ORDER BY s.created_at DESC";
$result = $conn->query($sql);
?>

<div class="row mb-4">
    <div class="col">
        <h2>Students List</h2>
    </div>
    <div class="col text-end">
        <a href="create.php" class="btn btn-primary">Add New Student</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Email</th>
                <th>Class</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="uploads/<?php echo $row['image']; ?>" class="table-image" alt="Student Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/50" class="table-image" alt="No Image">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['class_name'] ?? 'No Class'); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No students found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>