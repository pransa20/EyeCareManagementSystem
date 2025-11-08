<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];

// Handle department operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $sql = "INSERT INTO departments (name, description) VALUES (?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ss', $name, $description);
                $stmt->execute();
                break;
                
            case 'edit':
                $id = (int)$_POST['id'];
                $name = trim($_POST['name']);
                $description = trim($_POST['description']);
                $sql = "UPDATE departments SET name = ?, description = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('ssi', $name, $description, $id);
                $stmt->execute();
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                $sql = "DELETE FROM departments WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('i', $id);
                $stmt->execute();
                break;
        }
        header('Location: departments.php');
        exit;
    }
}

// Get all departments
$departments = [];
$sql = "SELECT * FROM departments ORDER BY name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $departments[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
<link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <?php include 'headadmin.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Manage Departments</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        <i class="fas fa-plus me-2"></i>Add Department
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($departments as $dept): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($dept['name']); ?></td>
                                <td><?php echo htmlspecialchars($dept['description']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary me-2" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editDepartmentModal"
                                            data-id="<?php echo $dept['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($dept['name']); ?>"
                                            data-description="<?php echo htmlspecialchars($dept['description']); ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#deleteDepartmentModal"
                                            data-id="<?php echo $dept['id']; ?>"
                                            data-name="<?php echo htmlspecialchars($dept['name']); ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Department Modal -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="departments.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Department Modal -->
    <div class="modal fade" id="editDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="departments.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" id="edit-dept-id">
                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" class="form-control" name="name" id="edit-dept-name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="edit-dept-description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Department Modal -->
    <div class="modal fade" id="deleteDepartmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="departments.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-dept-id">
                        <p>Are you sure you want to delete the department "<span id="delete-dept-name"></span>"?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.5-dist/js/bootstrap.bundle.min.js"></script>
     <script>
        // Handle edit department modal
        document.querySelectorAll('[data-bs-target="#editDepartmentModal"]').forEach(button => {
            button.addEventListener('click', event => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                const description = button.getAttribute('data-description');
                
                document.getElementById('edit-dept-id').value = id;
                document.getElementById('edit-dept-name').value = name;
                document.getElementById('edit-dept-description').value = description;
            });
        });

        // Handle delete department modal
        document.querySelectorAll('[data-bs-target="#deleteDepartmentModal"]').forEach(button => {
            button.addEventListener('click', event => {
                const id = button.getAttribute('data-id');
                const name = button.getAttribute('data-name');
                
                document.getElementById('delete-dept-id').value = id;
                document.getElementById('delete-dept-name').textContent = name;
            });
        });
    </script>
</body>
</html>