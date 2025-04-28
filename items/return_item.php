<?php
session_start();
require_once "../config/database.php";

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header('Location: ../index.php');
    exit();
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method";
    header("Location: ../dashboard/user.php");
    exit();
}

// Validate input
if (!isset($_POST['item_id'], $_POST['quantity'])) {
    $_SESSION['error'] = "Missing required fields";
    header("Location: ../dashboard/user.php");
    exit();
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if ($item_id === false || $item_id < 1 || $quantity === false || $quantity < 1) {
    $_SESSION['error'] = "Invalid item ID or quantity";
    header("Location: ../dashboard/user.php");
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check how many items the user has borrowed (not returned)
    $stmt = $conn->prepare("SELECT SUM(quantity) as total_borrowed 
                           FROM user_borrowed_items 
                           WHERE user_id = ? AND item_id = ? AND returned_at IS NULL
                           FOR UPDATE");
    $stmt->execute([$user_id, $item_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_borrowed = $result['total_borrowed'] ?? 0;

    if ($total_borrowed < $quantity) {
        throw new Exception("You can't return more than you've borrowed");
    }

    // Update item quantities in the inventory
    $update = $conn->prepare("UPDATE items SET available_quantity = available_quantity + ? WHERE id = ?");
    $update->execute([$quantity, $item_id]);

    // Mark items as returned (using FIFO approach)
    $stmt = $conn->prepare("UPDATE user_borrowed_items 
                           SET returned_at = NOW() 
                           WHERE user_id = ? AND item_id = ? AND returned_at IS NULL
                           ORDER BY borrowed_at ASC
                           LIMIT ?");
    $stmt->bindValue(1, $user_id);
    $stmt->bindValue(2, $item_id);
    $stmt->bindValue(3, $quantity, PDO::PARAM_INT);
    $stmt->execute();

    // Record the return transaction
    $insert = $conn->prepare("INSERT INTO transactions 
        (user_id, item_id, returned_quantity, transaction_date, status) 
        VALUES (?, ?, ?, NOW(), 'Returned')");
    $insert->execute([$user_id, $item_id, $quantity]);

    // Commit transaction
    $conn->commit();
    
    $_SESSION['success'] = "Successfully returned {$quantity} item(s)";
    
} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log("Return Error: " . $e->getMessage());
    
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to user dashboard
header("Location: ../dashboard/user.php");
exit();
?>
