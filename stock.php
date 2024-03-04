<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "stock";

$database = new mysqli($servername, $username, $password, $dbname);

if ($database->connect_error) {
    die("Connection failed: " . $database->connect_error);
}

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function validate_item_id($item_id) {
    return preg_match('/^I\d{3}$/', $item_id);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        $item_id = sanitize_input($_POST['item_id']);
        $item_name = sanitize_input($_POST['item_name']);
        $quantity = sanitize_input($_POST['quantity']);
        $price = sanitize_input($_POST['price']);

        $checkResult = $database->query("SELECT * FROM item WHERE item_id='$item_id'");
        if ($checkResult->num_rows > 0) {
            $_SESSION['message'] = "Item ID already exists!";
            $_SESSION['msg_type'] = "danger";
        } elseif (!validate_item_id($item_id)) {
            $_SESSION['message'] = "Invalid Item ID format. Example: I001";
            $_SESSION['msg_type'] = "danger";
        } else {
            $database->query("INSERT INTO item (item_id, item_name, quantity, price) VALUES ('$item_id', '$item_name', '$quantity', '$price')")
                or die($database->error);

            $_SESSION['message'] = "Item added successfully!";
            $_SESSION['msg_type'] = "success";
        }
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}

if (isset($_GET['delete'])) {
    $item_id = sanitize_input($_GET['delete']);
    $database->query("DELETE FROM item WHERE item_id='$item_id'") or die($database->error);

    $_SESSION['message'] = "Item deleted successfully!";
    $_SESSION['msg_type'] = "danger";
    header("Location: {$_SERVER['PHP_SELF']}");
    exit();
}

if (isset($_GET['edit'])) {
    $edit_item_id = sanitize_input($_GET['edit']);
    $editResult = $database->query("SELECT * FROM item WHERE item_id='$edit_item_id'") or die($database->error);

    if ($editResult->num_rows == 1) {
        $editData = $editResult->fetch_assoc();
        $edit_item_id = $editData['item_id'];
        $edit_item_name = $editData['item_name'];
        $edit_quantity = $editData['quantity'];
        $edit_price = $editData['price'];
    } else {
        header("Location: {$_SERVER['PHP_SELF']}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Item Registration</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        body {
            background-image: url('lg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        .center-title {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            background-color: darkblue;
            padding: 10px;
            border-radius: 5px;
        }

        .error-message {
            color: red;
        }

        .form-group {
            text-align: left;
            margin-bottom: 30px;
        }

        .form-group input, .form-group select {
            width: 450px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .form-group label {
            font-weight: bold;
            font-size: 17px;
        }

        table {
            margin-top: 20px;
        }

        th, td {
            text-align: center;
        }

        th {
            background-color: #343a40;
            color: #ffffff;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .button-container {
            text-align: center;
            margin-top: 20px;
        }

        .btn-primary {
            background-color: #28a745; 
            border: none;
        }

        .btn-primary:hover {
            background-color: #218838; 
        }

        .btn-secondary {
            background-color: #dc3545; 
            border: none;
        }

        .btn-secondary:hover {
            background-color: #c82333; 
        }
    </style>
</head>
<body>
    <div class="container">

        <h2 class="center-title">Stock Item Registration</h2>

        <!-- Form for adding or updating items -->
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="post" class="mx-auto col-lg-6">
            <!-- Input fields for item information -->
            <div class="form-group">
                <label for="item_id">Item ID:</label>
                <input type="text" class="form-control" id="item_id" name="item_id" value="<?= isset($edit_item_id) ? $edit_item_id : '' ?>" required>
                <!-- Error message for invalid item ID -->
                <small class="error-message">
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add']) && !validate_item_id($_POST['item_id'])) echo "Invalid Item ID format. Example: I001"; ?>
                </small>
            </div>
            <div class="form-group">
                <label for="item_name">Item Name:</label>
                <input type="text" class="form-control" id="item_name" name="item_name" value="<?= isset($edit_item_name) ? $edit_item_name : '' ?>" required>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="<?= isset($edit_quantity) ? $edit_quantity : '' ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" class="form-control" id="price" name="price" value="<?= isset($edit_price) ? $edit_price : '' ?>" required>
            </div>

            <!-- Submit button -->
            <div class="button-container">
                <button type="submit" class="btn btn-primary" name="<?= isset($edit_item_id) ? 'update' : 'add' ?>">
                    <?= isset($edit_item_id) ? 'Update Item' : 'Add Item' ?>
                </button>
                <!-- Cancel button for editing -->
                <?php if (isset($edit_item_id)): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
                <?php endif; ?>
            </div>
        </form>

        <!-- Table to display existing items -->
        <br>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Item ID</th>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $database->query("SELECT * FROM item") or die($database->error);

                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td><?= $row['item_id'] ?></td>
                        <td><?= $row['item_name'] ?></td>
                        <td><?= $row['quantity'] ?></td>
                        <td><?= $row['price'] ?></td>
                        <td>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?edit=<?= $row['item_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="<?= $_SERVER['PHP_SELF'] ?>?delete=<?= $row['item_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>
