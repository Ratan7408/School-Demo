<?php
// create.php
require_once 'config/database.php';
require_once 'includes/header.php';

$conn = getConnection();
$errors = [];
$success = false;

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
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_name = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed)) {
            $errors[] = "Only JPG and PNG files are allowed";
        } else {
            $image_name = uniqid() . '.' . $file_ext;
            $upload_path = 'uploads/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $errors[] = "Failed to upload image";
            }
        }
    }
    
    if (empty($errors)) {
        $sql = "INSERT INTO students (name, email, address, class_id, image) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $name, $email, $address, $class_id, $image_name);
        
        if ($stmt->execute()) {
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Error creating student: " . $conn->error;
            // Delete uploaded image if database insert failed
            if ($image_name && file_exists('uploads/' . $image_name)) {
                unlink('uploads/' . $image_name);
            }
        }
    }
}
?>

<div class="form-container">
    <h2>Create New Student</h2>
    
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
            <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="email" class="form-label">Email <span class="required">*</span></label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        
        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="class_id" class="form-label">Class</label>
            <select class="form-control" id="class_id" name="class_id">
                <option value="">Select Class</option>
                <?php while ($class = $classes_result->fetch_assoc()): ?>
                    <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($class['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="image" class="form-label">Image (JPG, PNG)</label>
            <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png">
        </div>
        
        <div class="mb-3">
            <button type="submit" class="btn btn-primary">Create Student</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'includes/footer.php';
?>