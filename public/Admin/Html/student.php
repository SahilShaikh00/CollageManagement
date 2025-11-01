<?php
session_start();
include '../../../Database/Connection.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user']) && $_SESSION['role'] != 'Admin' && $_SESSION['role'] != 'teacher') {
    header("Location: ../../Auth/Html/login.html");
    exit();
}

// Handle approval/rejection
if(isset($_POST['action']) && isset($_POST['id'])){
    $id = intval($_POST['id']);
    $action = $_POST['action'] === 'approve' ? 'approved' : 'rejected';

    $stmt = $conn->prepare("UPDATE student_profiles SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $action, $id);
    $stmt->execute();
    
    // Redirect to avoid form resubmission
    header("Location: ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']);
    exit();
}

// Fetch filter options
$departments = $conn->query("SELECT id, name FROM departments ORDER BY name");
$courses = $conn->query("SELECT id, courseName FROM courses ORDER BY courseName");
$divisions = $conn->query("SELECT id, division_name FROM divisions ORDER BY division_name");

// Pagination settings
$records_per_page_options = [10, 20, 40, 80, 100];
$records_per_page = isset($_GET['records_per_page']) ? intval($_GET['records_per_page']) : 10;
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Build query with filters
$whereConditions = ["sp.status = 'pending'"];
$params = [];
$types = "";

if(isset($_GET['department_id']) && !empty($_GET['department_id'])) {
    $whereConditions[] = "sp.department_id = ?";
    $params[] = $_GET['department_id'];
    $types .= "i";
}

if(isset($_GET['course_id']) && !empty($_GET['course_id'])) {
    $whereConditions[] = "sp.course_id = ?";
    $params[] = $_GET['course_id'];
    $types .= "i";
}

if(isset($_GET['division_id']) && !empty($_GET['division_id'])) {
    $whereConditions[] = "sp.division_id = ?";
    $params[] = $_GET['division_id'];
    $types .= "i";
}

if(isset($_GET['year']) && !empty($_GET['year'])) {
    $whereConditions[] = "sp.year = ?";
    $params[] = $_GET['year'];
    $types .= "s";
}

$whereClause = implode(" AND ", $whereConditions);

// Get total count for pagination
$countQuery = "
    SELECT COUNT(*) as total 
    FROM student_profiles sp
    JOIN departments d ON sp.department_id = d.id
    JOIN courses c ON sp.course_id = c.id
    JOIN divisions v ON sp.division_id = v.id
    WHERE $whereClause
";

if(!empty($params)) {
    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param($types, ...$params);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $total_records = $countResult->fetch_assoc()['total'];
} else {
    $countResult = $conn->query($countQuery);
    $total_records = $countResult->fetch_assoc()['total'];
}

$total_pages = ceil($total_records / $records_per_page);

// Ensure current page is within valid range
if ($current_page < 1) {
    $current_page = 1;
} elseif ($current_page > $total_pages && $total_pages > 0) {
    $current_page = $total_pages;
}

// Fetch filtered requests with pagination
$query = "
    SELECT sp.*, d.name AS dept_name, c.courseName, v.division_name 
    FROM student_profiles sp
    JOIN departments d ON sp.department_id = d.id
    JOIN courses c ON sp.course_id = c.id
    JOIN divisions v ON sp.division_id = v.id
    WHERE $whereClause
    ORDER BY sp.created_at DESC
    LIMIT ? OFFSET ?
";

// Add pagination parameters
$params[] = $records_per_page;
$params[] = $offset;
$types .= "ii";

if(!empty($params)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($query);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Requests</title>
    <link rel="stylesheet" href="../Css/Admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
            :root {
            --primary: #1a2a6c;
            --secondary: #b21f1f;
            --accent: #fdbb2d;
            --light: #f8f9fa;
            --dark: #222;
            --gray: #6c757d;
            --white: #ffffff;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
            --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --accent-gradient: linear-gradient(135deg, var(--accent) 0%, #ffd166 100%);
            --success: #28a745;
            --border-radius: 12px;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: "Poppins", sans-serif;
          background: #f5f7fa;
          color: var(--dark);
          line-height: 1.6;
          display: flex;
          min-height: 100vh;
        }

        .container {
          flex: 1;
          margin: 20px;
          margin-left: 260px;
          animation: fadeIn 0.6s ease-out;
        }

        /* Header */
        .header {
          background: var(--gradient);
          color: white;
          padding: 25px 30px;
          border-radius: var(--border-radius);
          margin-bottom: 30px;
          box-shadow: var(--card-shadow);
          position: relative;
          overflow: hidden;
        }

        .header::before {
          content: '';
          position: absolute;
          top: -50%;
          right: -10%;
          width: 300px;
          height: 300px;
          background: var(--accent-gradient);
          border-radius: 50%;
          opacity: 0.1;
        }

        .header h1 {
          font-size: 1.8rem;
          font-weight: 700;
          margin: 0;
          display: flex;
          align-items: center;
          gap: 12px;
          position: relative;
        }

        .header h1 i {
          font-size: 1.6rem;
          color: var(--accent);
        }

        /* Filter Form */
        .filter-form {
          background: white;
          padding: 25px;
          border-radius: var(--border-radius);
          margin-bottom: 25px;
          box-shadow: var(--card-shadow);
          border: 1px solid rgba(15, 26, 69, 0.05);
        }

        .filter-form h3 {
          color: var(--primary);
          margin-bottom: 20px;
          font-size: 1.2rem;
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .filter-form h3 i {
          color: var(--accent);
        }

        .filter-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 15px;
          align-items: end;
        }

        .form-group {
          display: flex;
          flex-direction: column;
        }

        .form-group label {
          font-weight: 600;
          margin-bottom: 8px;
          color: var(--primary);
          font-size: 0.9rem;
        }

        .form-control {
          padding: 12px 15px;
          border: 2px solid #e2e8f0;
          border-radius: 8px;
          font-size: 0.95rem;
          background: var(--light);
          transition: var(--transition);
          font-weight: 500;
        }

        .form-control:focus {
          outline: none;
          border-color: var(--primary);
          background: white;
          box-shadow: 0 0 0 3px rgba(26, 42, 108, 0.1);
        }

        .filter-actions {
          display: flex;
          gap: 10px;
          align-items: center;
        }

        /* Buttons */
        .btn {
          padding: 12px 20px;
          border: none;
          border-radius: 8px;
          font-weight: 600;
          font-size: 0.9rem;
          cursor: pointer;
          transition: var(--transition);
          display: inline-flex;
          align-items: center;
          gap: 8px;
          text-transform: uppercase;
          letter-spacing: 0.5px;
        }

        .btn-primary {
          background: var(--gradient);
          color: white;
          box-shadow: 0 4px 12px rgba(26, 42, 108, 0.3);
        }

        .btn-secondary {
          background: #6c757d;
          color: white;
          box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }

        .btn:hover {
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        /* Table Container */
        .table-container {
          background: white;
          border-radius: var(--border-radius);
          overflow: hidden;
          box-shadow: var(--card-shadow);
          margin-bottom: 30px;
        }

        .table-header {
          padding: 20px 25px;
          background: var(--gradient);
          color: white;
          display: flex;
          justify-content: space-between;
          align-items: center;
          flex-wrap: wrap;
          gap: 15px;
        }

        .table-header h2 {
          display: flex;
          align-items: center;
          gap: 10px;
          font-size: 1.4rem;
        }

        .table-header h2 i {
          color: var(--accent);
        }

        .results-count {
          background: rgba(255, 255, 255, 0.2);
          padding: 5px 12px;
          border-radius: 20px;
          font-size: 0.9rem;
          font-weight: 600;
        }

        .pagination-controls {
          display: flex;
          align-items: center;
          gap: 15px;
          flex-wrap: wrap;
        }

        .records-per-page {
          display: flex;
          align-items: center;
          gap: 10px;
        }

        .records-per-page select {
          padding: 8px 12px;
          border: 2px solid rgba(255, 255, 255, 0.3);
          border-radius: 6px;
          background: rgba(255, 255, 255, 0.2);
          color: white;
          font-weight: 600;
        }

        .records-per-page select option {
          background: var(--primary);
          color: white;
        }

        table {
          width: 100%;
          border-collapse: collapse;
        }

        th {
          padding: 18px 24px;
          text-align: left;
          color: var(--primary);
          font-weight: 600;
          font-size: 0.9rem;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          border-bottom: 2px solid #e2e8f0;
        }

        td {
          padding: 20px 24px;
          vertical-align: middle;
          font-weight: 500;
        }

        tbody tr {
          border-bottom: 1px solid #f1f5f9;
          transition: var(--transition);
        }

        tbody tr:hover {
          background: #f8fafc;
        }

        /* Action Buttons */
        .action-buttons {
          display: flex;
          gap: 8px;
        }

        .approve-btn {
          background: linear-gradient(135deg, #28a745, #34d058);
          color: white;
          box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .reject-btn {
          background: linear-gradient(135deg, var(--secondary) 0%, #c53030 100%);
          color: white;
          box-shadow: 0 2px 8px rgba(178, 31, 31, 0.3);
        }

        .action-btn {
          padding: 8px 16px;
          border: none;
          border-radius: 6px;
          font-weight: 600;
          font-size: 0.8rem;
          cursor: pointer;
          transition: var(--transition);
          display: inline-flex;
          align-items: center;
          gap: 5px;
        }

        .action-btn:hover {
          transform: translateY(-1px);
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Pagination */
        .pagination {
          display: flex;
          justify-content: center;
          align-items: center;
          padding: 25px;
          gap: 10px;
          flex-wrap: wrap;
        }

        .page-btn {
          padding: 10px 16px;
          border: 2px solid #e2e8f0;
          background: white;
          color: var(--primary);
          border-radius: 8px;
          font-weight: 600;
          cursor: pointer;
          transition: var(--transition);
          display: flex;
          align-items: center;
          gap: 5px;
        }

        .page-btn:hover {
          background: var(--primary);
          color: white;
          border-color: var(--primary);
        }

        .page-btn.active {
          background: var(--gradient);
          color: white;
          border-color: var(--primary);
        }

        .page-btn.disabled {
          opacity: 0.5;
          cursor: not-allowed;
        }

        .page-btn.disabled:hover {
          background: white;
          color: var(--primary);
          border-color: #e2e8f0;
        }

        .page-info {
          font-weight: 600;
          color: var(--primary);
          margin: 0 15px;
        }

        /* Empty State */
        .empty-state {
          text-align: center;
          padding: 60px 20px;
          color: var(--gray);
        }

        .empty-state i {
          font-size: 50px;
          margin-bottom: 15px;
        }

        /* Animations */
        @keyframes fadeIn {
          from {
            opacity: 0;
          }
          to {
            opacity: 1;
          }
        }

        @keyframes slideInRight {
          from {
            opacity: 0;
            transform: translateX(50px);
          }
          to {
            opacity: 1;
            transform: translateX(0);
          }
        }

        @keyframes slideOutRight {
          from {
            opacity: 1;
            transform: translateX(0);
          }
          to {
            opacity: 0;
            transform: translateX(50px);
          }
        }
    </style>
</head>
<body>
    <?php include 'side_menu.php'; ?>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-user-graduate"></i> Student Onboarding Requests</h1>
        </div>

        <!-- Filter Form -->
        <div class="filter-form">
            <h3><i class="fas fa-filter"></i> Filter Requests</h3>
            <form method="GET" id="filterForm">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="records_per_page" value="<?= $records_per_page ?>">
                
                <div class="filter-grid">
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select class="form-control" id="department_id" name="department_id">
                            <option value="">All Departments</option>
                            <?php 
                            $departments->data_seek(0); // Reset pointer
                            while($dept = $departments->fetch_assoc()): ?>
                                <option value="<?= $dept['id'] ?>" <?= (isset($_GET['department_id']) && $_GET['department_id'] == $dept['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept['name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="course_id">Course</label>
                        <select class="form-control" id="course_id" name="course_id">
                            <option value="">All Courses</option>
                            <?php 
                            $courses->data_seek(0); // Reset pointer
                            while($course = $courses->fetch_assoc()): ?>
                                <option value="<?= $course['id'] ?>" <?= (isset($_GET['course_id']) && $_GET['course_id'] == $course['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($course['courseName']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="division_id">Division</label>
                        <select class="form-control" id="division_id" name="division_id">
                            <option value="">All Divisions</option>
                            <?php 
                            $divisions->data_seek(0); // Reset pointer
                            while($division = $divisions->fetch_assoc()): ?>
                                <option value="<?= $division['id'] ?>" <?= (isset($_GET['division_id']) && $_GET['division_id'] == $division['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($division['division_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="year">Year</label>
                        <select class="form-control" id="year" name="year">
                            <option value="">All Years</option>
                            <option value="1" <?= (isset($_GET['year']) && $_GET['year'] == '1') ? 'selected' : '' ?>>First Year</option>
                            <option value="2" <?= (isset($_GET['year']) && $_GET['year'] == '2') ? 'selected' : '' ?>>Second Year</option>
                            <option value="3" <?= (isset($_GET['year']) && $_GET['year'] == '3') ? 'selected' : '' ?>>Third Year</option>
                            <option value="4" <?= (isset($_GET['year']) && $_GET['year'] == '4') ? 'selected' : '' ?>>Fourth Year</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filters
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-container">
                <div class="table-header">
                    <h2><i class="fas fa-clock"></i> Pending Approval Requests</h2>
                    <div class="pagination-controls">
                        <div class="results-count">
                            Showing <?= min($records_per_page, $result->num_rows) ?> of <?= $total_records ?> Request(s)
                        </div>
                        <div class="records-per-page">
                            <span>Show:</span>
                            <select onchange="updateRecordsPerPage(this.value)">
                                <?php foreach($records_per_page_options as $option): ?>
                                    <option value="<?= $option ?>" <?= $records_per_page == $option ? 'selected' : '' ?>>
                                        <?= $option ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student Name</th>
                            <th>Gender</th>
                            <th>Department</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Division</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><?= htmlspecialchars($row['first_name']." ".$row['last_name']) ?></td>
                                <td><?= htmlspecialchars($row['gender']) ?></td>
                                <td><?= htmlspecialchars($row['dept_name']) ?></td>
                                <td><?= htmlspecialchars($row['courseName']) ?></td>
                                <td><?= htmlspecialchars($row['year']) ?></td>
                                <td><?= htmlspecialchars($row['division_name']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="action" value="approve" class="action-btn approve-btn" onclick="return confirm('Are you sure you want to approve this student?')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                            <button type="submit" name="action" value="reject" class="action-btn reject-btn" onclick="return confirm('Are you sure you want to reject this student?')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <!-- First Page -->
                    <button class="page-btn <?= $current_page == 1 ? 'disabled' : '' ?>" 
                            onclick="changePage(1)" <?= $current_page == 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-angle-double-left"></i> First
                    </button>
                    
                    <!-- Previous Page -->
                    <button class="page-btn <?= $current_page == 1 ? 'disabled' : '' ?>" 
                            onclick="changePage(<?= $current_page - 1 ?>)" <?= $current_page == 1 ? 'disabled' : '' ?>>
                        <i class="fas fa-angle-left"></i> Prev
                    </button>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $current_page - 2);
                    $end_page = min($total_pages, $current_page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): 
                    ?>
                        <button class="page-btn <?= $i == $current_page ? 'active' : '' ?>" 
                                onclick="changePage(<?= $i ?>)">
                            <?= $i ?>
                        </button>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <button class="page-btn <?= $current_page == $total_pages ? 'disabled' : '' ?>" 
                            onclick="changePage(<?= $current_page + 1 ?>)" <?= $current_page == $total_pages ? 'disabled' : '' ?>>
                        Next <i class="fas fa-angle-right"></i>
                    </button>
                    
                    <!-- Last Page -->
                    <button class="page-btn <?= $current_page == $total_pages ? 'disabled' : '' ?>" 
                            onclick="changePage(<?= $total_pages ?>)" <?= $current_page == $total_pages ? 'disabled' : '' ?>>
                        Last <i class="fas fa-angle-double-right"></i>
                    </button>
                    
                    <!-- Page Info -->
                    <span class="page-info">
                        Page <?= $current_page ?> of <?= $total_pages ?>
                    </span>
                </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-container">
                <div class="table-header">
                    <h2><i class="fas fa-clock"></i> Pending Approval Requests</h2>
                    <div class="results-count">0 Requests</div>
                </div>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h3>No Pending Student Requests</h3>
                    <p>No student onboarding requests match your current filters.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function resetFilters() {
            document.getElementById('filterForm').reset();
            // Reset hidden fields too
            document.querySelector('input[name="page"]').value = 1;
            document.querySelector('input[name="records_per_page"]').value = 10;
            window.location.href = window.location.pathname;
        }
        
        function updateRecordsPerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('records_per_page', value);
            url.searchParams.set('page', 1); // Reset to first page
            window.location.href = url.toString();
        }
        
        function changePage(page) {
            document.querySelector('input[name="page"]').value = page;
            document.getElementById('filterForm').submit();
        }
        
        // Update hidden fields when form controls change
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('filterForm');
            const formControls = form.querySelectorAll('select, input');
            
            formControls.forEach(control => {
                control.addEventListener('change', function() {
                    document.querySelector('input[name="page"]').value = 1;
                });
            });
        });
    </script>
</body>
</html>