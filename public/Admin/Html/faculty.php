<?php
session_start();
include '../../../Database/Connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) && $_SESSION['role'] != 'Admin') {
    header("Location: ../../Auth/Html/login.html");
    exit();
}

$user = $_SESSION['user'];

$successMsg = '';
$errorMsg = '';
// Fetch all departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");

// --- ADD FACULTY ---
if (isset($_POST['add'])) {
    $username = trim($_POST['teacher_email']);
    $password = $username;
    $role = 'teacher';
    $department_id = $_POST['department_id'];
    $fullName = trim($_POST['full_name']);

    $stmt = $conn->prepare("INSERT INTO users (UserName, Password, role, department_id, FullName) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssis", $username, $password, $role, $department_id, $fullName);

    if ($stmt->execute()) {
        $successMsg = "✅ Teacher added successfully!";
    } else {
        $errorMsg = "❌ Failed to add teacher. Maybe username already exists.";
    }
}

// --- UPDATE FACULTY ---
if (isset($_POST['update'])) {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $username = trim($_POST['teacher_email']);
        $department_id = $_POST['department_id'];
        $fullName = trim($_POST['full_name']);
        $password = $username;

        $stmt = $conn->prepare("UPDATE users SET UserName=?, Password=?, department_id=?, FullName=? WHERE UserID=? AND role='teacher'");
        $stmt->bind_param("ssssi", $username, $password, $department_id, $fullName, $id);
        $stmt->execute();
        $successMsg = "✅ Teacher updated successfully!";
    } else {
        $errorMsg = "⚠️ Invalid teacher ID!";
    }
}

// --- DELETE FACULTY ---
if (isset($_POST['delete'])) {
    $id = $_POST['id'] ?? 0;
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE UserID=? AND role='teacher'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $successMsg = "✅ Teacher deleted successfully!";
    } else {
        $errorMsg = "⚠️ Invalid teacher ID!";
    }
}

// Fetch all teachers
$teachers = $conn->query("
    SELECT u.UserID, u.UserName, u.FullName, u.department_id, d.name AS department_name
    FROM users u
    JOIN departments d ON u.department_id = d.id
    WHERE u.role='teacher'
    ORDER BY u.UserID DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Faculty Management</title>
<link rel="stylesheet" href="../css/Faculty.css">
 <link rel="stylesheet" href="../Css/Admin.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<?php include 'side_menu.php'; ?>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-chalkboard-teacher"></i> Faculty Management</h1>
    </div>

    <!-- Toast -->
    <div id="toastMessage" class="toast-message"></div>

    <!-- Add Faculty Form -->
    <div class="form-box">
        <h2><i class="fas fa-plus-circle"></i> Add New Faculty Member</h2>
        <form method="POST">
            <div class="form-group">
                <label for="teacher_email">Email Address</label>
                <input type="email" id="teacher_email" name="teacher_email" placeholder="Enter email address" required>
            </div>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" placeholder="Enter full name" required>
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
                <i class="fas fa-plus"></i> Add Faculty
            </button>
        </form>
    </div>

    <!-- Faculty Table -->
    <div class="table-container">
        <div class="table-header">
            <h2><i class="fas fa-list"></i> Faculty Members</h2>
            <span class="faculty-count"><?php echo $teachers->num_rows; ?> Faculty Members</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Email / Username</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($teachers->num_rows > 0): ?>
                    <?php while($row = $teachers->fetch_assoc()): ?>
                        <tr>
                            <td><span class="faculty-id">#<?= $row['UserID'] ?></span></td>
                            <td>
                                <div class="faculty-info">
                                    <span class="faculty-email"><?= htmlspecialchars($row['UserName']) ?></span>
                                </div>
                                <form method="POST" class="edit-mode" id="edit-form-<?= $row['UserID'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['UserID'] ?>">
                                    <input type="email" name="teacher_email" value="<?= htmlspecialchars($row['UserName']) ?>" class="edit-input" required>
                                    <input type="text" name="full_name" value="<?= htmlspecialchars($row['FullName']) ?>" class="edit-input" required>
                                    <select name="department_id" class="edit-select" required>
                                        <?php
                                        $departments->data_seek(0);
                                        while($dept = $departments->fetch_assoc()):
                                        ?>
                                            <option value="<?= $dept['id'] ?>" <?= $dept['id']==$row['department_id']?'selected':'' ?>>
                                                <?= htmlspecialchars($dept['name']) ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <div class="action-buttons">
                                        <button type="submit" name="update" class="action-btn update-btn">
                                            <i class="fas fa-check"></i> Save
                                        </button>
                                        <button type="button" class="action-btn cancel-btn" onclick="cancelEdit(<?= $row['UserID'] ?>)">
                                            <i class="fas fa-times"></i> Cancel
                                        </button>
                                    </div>
                                </form>
                            </td>
                            <td><?= htmlspecialchars($row['FullName']) ?></td>
                            <td><span class="faculty-department"><?= htmlspecialchars($row['department_name']) ?></span></td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="action-btn edit-toggle-btn" onclick="toggleEdit(<?= $row['UserID'] ?>)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $row['UserID'] ?>">
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
                        <td colspan="5">
                            <div class="empty-state">
                                <i class="fas fa-chalkboard-teacher"></i>
                                <p>No faculty members found.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Toast messages
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

// Edit toggle
function toggleEdit(userId){
    const facultyInfo = document.querySelector(`#edit-form-${userId}`).parentElement.querySelector('.faculty-info');
    const editForm = document.getElementById(`edit-form-${userId}`);
    facultyInfo.style.display='none';
    editForm.style.display='grid';
}

function cancelEdit(userId){
    const facultyInfo = document.querySelector(`#edit-form-${userId}`).parentElement.querySelector('.faculty-info');
    const editForm = document.getElementById(`edit-form-${userId}`);
    facultyInfo.style.display='flex';
    editForm.style.display='none';
}

document.addEventListener('DOMContentLoaded', ()=>{
    document.querySelectorAll('.edit-mode').forEach(f=>f.style.display='none');
});
</script>
</body>
</html>
