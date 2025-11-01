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

// ADD Department
if (isset($_POST['add'])) {
    $name = trim($_POST['dept_name']);
    if ($name != '') {
        $stmt = $conn->prepare("INSERT INTO departments (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $successMsg = "✅ Department Added Successfully!";
        } else {
            $errorMsg = "❌ Failed to Add Department. Try again.";
        }
    } else {
        $errorMsg = "⚠️ Department name cannot be empty!";
    }
}

// UPDATE Department
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = trim($_POST['dept_name']);
    if ($name != '') {
        $stmt = $conn->prepare("UPDATE departments SET name=? WHERE id=?");
        $stmt->bind_param("si", $name, $id);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $successMsg = "✅ Department Updated Successfully!";
        } else {
            $errorMsg = "⚠️ No changes made or invalid ID.";
        }
    } else {
        $errorMsg = "⚠️ Department name cannot be empty!";
    }
}

// DELETE Department
if (isset($_POST['delete'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        $successMsg = "✅ Department Deleted Successfully!";
    } else {
        $errorMsg = "❌ Error Deleting Department. Try again.";
    }
}

// Fetch all departments
$departments = $conn->query("SELECT * FROM departments ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Department Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="../css/Department.css">
<link rel="stylesheet" href="../Css/Admin.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<?php include 'side_menu.php'; ?>

<div class="container">
    <!-- Header -->
    <div class="header">
        <h1><i class="fas fa-building"></i> Department Management</h1>
    </div>

    <!-- Toast -->
    <div id="toastMessage" class="toast-message"></div>

    <!-- Add Department Form -->
    <div class="form-box">
        <h2><i class="fas fa-plus-circle"></i> Add New Department</h2>
        <form method="POST" class="add-form">
            <input type="text" name="dept_name" placeholder="Enter Department Name" required>
            <button type="submit" name="add" class="btn btn-primary"><i class="fas fa-plus"></i> Add Department</button>
        </form>
    </div>

    <!-- Department Table -->
    <div class="table-container">
        <div class="table-header">
            <h2><i class="fas fa-list"></i> All Departments</h2>
            <span class="dept-count"><?= $departments->num_rows ?> Departments</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($departments->num_rows > 0): ?>
                    <?php while($row = $departments->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $row['id'] ?></td>
                            <td>
                                <div class="dept-info"><?= htmlspecialchars($row['name']) ?></div>
                                <form method="POST" class="edit-mode" id="edit-form-<?= $row['id'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="text" name="dept_name" value="<?= htmlspecialchars($row['name']) ?>" class="edit-input" required>
                                    <div class="action-buttons">
                                        <button type="submit" name="update" class="action-btn update-btn"><i class="fas fa-check"></i> Save</button>
                                        <button type="button" class="action-btn cancel-btn" onclick="cancelEdit(<?= $row['id'] ?>)"><i class="fas fa-times"></i> Cancel</button>
                                    </div>
                                </form>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button type="button" class="action-btn edit-toggle-btn" onclick="toggleEdit(<?= $row['id'] ?>)"><i class="fas fa-edit"></i> Edit</button>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                        <button type="submit" name="delete" class="action-btn delete-btn" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i> Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="3"><div class="empty-state"><i class="fas fa-building"></i><p>No Departments Found</p></div></td></tr>
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

function showToast(message, type="success", duration=4000){
    const toast = document.getElementById('toastMessage');
    toast.innerHTML = `<i class="fas ${type==="success"?"fa-check-circle":"fa-exclamation-circle"}"></i>
    <div class="toast-content">${message}</div>
    <button class="toast-close" onclick="hideToast()"><i class="fas fa-times"></i></button>`;
    toast.className = `toast-message show ${type}`;
    setTimeout(hideToast,duration);
}
function hideToast(){const t=document.getElementById('toastMessage');t.classList.add('hide');setTimeout(()=>t.classList.remove('show','hide'),500);}

// Edit toggle
function toggleEdit(id){
    const info = document.querySelector(`#edit-form-${id}`).parentElement.querySelector('.dept-info');
    const form = document.getElementById(`edit-form-${id}`);
    info.style.display='none';
    form.style.display='grid';
}
function cancelEdit(id){
    const info = document.querySelector(`#edit-form-${id}`).parentElement.querySelector('.dept-info');
    const form = document.getElementById(`edit-form-${id}`);
    info.style.display='block';
    form.style.display='none';
}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.edit-mode').forEach(f=>f.style.display='none');});
</script>
</body>
</html>
