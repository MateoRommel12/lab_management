<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../utils/Auth.php';
require_once __DIR__ . '/../utils/Helpers.php';
require_once __DIR__ . '/../models/Borrowing.php';

// Initialize Auth
$auth = Auth::getInstance();

// Check if user has permission to approve borrowing
if (!$auth->canApproveBorrowing()) {
    Helpers::redirectWithMessage("../index.php", "You do not have permission to approve borrowing requests.", "danger");
    exit;
}

// Get borrowing ID from URL
$borrowingId = $_GET['id'] ?? null;

if (!$borrowingId) {
    Helpers::redirectWithMessage("index.php", "Invalid borrowing request.", "danger");
    exit;
}

// Initialize Borrowing model
$borrowing = new Borrowing();

// Get borrowing details
$borrowingDetails = $borrowing->getBorrowingWithDetails($borrowingId);

if (!$borrowingDetails) {
    Helpers::redirectWithMessage("index.php", "Borrowing request not found.", "danger");
    exit;
}

// Check if borrowing is pending
if ($borrowingDetails['status'] !== 'pending') {
    Helpers::redirectWithMessage("index.php", "This borrowing request has already been processed.", "danger");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Approve borrowing request
    if ($borrowing->approveBorrowing($borrowingId, $auth->getUserId())) {
        Helpers::redirectWithMessage("index.php", "Borrowing request approved successfully.", "success");
        exit;
    } else {
        Helpers::redirectWithMessage("approve.php?id=" . $borrowingId, "Failed to approve borrowing request.", "danger");
        exit;
    }
}

// Set page title
$pageTitle = "Approve Borrowing Request";

// Include header
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-check-circle me-2"></i>Approve Borrowing Request</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h5>Request Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Equipment:</strong> <?php echo htmlspecialchars($borrowingDetails['equipment_name'] ?? 'Unknown Equipment'); ?></p>
                                <p><strong>Serial Number:</strong> <?php echo htmlspecialchars($borrowingDetails['serial_number'] ?? 'N/A'); ?></p>
                                <p><strong>Category:</strong> <?php echo htmlspecialchars($borrowingDetails['category_name'] ?? 'Uncategorized'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Borrower:</strong> <?php echo htmlspecialchars($borrowingDetails['borrower_name'] ?? 'Unknown User'); ?></p>
                                <p><strong>Request Date:</strong> <?php echo isset($borrowingDetails['request_date']) ? date('M d, Y', strtotime($borrowingDetails['request_date'])) : 'N/A'; ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-warning">Pending</span></p>
                            </div>
                        </div>
                    </div>

                    <form action="approve.php?id=<?php echo $borrowingId; ?>" method="post">
                        <div class="d-flex justify-content-between">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Approve Request
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?> 