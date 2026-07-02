<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db.php';

$msg = '';

// ── ADD Employee ──────────────────────────────────────────────────────────────
if (isset($_POST['add_employee'])) {
    $name    = $conn->real_escape_string(trim($_POST['employee_name']));
    $email   = $conn->real_escape_string(trim($_POST['email']));
    $phone   = $conn->real_escape_string(trim($_POST['phone']));
    $dept    = $conn->real_escape_string(trim($_POST['department']));
    $joining = $conn->real_escape_string($_POST['joining_date']);
    $status  = $_POST['status'] === 'Active' ? 'Active' : 'Inactive';

    $sql = "INSERT INTO employees (employee_name, email, phone, department, joining_date, status)
            VALUES ('$name','$email','$phone','$dept','$joining','$status')";
    if ($conn->query($sql)) {
        $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Employee added successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger auto-dismiss"><i class="bi bi-x-circle-fill me-2"></i>Error: ' . htmlspecialchars($conn->error) . '</div>';
    }
}

// ── UPDATE Employee ───────────────────────────────────────────────────────────
if (isset($_POST['update_employee'])) {
    $id      = (int)$_POST['edit_id'];
    $name    = $conn->real_escape_string(trim($_POST['employee_name']));
    $email   = $conn->real_escape_string(trim($_POST['email']));
    $phone   = $conn->real_escape_string(trim($_POST['phone']));
    $dept    = $conn->real_escape_string(trim($_POST['department']));
    $joining = $conn->real_escape_string($_POST['joining_date']);
    $status  = $_POST['status'] === 'Active' ? 'Active' : 'Inactive';

    $sql = "UPDATE employees SET employee_name='$name', email='$email', phone='$phone',
            department='$dept', joining_date='$joining', status='$status' WHERE id=$id";
    if ($conn->query($sql)) {
        $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Employee updated successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger auto-dismiss">Error: ' . htmlspecialchars($conn->error) . '</div>';
    }
}

// ── DELETE Employee ───────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($conn->query("DELETE FROM employees WHERE id=$id")) {
        $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Employee deleted.</div>';
    }
}

// ── Search + Fetch ────────────────────────────────────────────────────────────
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$where  = $search ? "WHERE employee_name LIKE '%$search%' OR email LIKE '%$search%' OR department LIKE '%$search%'" : '';
$employees = $conn->query("SELECT * FROM employees $where ORDER BY id DESC");

$departments = ['Engineering','HR','Finance','Marketing','Operations','Sales','IT','Admin'];

include 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-people-fill me-2 text-primary"></i>Employees</h4>
        <p>Manage your organisation's employee records.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#empModal"
        onclick="openAddModal()">
        <i class="bi bi-plus-lg me-1"></i>Add Employee
    </button>
</div>

<?= $msg ?>

<!-- Search Bar -->
<div class="card mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET" action="">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="search-bar">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by name, email or department…"
                            value="<?= htmlspecialchars($search) ?>">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Search</button>
                    <?php if ($search): ?>
                        <a href="employees.php" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Employee Table -->
<div class="card">
    <div class="card-header">
        <span><i class="bi bi-table me-2"></i>Employee List</span>
        <span class="text-muted small"><?= $employees->num_rows ?> record(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0" id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Department</th>
                    <th>Joining Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $employees->fetch_assoc()):
            ?>
                <tr>
                    <td class="text-muted"><?= $i++ ?></td>
                    <td class="fw-700"><?= htmlspecialchars($row['employee_name']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td><span class="dept-badge"><?= htmlspecialchars($row['department']) ?></span></td>
                    <td><?= date('d M Y', strtotime($row['joining_date'])) ?></td>
                    <td>
                        <span class="badge-status badge-<?= strtolower($row['status']) ?>">
                            <?= $row['status'] ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1"
                            onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <a href="?delete=<?= $row['id'] ?>"
                           class="btn btn-sm btn-outline-danger btn-delete">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($employees->num_rows === 0): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        No employees found.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add / Edit Modal -->
<div class="modal fade" id="empModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="bi bi-person-plus me-2 text-primary"></i>Add Employee
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="edit_id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Employee Name *</label>
                            <input type="text" name="employee_name" id="f_name"
                                class="form-control" required placeholder="Full name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" id="f_email"
                                class="form-control" required placeholder="email@company.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" id="f_phone"
                                class="form-control" placeholder="10-digit number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Department *</label>
                            <select name="department" id="f_dept" class="form-select" required>
                                <option value="">Select department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Joining Date *</label>
                            <input type="date" name="joining_date" id="f_joining"
                                class="form-control" required max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="f_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_employee" id="formSubmitBtn" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Add Employee
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalTitle').innerHTML =
        '<i class="bi bi-person-plus me-2 text-primary"></i>Add Employee';
    document.getElementById('formSubmitBtn').name = 'add_employee';
    document.getElementById('formSubmitBtn').innerHTML =
        '<i class="bi bi-plus-lg me-1"></i>Add Employee';
    ['f_name','f_email','f_phone','f_joining'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('f_dept').value   = '';
    document.getElementById('f_status').value = 'Active';
}

function openEditModal(data) {
    document.getElementById('modalTitle').innerHTML =
        '<i class="bi bi-pencil me-2 text-primary"></i>Edit Employee';
    document.getElementById('formSubmitBtn').name = 'update_employee';
    document.getElementById('formSubmitBtn').innerHTML =
        '<i class="bi bi-save me-1"></i>Save Changes';
    document.getElementById('edit_id').value   = data.id;
    document.getElementById('f_name').value    = data.employee_name;
    document.getElementById('f_email').value   = data.email;
    document.getElementById('f_phone').value   = data.phone;
    document.getElementById('f_dept').value    = data.department;
    document.getElementById('f_joining').value = data.joining_date;
    document.getElementById('f_status').value  = data.status;
    new bootstrap.Modal(document.getElementById('empModal')).show();
}
</script>

<?php include 'includes/footer.php'; ?>