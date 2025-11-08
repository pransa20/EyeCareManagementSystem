<?php
require_once '../config/database.php';
require_once '../includes/Auth.php';

// Initialize the session
session_start();

// Check if the user is logged in and is an admin, if not redirect to login page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

require_once '../includes/Settings.php';

$settings = new Settings();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = '';
    $messageType = '';
    
    if (isset($_POST['save_payment'])) {
        $api_key = trim($_POST['api_key']);
        $merchant_id = trim($_POST['merchant_id']);
        
        if (empty($api_key) || empty($merchant_id)) {
            $message = 'API Key and Merchant ID are required';
            $messageType = 'danger';
        } else {
            // Validate API key format (you can customize this based on your payment gateway requirements)
            if (!preg_match('/^[A-Za-z0-9_-]{20,}$/', $api_key)) {
                $message = 'Invalid API Key format';
                $messageType = 'danger';
            } else {
                if ($settings->setPaymentConfig($api_key, $merchant_id)) {
                    $message = 'Payment settings updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Failed to update payment settings';
                    $messageType = 'danger';
                }
            }
        }
    } elseif (isset($_POST['save_general'])) {
        // Process general settings update
        $message = 'General settings updated successfully!';
        $messageType = 'success';
    } elseif (isset($_POST['save_email'])) {
        // Process email settings update
        $message = 'Email settings updated successfully!';
        $messageType = 'success';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Trinetra Eye Care</title>
    <link href="../bootstrap-5.3.5-dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../fontawesome/css/all.min.css">
     <link href="../assets/css/style.css" rel="stylesheet">
    <link href="../assets/css/admin-nav.css" rel="stylesheet">
</head>
<body>
<?php include '../admin/headadmin.php'; ?>

    <div class="container-fluid py-5 mt-5">
        <div class="row">
            <div class="col-md-3">
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Admin Menu</h5>
                        <div class="list-group">
                            <a href="/admin/dashboard-new.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                            </a>
                            <a href="/admin/users.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-users me-2"></i> Users Management
                            </a>
                            <a href="/admin/doctors.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-md me-2"></i> Doctors
                            </a>
                            <a href="/admin/patients.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-user-injured me-2"></i> Patients
                            </a>
                            <a href="/admin/appointments.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-calendar-check me-2"></i> Appointments
                            </a>
                            <a href="/admin/products.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-glasses me-2"></i> Products
                            </a>
                            <a href="/admin/orders.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart me-2"></i> Orders
                            </a>
                            <a href="/admin/content.php" class="list-group-item list-group-item-action">
                                <i class="fas fa-edit me-2"></i> Content Management
                            </a>
                            <a href="/admin/settings.php" class="list-group-item list-group-item-action active">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-9">
                <?php if (isset($message) && !empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">System Settings</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="siteName" class="form-label">Site Name</label>
                                <input type="text" class="form-control" id="siteName" name="siteName" value="Trinetra Eye Care">
                            </div>
                            <div class="mb-3">
                                <label for="siteEmail" class="form-label">Site Email</label>
                                <input type="email" class="form-control" id="siteEmail" name="siteEmail" value="contact@trinetraeyecare.com">
                            </div>
                            <div class="mb-3">
                                <label for="sitePhone" class="form-label">Contact Phone</label>
                                <input type="tel" class="form-control" id="sitePhone" name="sitePhone" value="+91 1234567890">
                            </div>
                            <div class="mb-3">
                                <label for="siteAddress" class="form-label">Address</label>
                                <textarea class="form-control" id="siteAddress" name="siteAddress" rows="3">123 Eye Care Street, Medical District, City - 123456</textarea>
                            </div>
                            <button type="submit" name="save_general" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Payment Gateway Settings</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="api_key" class="form-label">API Key</label>
                                <input type="password" class="form-control" id="api_key" name="api_key" value="<?php echo htmlspecialchars($settings->get('payment_gateway_api_key')); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="merchant_id" class="form-label">Merchant ID</label>
                                <input type="password" class="form-control" id="merchant_id" name="merchant_id" value="<?php echo htmlspecialchars($settings->get('payment_gateway_merchant_id')); ?>">
                            </div>
                            <button type="submit" name="save_payment" class="btn btn-primary">Save Payment Settings</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold">Email Settings</h6>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="smtpHost" class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" id="smtpHost" name="smtpHost">
                            </div>
                            <div class="mb-3">
                                <label for="smtpPort" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtpPort" name="smtpPort">
                            </div>
                            <div class="mb-3">
                                <label for="smtpUser" class="form-label">SMTP Username</label>
                                <input type="text" class="form-control" id="smtpUser" name="smtpUser">
                            </div>
                            <div class="mb-3">
                                <label for="smtpPass" class="form-label">SMTP Password</label>
                                <input type="password" class="form-control" id="smtpPass" name="smtpPass">
                            </div>
                            <button type="submit" class="btn btn-primary">Save Email Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>