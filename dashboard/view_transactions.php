<?php
session_start();
require_once "../config/database.php";

// Check Admin Authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

$transactions = [];
$page = 1;
$limit = 10;
$total = 0;
$pages = 1;

// Get Pagination Parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$start = ($page - 1) * $limit;

try {
    // Get Transaction Data
    $query = "SELECT t.transaction_id, i.name AS item_name, CONCAT(u.first_name, ' ', u.last_name) AS user_name, 
                 t.borrowed_quantity, t.returned_quantity, t.transaction_date 
          FROM transactions t
          JOIN items i ON t.item_id = i.id
          JOIN users u ON t.user_id = u.id
          ORDER BY t.transaction_date DESC 
          LIMIT ?, ?";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(1, $start, PDO::PARAM_INT);
    $stmt->bindValue(2, $limit, PDO::PARAM_INT);
    $stmt->execute();

    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count for Pagination
    $countQuery = "SELECT COUNT(*) FROM transactions";
    $countStmt = $conn->prepare($countQuery);
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
    $pages = ceil($total / $limit);
    if ($pages < 1) $pages = 1;

} catch (PDOException $e) {
    $_SESSION['error'] = "Failed to load transactions: " . $e->getMessage();
    error_log("Database Error: " . $e->getMessage());
    $transactions = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Transactions | Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white p-4 shadow flex justify-between items-center">
    <h1 class="text-xl font-bold">Admin Dashboard</h1>
    <a href="../logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
</nav>

<div class="p-8">
    <h2 class="text-2xl font-bold mb-6">Transactions</h2>

    <!-- Back to Admin Dashboard Button -->
    <div class="mb-4">
        <a href="admin_requests.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            Back to Admin Dashboard
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white shadow rounded">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-2 text-left">Item Name</th>
                    <th class="px-4 py-2 text-left">User Name</th>
                    <th class="px-4 py-2 text-center">Borrowed Quantity</th>
                    <th class="px-4 py-2 text-center">Returned Quantity</th>
                    <th class="px-4 py-2 text-center">Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($transactions) > 0): ?>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr class="border-t">
                            <td class="px-4 py-2"><?= htmlspecialchars($transaction['item_name']) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($transaction['user_name']) ?></td>
                            <td class="px-4 py-2 text-center"><?= htmlspecialchars($transaction['borrowed_quantity']) ?></td>
                            <td class="px-4 py-2 text-center"><?= htmlspecialchars($transaction['returned_quantity']) ?></td>
                            <td class="px-4 py-2 text-center"><?= date('Y-m-d H:i:s', strtotime($transaction['transaction_date'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500">No transactions found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex justify-center">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
            <a href="?page=<?= $i ?>"
               class="px-4 py-2 mx-1 rounded <?= $i === $page ? 'bg-blue-500 text-white' : 'bg-white text-blue-500 border'; ?>">
               <?= $i ?>
            </a>
        <?php endfor; ?>
    </div>

</div>

</body>
</html>
