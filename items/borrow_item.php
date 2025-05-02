<?php
session_start();
require_once "../config/database.php";

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Validate inputs
$required = ['item_id', 'quantity', 'due_date'];
foreach ($required as $field) {
    if (!isset($_POST[$field])) {
        $_SESSION['error'] = "Missing required field: $field";
        header("Location: ../dashboard/user.php");
        exit();
    }
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_VALIDATE_INT);
$quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
$due_date = $_POST['due_date'];
$user_id = $_SESSION['user_id'];

// Validate date
if (!strtotime($due_date) || strtotime($due_date) < strtotime('tomorrow')) {
    $_SESSION['error'] = "Please select a valid future date (at least tomorrow)";
    header("Location: ../dashboard/user.php");
    exit();
}

try {
    // Set database timeout
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 5);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check for overdue items
    $overdue_check = $conn->prepare("SELECT COUNT(*) as overdue_count FROM user_borrowed_items 
                                   WHERE user_id = ? AND returned_at IS NULL AND due_date < NOW()");
    $overdue_check->execute([$user_id]);
    if ($overdue_check->fetchColumn() > 0) {
        $_SESSION['error'] = "You have overdue items. Please return them first.";
        header("Location: ../dashboard/user.php");
        exit();
    }

    // Start transaction
    $conn->beginTransaction();

    // Check item availability
    $stmt = $conn->prepare("SELECT available_quantity FROM items WHERE id = ? FOR UPDATE");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch();
    
    if (!$item || $item['available_quantity'] < $quantity) {
        throw new Exception($item ? "Not enough items available" : "Item not found");
    }

    // Record borrowing
    $insert = $conn->prepare("INSERT INTO user_borrowed_items 
                            (user_id, item_id, quantity, borrowed_at, due_date) 
                            VALUES (?, ?, ?, NOW(), ?)");
    $insert->execute([$user_id, $item_id, $quantity, $due_date]);

    // Update inventory
    $conn->prepare("UPDATE items SET available_quantity = available_quantity - ? WHERE id = ?")
         ->execute([$quantity, $item_id]);

    // Record transaction
    $conn->prepare("INSERT INTO transactions 
                   (user_id, item_id, borrowed_quantity, transaction_date, status) 
                   VALUES (?, ?, ?, NOW(), 'Borrowed')")
         ->execute([$user_id, $item_id, $quantity]);

    $conn->commit();
    $_SESSION['success'] = "Successfully borrowed item. Due: " . date('M j, Y', strtotime($due_date));

} catch (PDOException $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    $_SESSION['error'] = "Database error. Please try again.";
    error_log("Borrow Error: " . $e->getMessage());
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header("Location: ../dashboard/user.php");
exit();
?>