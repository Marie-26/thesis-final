<?php
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data for professor
    $salutation = $_POST["salutation"];
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $department = $_POST["department"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"];

    include 'dbconn.php';

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $signup_error = ""; // Initialize the error message

    // Check if the username already exists
    $check_username_sql = "SELECT id FROM professors WHERE username = ?";
    $check_username_stmt = $conn->prepare($check_username_sql);
    $check_username_stmt->bind_param("s", $username);
    $check_username_stmt->execute();
    $check_username_stmt->store_result();

    if ($check_username_stmt->num_rows > 0) {
        $signup_error = "Username already exists. Please choose a different username.";
    } elseif (strlen($username) < 5 || strlen($password) < 5) {
        $signup_error = "Username and password must be at least 5 characters long.";
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>0-9]/', $password)) {
        $signup_error = "Password must contain at least one symbol or numbers.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert professor data into the database with hashed password
        $insert_professor_sql = "INSERT INTO professors (salutation, firstname, lastname, department, username, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_professor_stmt = $conn->prepare($insert_professor_sql);
        $insert_professor_stmt->bind_param("sssssss", $salutation, $firstname, $lastname, $department, $username, $hashed_password, $role);

        // Execute the query
        if ($insert_professor_stmt->execute()) {
            // Close the database connection
            $conn->close();

            // Show a success message and redirect after 0.5 seconds
            echo '<script>
                    setTimeout(function() {
                        alert("User added successfully.");
                        window.location = "professor.php";
                    }, 500);
                  </script>';
            exit; // Make sure to exit to prevent further execution
        } else {
            $signup_error = "Error adding user: " . $conn->error;
        }

        $insert_professor_stmt->close();
    }

    $check_username_stmt->close();
    $conn->close();
}
?>



<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/professor.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Add Professor and User</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Add Profes<span>sor's Account</span></h2>

        <?php
        if (!empty($signup_message)) {
            echo "<div class='alert alert-success'>$signup_message</div>";
        }
        if (!empty($signup_error)) {
            echo "<div class='alert alert-danger'>$signup_error</div>";
        }
        ?>

        <form action="professor.php" method="POST">
            <div class="mb-3">
                <label for="salutation" class="form-label">Salutation</label>
                <select class="form-select" name="salutation" id="salutation">
                    <option value="" disabled selected></option>
                    <option value="Mr." <?php if (isset($salutation) && $salutation === 'Mr.') echo 'selected'; ?>>Mr.</option>
                    <option value="Ms." <?php if (isset($salutation) && $salutation === 'Ms.') echo 'selected'; ?>>Ms.</option>
                    <option value="Mrs." <?php if (isset($salutation) && $salutation === 'Mrs.') echo 'selected'; ?>>Mrs.</option>
                    <option value="Dr." <?php if (isset($salutation) && $salutation === 'Dr.') echo 'selected'; ?>>Dr.</option>
                    <option value="Prof." <?php if (isset($salutation) && $salutation === 'Prof.') echo 'selected'; ?>>Prof.</option>
                    <option value="Dean" <?php if (isset($salutation) && $salutation === 'Dean') echo 'selected'; ?>>Dean</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" name="firstname" id="firstname" required value="<?php echo isset($firstname) ? $firstname : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" name="lastname" id="lastname" required value="<?php echo isset($lastname) ? $lastname : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" name="department" id="department">
                    <option value="" disabled selected></option>
                    <option value="BSCS" <?php if (isset($department) && $department === 'BSCS') echo 'selected'; ?>>(BSCS) BS Computer Science</option>
                    <option value="BEED" <?php if (isset($department) && $department === 'BEED') echo 'selected'; ?>>(BEED) BE Elementary Education</option>
                    <option value="BSOA" <?php if (isset($department) && $department === 'BSOA') echo 'selected'; ?>>(BSOA) BS Office Administration</option>
                    <option value="BSED" <?php if (isset($department) && $department === 'BSED') echo 'selected'; ?>>(BSED) BS Secondary Education</option>
                    <option value="BSBA" <?php if (isset($department) && $department === 'BSBA') echo 'selected'; ?>>(BSBA) BS Business Administration</option>
                    <option value="ABREED" <?php if (isset($department) && $department === 'ABREED') echo 'selected'; ?>>(ABREED) AB Religious Education</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" name="username" id="username" required placeholder="Username (at least 5 characters)" value="<?php echo isset($username) ? $username : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" name="password" id="password" required placeholder="Password (at least 5 characters with one symbol)" value="<?php echo isset($password) ? $password : ''; ?>">
                <small class="form-text text-muted">Password must contain at least one symbol or numbers.</small>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select class="form-select" name="role" id="role" required>
                    <option value="user" <?php if (isset($role) && $role === 'user') echo 'selected'; ?>>User</option>
                    <option value="admin" <?php if (isset($role) && $role === 'admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Account</button>
        </form>
    </div>
</body>

</html>