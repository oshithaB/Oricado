<?php
require_once '../config/config.php';
checkAuth(['office_staff']);

// Handle new expense submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $bill_number = trim($_POST['bill_number']);
    $created_by = $_SESSION['user_id'];

    if ($description !== '' && $amount > 0) {
        $stmt = $conn->prepare("INSERT INTO expenses (created_by, description, amount, bill_number, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("isds", $created_by, $description, $amount, $bill_number);
        $stmt->execute();
        header("Location: expenses.php");
        exit;
    }
}

// Fetch all expenses
$expenses = $conn->query("SELECT e.*, u.username FROM expenses e LEFT JOIN users u ON e.created_by = u.id ORDER BY e.created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Expenses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .expenses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .expenses-table th, .expenses-table td {
            vertical-align: middle;
        }
        .expenses-table th {
            background: #f8f9fa;
        }
        .expenses-total {
            font-size: 1.2rem;
            font-weight: bold;
            color: #007bff;
            text-align: right;
            margin-top: 1rem;
        }
        .modal-header {
            background: #007bff;
            color: white;
        }
        .modal-footer .btn {
            min-width: 100px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="expenses-header">
            <h2><i class="fas fa-money-bill-wave me-2"></i>Expenses</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                <i class="fas fa-plus me-1"></i>New Expense
            </button>
        </div>
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-hover expenses-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Description</th>
                            <th>Amount (Rs.)</th>
                            <th>Bill Number</th>
                            <th>Created By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; foreach ($expenses as $i => $exp): $total += $exp['amount']; ?>
                        <tr>
                            <td><?php echo $i + 1; ?></td>
                            <td><?php echo htmlspecialchars($exp['description']); ?></td>
                            <td class="text-end text-success fw-bold"><?php echo number_format($exp['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($exp['bill_number']); ?></td>
                            <td><?php echo htmlspecialchars($exp['username'] ?? ''); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($exp['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="expenses-total">
                    Total Expenses: Rs. <?php echo number_format($total, 2); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form method="post" class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addExpenseModalLabel"><i class="fas fa-plus me-2"></i>Add New Expense</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Description <span class="text-danger">*</span></label>
                  <input type="text" name="description" class="form-control" required maxlength="255">
              </div>
              <div class="mb-3">
                  <label class="form-label">Amount (Rs.) <span class="text-danger">*</span></label>
                  <input type="number" name="amount" class="form-control" required min="0.01" step="0.01">
              </div>
              <div class="mb-3">
                  <label class="form-label">Bill Number</label>
                  <input type="text" name="bill_number" class="form-control" maxlength="100">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Expense</button>
          </div>
        </form>
      </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>
