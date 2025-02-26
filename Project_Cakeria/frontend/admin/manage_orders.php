<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../auth/admin_login.php");
    exit();
}

include '../../backend/db_config.php';

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'order_date_desc';

$order_query = "SELECT orders.*, customers.name AS customer_name, cakes.name AS cake_name 
                FROM orders
                JOIN customers ON orders.customer_id = customers.id
                JOIN cakes ON orders.cake_id = cakes.id";

if (!empty($search)) {
    $order_query .= " WHERE customers.name LIKE '%$search%' 
                      OR cakes.name LIKE '%$search%' 
                      OR orders.status LIKE '%$search%'";
}

switch ($sort) {
    case 'order_date_asc':
        $order_query .= " ORDER BY orders.order_date ASC";
        break;
    case 'customer_name_asc':
        $order_query .= " ORDER BY customers.name ASC";
        break;
    case 'customer_name_desc':
        $order_query .= " ORDER BY customers.name DESC";
        break;
    case 'cake_name_asc':
        $order_query .= " ORDER BY cakes.name ASC";
        break;
    case 'cake_name_desc':
        $order_query .= " ORDER BY cakes.name DESC";
        break;
    case 'status_asc':
        $order_query .= " ORDER BY orders.status ASC";
        break;
    case 'status_desc':
        $order_query .= " ORDER BY orders.status DESC";
        break;
    default:
        $order_query .= " ORDER BY orders.order_date DESC";
        break;
}

$result = mysqli_query($conn, $order_query);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateOrderStatus(orderId, status) {
            $.ajax({
                url: 'update_order_status.php',
                type: 'POST',
                data: {
                    order_id: orderId,
                    status: status
                },
                success: function(response) {
                    $('#status-' + orderId).text(status);
                    $('#message').html('<p style="color: green;">Order status updated successfully</p>');
                },
                error: function() {
                    $('#message').html('<p style="color: red;">Failed to update order status</p>');
                }
            });
        }

        function deleteOrder(orderId) {
            $.ajax({
                url: 'delete_order.php',
                type: 'POST',
                data: {
                    order_id: orderId
                },
                success: function(response) {
                    if (response == 'success') {
                        $('#order-' + orderId).remove();
                        $('#message').html('<p style="color: green;">Order deleted successfully</p>');
                    } else {
                        $('#message').html('<p style="color: red;">Failed to delete order</p>');
                    }
                },
                error: function() {
                    $('#message').html('<p style="color: red;">Failed to delete order</p>');
                }
            });
        }
    </script>
</head>
<body>
<header>
    <h1>Manage Orders</h1>
    <nav>
        <a href="../admin/admin_dashboard.php">Go to Dashboard</a>
        <a href="../admin/admin_login.php">Logout</a>
    </nav>
</header>
<main>
    <div id="message"></div>

    <form action="manage_orders.php" method="GET">
        <label for="sort">Sort Orders By:</label>
        <select name="sort" id="sort">
            <option value="order_date_desc" <?php if ($sort == 'order_date_desc') echo 'selected'; ?>>Order Date (Newest First)</option>
            <option value="order_date_asc" <?php if ($sort == 'order_date_asc') echo 'selected'; ?>>Order Date (Oldest First)</option>
            <option value="customer_name_asc" <?php if ($sort == 'customer_name_asc') echo 'selected'; ?>>Customer Name (A-Z)</option>
            <option value="customer_name_desc" <?php if ($sort == 'customer_name_desc') echo 'selected'; ?>>Customer Name (Z-A)</option>
            <option value="cake_name_asc" <?php if ($sort == 'cake_name_asc') echo 'selected'; ?>>Cake Name (A-Z)</option>
            <option value="cake_name_desc" <?php if ($sort == 'cake_name_desc') echo 'selected'; ?>>Cake Name (Z-A)</option>
            <option value="status_asc" <?php if ($sort == 'status_asc') echo 'selected'; ?>>Status (A-Z)</option>
            <option value="status_desc" <?php if ($sort == 'status_desc') echo 'selected'; ?>>Status (Z-A)</option>
        </select>
        <button type="submit">Sort</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Cake</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Status</th>
                <th>Order Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr id="order-<?php echo $row['id']; ?>">
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['cake_name']); ?></td>
                    <td><?php echo $row['quantity']; ?></td>
                    <td><?php echo $row['total_price']; ?></td>
                    <td id="status-<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo $row['order_date']; ?></td>
                    <td>
                        <select onchange="updateOrderStatus(<?php echo $row['id']; ?>, this.value)">
                            <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="completed" <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            <option value="cancelled" <?php if ($row['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                        </select>
                        <button onclick="deleteOrder(<?php echo $row['id']; ?>)">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</main>
<footer>
    &copy; <?php echo date('Y'); ?> Cakeria. All rights reserved.
</footer>
</body>
</html>

<?php

mysqli_close($conn);
?>