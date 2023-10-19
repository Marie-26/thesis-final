<?php
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data for professor
    $salutation = $_POST["salutation"];
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $department = $_POST["department"];

    // Get form data for user registration
    $username = $_POST["username"];
    $password = $_POST["password"];
    $name = $_POST["name"];
    $role = $_POST["role"];

    include 'dbconn.php';

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert professor data into the database
    $insert_professor_sql = "INSERT INTO professors (salutation, firstname, lastname, department) VALUES (?, ?, ?, ?)";
    $insert_professor_stmt = $conn->prepare($insert_professor_sql);
    $insert_professor_stmt->bind_param("ssss", $salutation, $firstname, $lastname, $department);

    // Register user
    $register_user_sql = "INSERT INTO login (username, password, name, role) VALUES (?, ?, ?, ?)";
    $register_user_stmt = $conn->prepare($register_user_sql);
    $register_user_stmt->bind_param("ssss", $username, $password, $name, $role);

    // Execute both queries within a transaction
    $conn->begin_transaction();
    $success = true;

    if (!$insert_professor_stmt->execute()) {
        $success = false;
    }

    if (!$register_user_stmt->execute()) {
        $success = false;
    }

    if ($success) {
        $conn->commit();
        echo "<script>alert('Professor and user added successfully.');</script>";
    } else {
        $conn->rollback();
        echo "<script>alert('Error adding professor or user: " . $conn->error . "');</script>";
    }

    $insert_professor_stmt->close();
    $register_user_stmt->close();
    $conn->close();
}
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
    <title>Add Professor and User</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Add Professor's Account</h2>
        <form action="professor.php" method="POST">
            <div class="mb-3">
                <label for="salutation" class="form-label">Salutation</label>
                <select class="form-select" name="salutation" id="salutation">
                    <option value="" disabled selected></option>
                    <option value="Mr.">Mr.</option>
                    <option value="Ms.">Ms.</option>
                    <option value="Mrs.">Mrs.</option>
                    <option value="Dr.">Dr.</option>
                    <option value="Prof.">Prof.</option>
                    <option value="Prof.">Dean</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="firstname" class="form-label">First Name</label>
                <input type="text" class="form-control" name="firstname" id="firstname" required>
            </div>
            <div class="mb-3">
                <label for="lastname" class="form-label">Last Name</label>
                <input type="text" class="form-control" name="lastname" id="lastname" required>
            </div>
            <div class="mb-3">
                <label for="department" class="form-label">Department</label>
                <select class="form-select" name="department" id="department">
                    <option value="" disabled selected></option>
                    <option value="BSCS">(BSCS) BS Computer Science</option>
                    <option value="BEED">(BEED) BE Elementary Education</option>
                    <option value="BSOA">(BSOA) BS Office Administration</option>
                    <option value="BSED">(BSED) BS Secondary Education</option>
                    <option value="BSBA">(BSBA) BS Business Administration</option>
                    <option value="ABREED">(ABREED) AB Religious Education</option>
                </select>
            </div>
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
            <button type="submit" class="btn btn-primary">Save Account</button>
        </form>
    </div>
</body>

</html>