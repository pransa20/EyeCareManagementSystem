<?php
session_start();
require_once __DIR__ . '/../includes/Auth.php';

$auth = new Auth();
if (!$auth->isLoggedIn() || !$auth->hasRole('admin')) {
    header('Location: /login.php');
    exit;
}

$conn = $GLOBALS['conn'];

// Get all website content sections
$sections = [];
$sql = "SELECT * FROM website_content ORDER BY section_name";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

// Handle content update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_content'])) {
    $section_name = $_POST['section_name'];
    $content = $_POST['content'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Check if section exists
    $sql = "SELECT id FROM website_content WHERE section_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $section_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing section
        $sql = "UPDATE website_content SET content = ?, is_active = ?, updated_at = NOW() WHERE section_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sis', $content, $is_active, $section_name);
    } else {
        // Create new section
        $sql = "INSERT INTO website_content (section_name, content, is_active) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $section_name, $content, $is_active);
    }
    
    if ($stmt->execute()) {
        $success_message = 'Content updated successfully!';
        // Refresh sections list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = 'Failed to update content. Please try again.';
    }
}

// Handle section deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_section'])) {
    $section_name = $_POST['section_name'];
    
    $sql = "DELETE FROM website_content WHERE section_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $section_name);
    
    if ($stmt->execute()) {
        $success_message = 'Section deleted successfully!';
        // Refresh sections list
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = 'Failed to delete section. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Management - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
    <link href="../assets/css/style.css" rel="stylesheet">
    <link href="..w/assets/css/admin.css" rel="stylesheet">
    <!-- Include TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            selector: '#content-editor',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; }'
        });
    </script>
    <style>
         .navbar {
            transition: all 0.3s ease;
            padding: 0.5rem 0;
            background-color: white !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary-color);
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
            margin-left: -100px;
            
            transition: transform 0.3s ease;
        }

        .nav-link {
            color: #333 !important;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color) !important;
        }

        /* Cart Badge */
        .cart-badge {
            position: relative;
        }
        </style>
</head>
<body>

            
            <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar" style="background: var(--sidebar-dark); box-shadow: var(--shadow-sm); border-right: 1px solid var(--card-border);">
                <div class="text-center mb-4 py-4">
                    <img src="logo.png" alt="Logo" height="50" class="mb-3">
                    <h5 class="fw-bold mb-0" style="color: var(--text-primary);">Trinetra Eye Care</h5>
                    <p class="text-muted small mb-0">Admin Dashboard</p>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="doctors.php">
                            <i class="fas fa-user-md"></i>Doctors
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">
                            <i class="fas fa-users"></i>Patients
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="appointments.php">
                            <i class="fas fa-calendar-check"></i>Appointments
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical-history.php">
                            <i class="fas fa-notes-medical"></i>Medical History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-glasses"></i>Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            <i class="fas fa-shopping-cart"></i>Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog"></i>Settings
                        </a>
                    </li>
                    <li class="nav-item mt-4">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i>Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <!-- Dashboard Header -->
                <div class="dashboard-header rounded-xl p-4 mb-4" style="background: linear-gradient(135deg, var(--primary-color) 0%, #2e59d9 100%); box-shadow: var(--shadow-lg);">
                    <div class="position-absolute top-0 end-0 mt-2 me-2">
                        <div class="d-flex align-items-center bg-white bg-opacity-10 rounded-pill px-3 py-2">
                            <i class="fas fa-calendar-alt text-white me-2"></i>
                            <span class="text-white"><?php echo date('F j, Y'); ?></span>
                        </div>
                       
                    </div>

            <div class="col-md-9">
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success"><?php echo $success_message; ?></div>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold">Website Content Management</h6>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSectionModal">
                            <i class="fas fa-plus me-2"></i>Add New Section
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Section Name</th>
                                        <th>Content Preview</th>
                                        <th>Status</th>
                                        <th>Last Updated</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sections as $section): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($section['section_name']); ?></td>
                                        <td><?php echo substr(strip_tags($section['content']), 0, 100) . '...'; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $section['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $section['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d M Y H:i', strtotime($section['updated_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-primary edit-section" 
                                                    data-section="<?php echo htmlspecialchars(json_encode($section)); ?>" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editSectionModal">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-section"
                                                    data-section-name="<?php echo htmlspecialchars($section['section_name']); ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSectionModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Section Modal -->
    <div class="modal fade" id="addSectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Section Name</label>
                            <input type="text" class="form-control" name="section_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea id="content-editor" name="content" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="is_active" checked>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                        <button type="submit" name="update_content" class="btn btn-primary">Add Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Section Modal -->
    <div class="modal fade" id="editSectionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="section_name" id="edit_section_name">
                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea id="edit-content-editor" name="content" class="form-control"></textarea>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active">
                            <label class="form-check-label" for="edit_is_active">Active</label>
                        </div>
                        <button type="submit" name="update_content" class="btn btn-primary">Update Section</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Section Modal -->
    <div class="modal fade" id="deleteSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Section</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this section? This action cannot be undone.</p>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="section_name" id="delete_section_name">
                        <button type="submit" name="delete_section" class="btn btn-danger">Delete Section</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle edit section modal
        document.querySelectorAll('.edit-section').forEach(button => {
            button.addEventListener('click', function() {
                const section = JSON.parse(this.dataset.section);
                document.getElementById('edit_section_name').value = section.section_name;
                tinymce.get('edit-content-editor').setContent(section.content);
                document.getElementById('edit_is_active').checked = section.is_active == 1;
            });
        });

        // Handle delete section modal
        document.querySelectorAll('.delete-section').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('delete_section_name').value = this.dataset.sectionName;
            });
        });

        // Initialize TinyMCE for edit modal
        tinymce.init({
            selector: '#edit-content-editor',
            height: 500,
            plugins: [
                'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                'insertdatetime', 'media', 'table', 'help', 'wordcount'
            ],
            toolbar: 'undo redo | blocks | ' +
                'bold italic backcolor | alignleft aligncenter ' +
                'alignright alignjustify | bullist numlist outdent indent | ' +
                'removeformat | help',
            content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 16px; }'
        });
    </script>
</body>
</html>