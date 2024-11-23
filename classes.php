<?php
// classes.php
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();
$errors = [];
$success = '';

// Handle class creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $name = trim($_POST['name']);
            
            if (empty($name)) {
                $errors[] = "Class name is required";
            } else {
                $sql = "INSERT INTO classes (name) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $name);
                
                if ($stmt->execute()) {
                    $success = "Class created successfully";
                } else {
                    $errors[] = "Error creating class: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'edit') {
            $class_id = $_POST['class_id'];
            $name = trim($_POST['name']);
            
            if (empty($name)) {
                $errors[] = "Class name is required";
            } else {
                $sql = "UPDATE classes SET name = ? WHERE class_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $name, $class_id);
                
                if ($stmt->execute()) {
                    $success = "Class updated successfully";
                } else {
                    $errors[] = "Error updating class: " . $conn->error;
                }
            }
        } elseif ($_POST['action'] === 'delete') {
            $class_id = $_POST['class_id'];
            
            // Check if class has students
            $check_sql = "SELECT COUNT(*) as count FROM students WHERE class_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("i", $class_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $student_count = $check_result->fetch_assoc()['count'];
            
            if ($student_count > 0) {
                $errors[] = "Cannot delete class: There are students assigned to this class";
            } else {
                $sql = "DELETE FROM classes WHERE class_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $class_id);
                
                if ($stmt->execute()) {
                    $success = "Class deleted successfully";
                } else {
                    $errors[] = "Error deleting class: " . $conn->error;
                }
            }
        }
    }
}

// Fetch all classes
$classes_sql = "SELECT c.*, COUNT(s.id) as student_count 
                FROM classes c 
                LEFT JOIN students s ON c.class_id = s.class_id 
                GROUP BY c.class_id 
                ORDER BY c.name";
$classes_result = $conn->query($classes_sql);
?>

<div class="row mb-4">
    <div class="col">
        <h2>Manage Classes</h2>
    </div>
</div>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($success); ?>
    </div>
<?php endif; ?>

<!-- Create Class Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Create New Class</h5>
    </div>
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="create">
            <div class="col-auto">
                <input type="text" class="form-control" name="name" placeholder="Class Name" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Create Class</button>
            </div>
        </form>
    </div>
</div>

<!-- Classes List -->
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Class Name</th>
                <th>Students</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($classes_result->num_rows > 0): ?>
                <?php while($class = $classes_result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <form method="POST" class="edit-form d-none">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo htmlspecialchars($class['name']); ?>" required>
                                    <button type="submit" class="btn btn-sm btn-success">Save</button>
                                    <button type="button" class="btn btn-sm btn-secondary cancel-edit">Cancel</button>
                                </div>
                            </form>
                            <span class="class-name"><?php echo htmlspecialchars($class['name']); ?></span>
                        </td>
                        <td><?php echo $class['student_count']; ?> students</td>
                        <td><?php echo date('Y-m-d H:i', strtotime($class['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm edit-class">Edit</button>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this class?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center">No classes found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>