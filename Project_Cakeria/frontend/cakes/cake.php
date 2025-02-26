<?php
session_start();
include '../../backend/db_config.php';

$query = "SELECT * FROM cakes";
if (isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT * FROM cakes WHERE name LIKE '%$search%'";
}
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cake Catalog</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".add-to-cart-form").submit(function(event) {
                event.preventDefault(); 
                var form = $(this);
                var formData = form.serialize();
                console.log("Form Data: ", formData); 

                $.ajax({
                    type: "POST",
                    url: "cart.php?action=add",
                    data: formData,
                    success: function(response) {
                        console.log("Response: ", response);
                        alert("Item added to cart successfully!");
                    },
                    error: function(xhr, status, error) {
                        console.error("Error: ", error); 
                        alert("An error occurred while adding the item to the cart.");
                    }
                });
            });
        });
    </script>
</head>
<body>
<header>
    <h1>Welcome to Cakeria</h1>
    <nav>
        <a href="../dashboard.php">Go to Dashboard</a>
        <a href="cart.php">View Cart</a>
    </nav>
</header>

<main>
    <form action="cake.php" method="GET">
        <label for="search">Search Cakes:</label>
        <input type="text" name="search" id="search" placeholder="Enter cake name">
        <button type="submit">Search</button>
    </form>

    <div class="cake-container">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="cake-item">
                <img src="../<?php echo $row['image']; ?>" alt="<?php echo $row['name']; ?>">
                <h3><?php echo $row['name']; ?></h3>
                <p><?php echo $row['description']; ?></p>
                <p>Price: NRS <?php echo $row['price']; ?></p>
                <form class="add-to-cart-form" method="POST">
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                    <input type="hidden" name="name" value="<?php echo $row['name']; ?>">
                    <input type="hidden" name="price" value="<?php echo $row['price']; ?>">
                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="1" min="1">
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</main>
</body>
</html>