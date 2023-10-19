<?php
session_start();

include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$login_error = ""; // Initialize the error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Query the professors table to check for username
    $sql = "SELECT id, username, password, role, salutation, firstname, lastname FROM professors WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // Verify the password using password_verify
        if (password_verify($password, $row["password"])) {
            $_SESSION["user_id"] = $row["id"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["salutation"] = $row["salutation"];
            $_SESSION["firstname"] = $row["firstname"];
            $_SESSION["lastname"] = $row["lastname"];

            if ($row["role"] === "admin") {
                header("Location: download.php"); // Redirect to admin page
                exit();
            } else {
                header("Location: userdownload.php"); // Redirect to user page
                exit();
            }
        } else {
            $login_error = "Invalid username or password.";
        }
    } else {
        $login_error = "Invalid username or password.";
    }

    $stmt->close();
}

$conn->close();
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <title>Login</title>
</head>

<body>

    <div class="wrapper">
        <div class="logo">
        <img src="image/rare.png" alt="">    
    </div>
    <div class="text-center mt-4 name">
        <h2>Login</h2>
    </div>

        <!-- Display the error message in an alert -->
        <?php if (!empty($login_error)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" class="p-3 mt-3">

            <div class="form-field d-flex align-items-center">
                <span class="far fa-user"></span>
                <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
            </div>

            <div class="form-field d-flex align-items-center">
                <span class="fas fa-key"></span>
                <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
            </div>

            <button type="submit" class="btn mt-3">Login</button>
        </form>
        </div>
    
</body>

</html>
