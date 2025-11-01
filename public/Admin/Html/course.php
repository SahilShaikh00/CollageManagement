<?php
session_start();
include '../../../Database/Connection.php';


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'Admin') {
    
    echo(" user Not Authorized to access this page.");
    exit();
}

$user = $_SESSION['user'];
$successMsg = '';
$errorMsg = '';

// ✅ Fetch all departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");

// ✅ ADD COURSE
if (isset($_POST['add'])) {
    $department_id = $_POST['department_id'];
    $course_name = trim($_POST['course_name']);

    if ($course_name != '' && $department_id != '') {
        $stmt = $conn->prepare("INSERT INTO courses (departmentsId, courseName) VALUES (?, ?)");
        $stmt->bind_param("is", $department_id, $course_name);

        if ($stmt->execute()) {
            $successMsg = "✅ Course added successfully!";
        } else {
            $errorMsg = "❌ Failed to add course. Possibly duplicate name.";
        }
    } else {
        $errorMsg = "⚠️ All fields are required!";
    }
}

// ✅ UPDATE COURSE
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? 0;
    $department_id = $_POST['department_id'];
    $course_name = trim($_POST['course_name']);

    if ($id > 0 && $course_name != '') {
        $stmt = $conn->prepare("UPDATE courses SET courseName=?, departmentId=? WHERE id=?");
        $stmt->bind_param("sii", $course_name, $department_id, $id);
        if ($stmt->execute()) {
            $successMsg = "✅ Course updated successfully!";
        } else {
            $errorMsg = "❌ Failed to update course.";
        }
    } else {
        $errorMsg = "⚠️ Invalid course details!";
    }
}

// ✅ DELETE COURSE
if (isset($_POST['delete'])) {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $successMsg = "✅ Course deleted successfully!";
        } else {
            $errorMsg = "❌ Failed to delete course.";
        }
    } else {
        $errorMsg = "⚠️ Invalid course ID!";
    }
}

// ✅ FETCH ALL COURSES
$courses = $conn->query("
    SELECT c.id, c.courseName, d.name AS department_name, c.departmentsId 
    FROM courses c
    JOIN departments d ON c.departmentsId = d.id
    ORDER BY c.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Course Management</title>
<link rel="stylesheet" href="../Css/Admin.css">
<link rel="stylesheet" href="../Css/Faculty.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<?php include 'side_menu.php'; ?>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-book"></i> Course Management</h1>
    </div>

    <!-- Toast -->
    <div id="toastMessage" class="toast-message"></div>

    <!-- Add Course Form -->
    <div class="form-box">
        <h2><i class="fas fa-plus-circle"></i> Add New Course</h2>
        <form method="POST">
            <div class="form-group">
                <label for="course_name">Course Name</label>
                <input type="text" id="course_name" name="course_name" placeholder="Enter course name" required>
            </div>
            <div class="form-group">
                <label for="department_id">Department</label>
                <select id="department_id" name="department_id" required>
                    <option value="">Select Department</option>
                    <?php
                    $departments->data_seek(0);
                    while($dept = $departments->fetch_assoc()):
                    ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" name="add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Course
            </button>
        </form>
    </div>

    <!-- Course Table -->
    <div class="table-container">
        <div class="table-header">
            <h2><i class="fas fa-list"></i> Courses List</h2>
            <span class="faculty-count"><?php echo $courses->num_rows; ?> Total Courses</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($courses->num_rows > 0): ?>
                    <?php while($row = $courses->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div class="faculty-info">
                                    <span class="faculty-email"><?= htmlspecialchars($row['courseName']) ?></span>
                                </div>
                                <form method="POST" class="edit-mode" id="edit-form-<?= $row['id'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="course_name" value="<?= htmlspecialchars($row['courseName']) ?>" class="edit-input" required>
                                    <select name="department_id" class="edit-select" required>
                                        <?php
                                        $departments->data_seek(0);
                                        while($dept = $departments->fetch_assoc()):
                                        ?>
                                            <option value="<?= $dept['id'] ?>" <?= $dept['id']==$row['departmentsId']?'selected':'' ?>>
                                                <?= htmlspecialchars($dept['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="action-buttons">
                                        <button type="submit" name="update" class="action-btn update-btn">
                                            <i class="fas fa-check"></i> Save
                                        </button>
                                        <button type="button" class="action-btn cancel-btn" onclick="cancelEdit(<?= $row['id'] ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($row['department_name']) ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="action-btn edit-toggle-btn" onclick="toggleEdit(<?= $row['id'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete" class="action-btn delete-btn" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">
                            <div class="empty-state">
                                <i class="fas fa-book-open"></i>
                                <p>No courses found.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ✅ Toast messages
<?php if (!empty($successMsg)): ?>
    showToast("<?= addslashes($successMsg) ?>", "success");
<?php endif; ?>
<?php if (!empty($errorMsg)): ?>
    showToast("<?= addslashes($errorMsg) ?>", "error");
<?php endif; ?>

function showToast(message, type = "success", duration = 4000) {
    const toast = document.getElementById('toastMessage');
    toast.innerHTML = `<i class="fas ${type==="success"?"fa-check-circle":"fa-exclamation-circle"}"></i><div class="toast-content">${message}</div><button class="toast-close" onclick="hideToast()"><i class="fas fa-times"></i></button>`;
    toast.className = `toast-message show ${type}`;
    setTimeout(hideToast, duration);
}

function hideToast() {
    const toast = document.getElementById('toastMessage');
    toast.classList.add('hide');
    setTimeout(()=>toast.classList.remove('show','hide'),500);
}

// ✅ Edit toggle
function toggleEdit(id){
    const info = document.querySelector(`#edit-form-${id}`).parentElement.querySelector('.faculty-info');
    const form = document.getElementById(`edit-form-${id}`);
    info.style.display='none';
    form.style.display='grid';
}

function cancelEdit(id){
    const info = document.querySelector(`#edit-form-${id}`).parentElement.querySelector('.faculty-info');
    const form = document.getElementById(`edit-form-${id}`);
    info.style.display='flex';
    form.style.display='none';
}

document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.edit-mode').forEach(f=>f.style.display='none');
});
</script>
</body>
</html>
