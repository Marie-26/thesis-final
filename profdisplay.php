<?php
session_start();

include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a professor should be deleted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_username"])) {
    $delete_username = $_POST["delete_username"];

    // Delete professor from the professors table
    $delete_professor_sql = "DELETE FROM professors WHERE username = ?";
    $stmt_delete_professor = $conn->prepare($delete_professor_sql);
    $stmt_delete_professor->bind_param("s", $delete_username);
    $stmt_delete_professor->execute();

    // Delete professor's login data from the login table
    $delete_login_sql = "DELETE FROM login WHERE username = ?";
    $stmt_delete_login = $conn->prepare($delete_login_sql);
    $stmt_delete_login->bind_param("s", $delete_username);
    $stmt_delete_login->execute();

    // Redirect back to the professor list after deletion
    header("Location: profdisplay.php");
    exit();
}

// Query to fetch all professors
$fetch_professors_sql = "SELECT * FROM professors";
$fetch_professors_result = $conn->query($fetch_professors_sql);

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
    <title>Professor Display</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>List of Professors</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Salutation</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Department</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($fetch_professors_result->num_rows > 0) {
                    while ($row = $fetch_professors_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row["salutation"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["firstname"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["lastname"]) . "</td>";
                        echo "<td>" . htmlspecialchars($row["department"]) . "</td>";

                        // Fetch the role from the login table using the professor's username
                        $username = $row["username"];
                        $fetch_role_sql = "SELECT role FROM login WHERE username = ?";
                        $stmt = $conn->prepare($fetch_role_sql);
                        $stmt->bind_param("s", $username);
                        $stmt->execute();
                        $stmt->bind_result($role);
                        $stmt->fetch();
                        $stmt->close();

                        echo "<td>" . htmlspecialchars($role) . "</td>";

                        // Delete button with a confirmation dialog
                        echo "<td><form method='post' action='profdisplay.php' onsubmit='return confirm(\"Are you sure you want to delete this professor?\");'>";
                        echo "<input type='hidden' name='delete_username' value='" . $username . "'>";
                        echo "<button type='submit' class='btn btn-danger'>Delete</button>";
                        echo "</form></td>";

                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No professors found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>
