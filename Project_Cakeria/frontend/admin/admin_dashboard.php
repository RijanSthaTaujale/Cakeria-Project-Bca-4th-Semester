<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: ../auth/admin_login.php");
    exit();
}

include '../../backend/db_config.php';

$product_query = "SELECT * FROM cakes";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $product_query = "SELECT * FROM cakes WHERE name LIKE '%$search%'";
}
$product_result = mysqli_query($conn, $product_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <a href="change_admin_credentials.php">Change Admin Credentials</a>
        <a href="admin_logout.php">Logout</a>
        <a href="manage_orders.php">Manage Orders</a>
    </nav>
</header>

<main>
    <h2>Manage Products</h2>

    <form action="admin_dashboard.php" method="GET">
        <label for="search">Search Cakes:</label>
        <input type="text" name="search" id="search" placeholder="Enter cake name">
        <button type="submit">Search</button>
    </form>

    <form action="add_product.php" method="POST" enctype="multipart/form-data">
        <h3>Add New Product</h3>
        <label for="name">Product Name:</label>
        <input type="text" name="name" id="name" required>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea>

        <label for="price">Price (NRS):</label>
        <input type="number" step="0.01" name="price" id="price" required>

        <label for="image">Image:</label>
        <input type="file" name="image" id="image" required>

        <button type="submit">Add Product</button>
    </form>

    <h3>Existing Products</h3>
    <div class="cake-container">
        <?php while ($row = mysqli_fetch_assoc($product_result)): ?>
            <div class="cake-item">
                <img src="../<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h3><?php echo $row['name']; ?></h3>
                <p><?php echo $row['description']; ?></p>
                <p><strong>Price: NRS <?php echo number_format($row['price'], 2); ?></strong></p>

                <form action="update_product.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <label for="name">Product Name:</label>
                    <input type="text" name="name" value="<?php echo $row['name']; ?>" required>

                    <label for="description">Description:</label>
                    <textarea name="description" required><?php echo $row['description']; ?></textarea>

                    <label for="price">Price (NRS):</label>
                    <input type="number" step="0.01" name="price" value="<?php echo $row['price']; ?>" required>

                    <label for="image">Image:</label>
                    <input type="file" name="image" id="image">

                    <button type="submit">Update Product</button>
                </form>

                <form action="delete_product.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <button type="submit">Delete Product</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> Cakeria. All rights reserved.
</footer>
</body>
</html>

<?php

mysqli_close($conn);
?>