<?php
session_start();

include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$signup_error = ""; // Initialize the error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    $name = $_POST["name"];
    $role = $_POST["role"]; // Retrieve the role from the form

    // Check if username and password meet the length requirement
    if (strlen($username) < 5 || strlen($password) < 5) {
        $signup_error = "Username and password must be at least 5 characters long.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $signup_error = "Password must contain at least one symbol.";
    } else {
        // Check if the username already exists in the database
        $check_username_sql = "SELECT id FROM login WHERE username = '$username'";
        $check_result = $conn->query($check_username_sql);

        if ($check_result->num_rows > 0) {
            $signup_error = "Username already exists. Please choose a different username.";
        } else {
            // Username is unique, meets the length and symbol requirement, proceed with registration
            $sql = "INSERT INTO login (username, password, name, role) VALUES ('$username', '$password', '$name', '$role')";

            if ($conn->query($sql) === TRUE) {
                $_SESSION["user_id"] = $conn->insert_id;
                $_SESSION["username"] = $username;
                $_SESSION["name"] = $name;
                $_SESSION["role"] = $role; // Store the role in the session
                header("Location: signup.php");
                exit();
            } else {
                $signup_error = "Error: " . $conn->error;
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Sign Up</title>
</head>

<body>
<?php include 'nav.php';?>
    <div class="container">
        <h2>Add Logins</h2>
        <form action="signup.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" name="name" id="name" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" name="role" id="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
        <?php if (!empty($signup_error)) : ?>
            <div class="mt-3 alert alert-danger"><?php echo $signup_error; ?></div>
        <?php endif; ?>
    </div>
    <script>
        // Enable Bootstrap dropdown
        $(document).ready(function () {
            $(".dropdown-toggle").dropdown();
        });
    </script>
</body>

</html>
