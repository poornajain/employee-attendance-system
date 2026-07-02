<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'includes/db.php';

$msg = '';

// ── ADD Attendance ────────────────────────────────────────────────────────────
if (isset($_POST['add_attendance'])) {
    $emp_id = (int)$_POST['employee_id'];
    $date   = $conn->real_escape_string($_POST['attendance_date']);
    $status = $conn->real_escape_string($_POST['attendance_status']);
    $in     = !empty($_POST['check_in'])  ? "'" . $conn->real_escape_string($_POST['check_in'])  . "'" : 'NULL';
    $out    = !empty($_POST['check_out']) ? "'" . $conn->real_escape_string($_POST['check_out']) . "'" : 'NULL';

    $sql = "INSERT INTO attendance (employee_id, attendance_date, check_in, check_out, attendance_status)
            VALUES ($emp_id, '$date', $in, $out, '$status')
            ON DUPLICATE KEY UPDATE check_in=$in, check_out=$out, attendance_status='$status'";
    if ($conn->query($sql)) {
        $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Attendance saved successfully.</div>';
    } else {
        $msg = '<div class="alert alert-danger auto-dismiss">Error: ' . htmlspecialchars($conn->error) . '</div>';
    }
}

// ── UPDATE Attendance ─────────────────────────────────────────────────────────
if (isset($_POST['update_attendance'])) {
    $id     = (int)$_POST['edit_id'];
    $status = $conn->real_escape_string($_POST['attendance_status']);
    $in     = !empty($_POST['check_in'])  ? "'" . $conn->real_escape_string($_POST['check_in'])  . "'" : 'NULL';
    $out    = !empty($_POST['check_out']) ? "'" . $conn->real_escape_string($_POST['check_out']) . "'" : 'NULL';
    if ($conn->query("UPDATE attendance SET check_in=$in, check_out=$out, attendance_status='$status' WHERE id=$id")) {
        $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Record updated.</div>';
    }
}

// ── DELETE ────────────────────────────────────────────────────────────────────
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM attendance WHERE id=$id");
    $msg = '<div class="alert alert-success auto-dismiss"><i class="bi bi-check-circle-fill me-2"></i>Record deleted.</div>';
}

// ── Filters ───────────────────────────────────────────────────────────────────
$filter_emp    = isset($_GET['filter_emp'])    ? (int)$_GET['filter_emp']                          : 0;
$filter_date   = isset($_GET['filter_date'])   ? $conn->real_escape_string($_GET['filter_date'])   : '';
$filter_status = isset($_GET['filter_status']) ? $conn->real_escape_string($_GET['filter_status']) : '';

$where = "WHERE 1=1";
if ($filter_emp)    $where .= " AND a.employee_id=$filter_emp";
if ($filter_date)   $where .= " AND a.attendance_date='$filter_date'";
if ($filter_status) $where .= " AND a.attendance_status='$filter_status'";

// JOIN query
$records = $conn->query("
    SELECT a.*, e.employee_name, e.department
    FROM attendance a
    JOIN employees e ON a.employee_id = e.id
    $where
    ORDER BY a.attendance_date DESC, e.employee_name ASC
");

// Employees for dropdowns
$employees = $conn->query("SELECT id, employee_name, department FROM employees WHERE status='Active' ORDER BY employee_name");

include 'includes/header.php';
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
    <div>
        <h4><i class="bi bi-calendar-check-fill me-2 text-primary"></i>Attendance</h4>
        <p>Track and manage daily check-in / check-out records.</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#attModal">
        <i class="bi bi-plus-lg me-1"></i>Mark Attendance
    </button>
</div>

<?= $msg ?>

<!-- Filter Bar -->
<div class="card mb-3">
    <div class="card-body py-2 px-3">
        <form method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <select name="filter_emp" class="form-select form-select-sm">
                        <option value="">All Employees</option>
                        <?php
                        $emp_list = $conn->query("SELECT id, employee_name FROM employees ORDER BY employee_name");
                        while ($e = $emp_list->fetch_assoc()):
                        ?>
                            <option value="<?= $e['id'] ?>" <?= $filter_emp == $e['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['employee_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="filter_date" class="form-control form-control-sm"
                        value="<?= htmlspecialchars($filter_date) ?>" max="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2">
                    <select name="filter_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Present"  <?= $filter_status==='Present'  ? 'selected':'' ?>>Present</option>
                        <option value="Absent"   <?= $filter_status==='Absent'   ? 'selected':'' ?>>Absent</option>
                        <option value="Half Day" <?= $filter_status==='Half Day' ? 'selected':'' ?>>Half Day</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                    <a href="attendance.php" class="btn btn-sm btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Attendance Table -->
<div class="card">
    <div class="card-header">
        <span><i class="bi bi-table me-2"></i>Attendance Records</span>
        <span class="text-muted small"><?= $records->num_rows ?> record(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0" id="dataTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Date</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Hours</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $i = 1;
            while ($row = $records->fetch_assoc()):
                $hours = '';
                if ($row['check_in'] && $row['check_out']) {
                    $diff = strtotime($row['check_out']) - strtotime($row['check_in']);
                    $h = floor($diff / 3600);
                    $m = floor(($diff % 3600) / 60);
                    $hours = "{$h}h {$m}m";
                }
                $s    = $row['attendance_status'];
                $cls  = $s === 'Present' ? 'present' : ($s === 'Absent' ? 'absent' : 'halfday');
                $icon = $s === 'Present' ? 'bi-check-circle-fill' : ($s === 'Absent' ? 'bi-x-circle-fill' : 'bi-dash-circle-fill');
            ?>
                <tr>
                    <td class="text-muted"><?= $i++ ?></td>
                    <td class="fw-700"><?= htmlspecialchars($row['employee_name']) ?></td>
                    <td><span class="dept-badge"><?= htmlspecialchars($row['department']) ?></span></td>
                    <td><?= date('d M Y', strtotime($row['attendance_date'])) ?></td>
                    <td><?= $row['check_in']  ? date('h:i A', strtotime($row['check_in']))  : '<span class="text-muted">—</span>' ?></td>
                    <td><?= $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= $hours ?: '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <span class="badge-status badge-<?= $cls ?>">
                            <i class="bi <?= $icon ?>"></i> <?= $s ?>
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1"
                            onclick="openEditAtt(<?= htmlspecialchars(json_encode($row)) ?>)">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                        <a href="?delete=<?= $row['id'] ?>"
                           class="btn btn-sm btn-outline-danger btn-delete">
                            <i class="bi bi-trash-fill"></i>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($records->num_rows === 0): ?>
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        No records found. Click <strong>Mark Attendance</strong> to add one.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mark / Edit Attendance Modal -->
<div class="modal fade" id="attModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attModalTitle">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>Mark Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="edit_id" id="att_edit_id">
                    <div class="row g-3">
                        <div class="col-12" id="emp_select_wrap">
                            <label class="form-label">Employee *</label>
                            <select name="employee_id" id="att_emp" class="form-select" required>
                                <option value="">Select employee</option>
                                <?php
                                $employees->data_seek(0);
                                while ($e = $employees->fetch_assoc()):
                                ?>
                                    <option value="<?= $e['id'] ?>">
                                        <?= htmlspecialchars($e['employee_name']) ?> — <?= $e['department'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-12" id="emp_name_wrap" style="display:none;">
                            <label class="form-label">Employee</label>
                            <input type="text" id="att_emp_name" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date *</label>
                            <input type="date" name="attendance_date" id="att_date"
                                class="form-control" required
                                value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status *</label>
                            <select name="attendance_status" id="attendance_status" class="form-select" required>
                                <option value="Present">Present</option>
                                <option value="Absent">Absent</option>
                                <option value="Half Day">Half Day</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check In</label>
                            <input type="time" name="check_in" id="check_in" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Check Out</label>
                            <input type="time" name="check_out" id="check_out" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_attendance" id="attSubmitBtn" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditAtt(data) {
    document.getElementById('attModalTitle').innerHTML =
        '<i class="bi bi-pencil me-2 text-primary"></i>Edit Attendance';
    document.getElementById('attSubmitBtn').name = 'update_attendance';
    document.getElementById('att_edit_id').value  = data.id;
    document.getElementById('att_date').value      = data.attendance_date;
    document.getElementById('attendance_status').value = data.attendance_status;
    document.getElementById('check_in').value  = data.check_in  || '';
    document.getElementById('check_out').value = data.check_out || '';
    document.getElementById('check_in').setAttribute('data-value', '1');
    // Show name, hide dropdown
    document.getElementById('emp_select_wrap').style.display = 'none';
    document.getElementById('emp_name_wrap').style.display   = '';
    document.getElementById('att_emp_name').value = data.employee_name;
    document.getElementById('att_emp').removeAttribute('required');
    new bootstrap.Modal(document.getElementById('attModal')).show();
}

document.getElementById('attModal').addEventListener('hidden.bs.modal', function () {
    document.getElementById('attModalTitle').innerHTML =
        '<i class="bi bi-calendar-plus me-2 text-primary"></i>Mark Attendance';
    document.getElementById('attSubmitBtn').name = 'add_attendance';
    document.getElementById('emp_select_wrap').style.display = '';
    document.getElementById('emp_name_wrap').style.display   = 'none';
    document.getElementById('att_emp').setAttribute('required', '');
    document.getElementById('att_edit_id').value = '';
    document.getElementById('check_in').removeAttribute('data-value');
});
</script>

<?php include 'includes/footer.php'; ?>