<?php
session_start();

// Check if the user is logged in or authenticated
// You can add your authentication logic here

// Include the database connection file
include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$successMessage = "";
$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the delete button is clicked
    if (isset($_POST['delete'])) {
        $professor_id = $_POST['delete'];
        // Perform the delete operation
        $delete_sql = "DELETE FROM professors WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $professor_id);

        if ($delete_stmt->execute()) {
            // Record deleted successfully
            $successMessage = "Professor record deleted successfully.";
        } else {
            // Error while deleting
            $errorMessage = "Error deleting professor record: " . $conn->error;
        }

        $delete_stmt->close();
    }
}

// Fetch data from the professors table
$query = "SELECT id, salutation, firstname, lastname, department, username, role FROM professors";
$result = $conn->query($query);

// Create an array to store the results
$professors = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $professors[] = $row;
    }
}

// Custom sorting function to display "admin" at the top
function customSort($a, $b) {
    if ($a['role'] === $b['role']) {
        return 0;
    }
    return ($a['role'] === 'admin') ? -1 : 1;
}

usort($professors, 'customSort');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/proflist.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Professor List</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Professor <span>List</span></h2>
        <?php
        if (!empty($successMessage)) {
            echo '<div class="alert alert-success">' . $successMessage . '</div>';
        }
        if (!empty($errorMessage)) {
            echo '<div class="alert alert-danger">' . $errorMessage . '</div>';
        }
        if (!empty($professors)) {
            echo '<table class="table table-striped">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Salutation</th>';
            echo '<th>First Name</th>';
            echo '<th>Last Name</th>';
            echo '<th>Department</th>';
            echo '<th>Username</th>';
            echo '<th>Role</th>';
            echo '<th>Action</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';
            foreach ($professors as $professor) {
                echo '<tr>';
                echo '<td>' . $professor['salutation'] . '</td>';
                echo '<td>' . $professor['firstname'] . '</td>';
                echo '<td>' . $professor['lastname'] . '</td>';
                echo '<td>' . $professor['department'] . '</td>';
                echo '<td>' . $professor['username'] . '</td>';
                echo '<td>' . $professor['role'] . '</td>';
                echo '<td>
                      <form method="post">
                      <button type="submit" name="delete" class="btn btn-danger" value="' . $professor['id'] . '">Delete</button>
                      </form>
                      </td>';
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo "No records found.";
        }
        ?>
    </div>
</body>

</html>
