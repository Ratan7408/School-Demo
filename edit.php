<?php
// edit.php
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'];
$errors = [];

// Fetch student data
$sql = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit;
}

$student = $result->fetch_assoc();

// Fetch classes for dropdown
$classes_sql = "SELECT * FROM classes ORDER BY name";
$classes_result = $conn->query($classes_sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $class_id = $_POST['class_id'];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Handle image upload
    $image_name = $student['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $errors[] = "Only JPG and PNG files are allowed";
        } else {
            $image_name = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $image_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                // Delete old image if exists
                if ($student['image'] && file_exists('uploads/' . $student['image'])) {
                    unlink('uploads/' . $student['image']);
                }
            } else {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        $sql = "UPDATE students SET name = ?, email = ?, address = ?, class_id = ?, image = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssisi", $name, $email, $address, $class_id, $image_name, $id);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Error updating student: " . $conn->error;
        }
    }
}
?>

<div class="form-container">
    <h2>Edit Student</h2>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name <span class="required">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($student['name']); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="required">*</span></label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="class_id" class="form-label">Class</label>
            <select class="form-control" id="class_id" name="class_id">
                <option value="">Select Class</option>
                <?php while ($class = $classes_result->fetch_assoc()): ?>
                    <option value="<?php echo $class['class_id']; ?>" <?php echo ($student['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="image" class="form-label">Image (JPG, PNG)</label>
            <?php if ($student['image']): ?>
                <div class="mb-2">
                    <img src="uploads/<?php echo htmlspecialchars($student['image']); ?>" class="student-image" alt="Current Image">
                </div>
            <?php endif; ?>
            <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Update Student</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>