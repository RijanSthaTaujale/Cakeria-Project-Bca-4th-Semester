<?php
session_start();

include '../../backend/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location:../auth/login.php');
    exit();
}

if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $cake_id = isset($_GET['id']) ? $_GET['id'] : null;

    if ($action == 'add' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $cake_id = $_POST['id'];
        $quantity = $_POST['quantity'];
        $user_id = $_SESSION['user_id'];

        error_log("Form Data: Cake ID=$cake_id, Quantity=$quantity, User ID=$user_id");

        $sql = "INSERT INTO cart (user_id, cake_id, quantity) VALUES ('$user_id', '$cake_id', '$quantity')";

        if (mysqli_query($conn, $sql)) {
            echo "Added to cart successfully!";
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    } elseif ($action == 'remove' && $cake_id) {
        $sql = "DELETE FROM cart WHERE id = '$cake_id'";
        if (!mysqli_query($conn, $sql)) {
            die("Error: " . $sql . "<br>" . mysqli_error($conn));
        }
    } elseif ($action == 'clear') {
        $sql = "DELETE FROM cart";
        if (!mysqli_query($conn, $sql)) {
            die("Error: " . $sql . "<br>" . mysqli_error($conn));
        }
    }
}

$query = "
    SELECT cart.id, cakes.name AS cake_name, cakes.price AS cake_price, cart.quantity
    FROM cart
    JOIN cakes ON cart.cake_id = cakes.id
    WHERE cart.user_id = '{$_SESSION['user_id']}'
";
$cart_result = mysqli_query($conn, $query);

if (!$cart_result) {
    die("Query failed: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header>
    <h1>Your Cart</h1>
    <nav>
        <a href="../dashboard.php">Go to Dashboard</a>
        <a href="cake.php">Cakes</a>
    </nav>
</header>
<main>
    <table>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Total</th>
            <th>Action</th>
        </tr>
        <?php
        $total = 0;
        while ($row = mysqli_fetch_assoc($cart_result)):
            $total += $row['cake_price'] * $row['quantity'];
        ?>
        <tr>
            <td><?php echo $row['cake_name']; ?></td>
            <td><?php echo $row['cake_price']; ?></td>
            <td><?php echo $row['quantity']; ?></td>
            <td><?php echo $row['cake_price'] * $row['quantity']; ?></td>
            <td>
                <a href="cart.php?action=remove&id=<?php echo $row['id']; ?>">Remove</a>
            </td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3">Total</td>
            <td><?php echo $total; ?></td>
            <td>
                <a href="cart.php?action=clear">Clear Cart</a>
            </td>
        </tr>
    </table>
    <br>
    <?php if ($total > 0): ?>
        <a href="order.php">Place Order</a>
    <?php endif; ?>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> Cakeria. All rights reserved.
</footer>
</body>
</html>