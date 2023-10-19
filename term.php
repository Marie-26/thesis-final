<?php
session_start();

$settings_error = ""; // Initialize the error message

// Include your database connection code here
include 'dbconn.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a delete request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];

    // Prepare and execute the DELETE query
    $delete_query = "DELETE FROM settings WHERE id = $delete_id";
    if ($conn->query($delete_query) === TRUE) {
        // Redirect back to the same page after deleting
        header("Location: term.php");
        exit(); // Ensure no further code execution
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Check if a set request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["set_id"])) {
    $set_id = $_POST["set_id"];

    // Start by setting all rows to inactive
    $set_inactive_query = "UPDATE settings SET status = 'inactive'";
    if ($conn->query($set_inactive_query) === TRUE) {
        // Set the selected row as active
        $set_query = "UPDATE settings SET status = 'active' WHERE id = $set_id";
        if ($conn->query($set_query) === TRUE) {
            // Redirect back to the same page after setting
            header("Location: term.php");
            exit(); // Ensure no further code execution
        } else {
            echo "Error setting record as active: " . $conn->error;
        }
    } else {
        echo "Error setting all records as inactive: " . $conn->error;
    }
}

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $semester = isset($_POST['semester']) ? $_POST['semester'] : "";
    $school_year = isset($_POST['school_year']) ? $_POST['school_year'] : "";
    $term = isset($_POST['term']) ? $_POST['term'] : "";
    $status = isset($_POST['status']) ? $_POST['status'] : "";

    if ($status === "active") {
        // Check if an active setting already exists
        $active_check_sql = "SELECT COUNT(*) FROM settings WHERE status = 'active'";
        $active_check_result = $conn->query($active_check_sql);
        $active_count = $active_check_result->fetch_row()[0];

        if ($active_count > 0) {
            // Active setting already exists, set the error message
            $settings_error = "An active setting already exists. Please modify it accordingly.";
        }
    }

    if (empty($settings_error)) {
        // Check if a setting with the same Semester, School Year, and Term combination exists
        $duplicate_check_sql = "SELECT COUNT(*) FROM settings WHERE semester = '$semester' AND school_year = '$school_year' AND term = '$term'";
        $duplicate_check_result = $conn->query($duplicate_check_sql);
        $duplicate_count = $duplicate_check_result->fetch_row()[0];

        if ($duplicate_count > 0) {
            // Duplicate setting exists, set the error message
            $settings_error = "A setting with the same Semester, School Year, and Term already exists. Please modify it accordingly.";
        } else {
            // Insert data into the 'settings' table
            $sql = "INSERT INTO settings (semester, school_year, term, status) VALUES ('$semester', '$school_year', '$term', '$status')";

            if ($conn->query($sql) === TRUE) {
                // Display success alert using JavaScript
                echo "<script>alert('Settings saved successfully!');</script>";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }
}

// Fetch all settings from the database and order them by status
$query = "SELECT * FROM settings ORDER BY CASE WHEN status='active' THEN 0 ELSE 1 END, id DESC";
$result = $conn->query($query);

// Check if there are any settings
if ($result->num_rows > 0) {
    $settings = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $settings = [];
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/term.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Term</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Te<span>rm</span></h2>
        <?php if (!empty($settings_error)) : ?>
            <div class="mt-3 alert alert-danger"><?php echo $settings_error; ?></div>
        <?php endif; ?>
        <form action="term.php" method="POST">
            <div class="mb-3">
                <label for="semester" class="form-label">Select Semester</label>
                <select class="form-select" name="semester" id="semester">
                    <option value="1st">1st</option>
                    <option value="2nd">2nd</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="school_year" class="form-label">Select School Year</label>
                <select class="form-select" name="school_year" id="school_year">
                    <option value="2021-2022">2021-2022</option>
                    <option value="2022-2023">2022-2023</option>
                    <option value="2023-2024">2023-2024</option>
                    <option value="2024-2025">2024-2025</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="term" class="form-label">Select Term</label>
                <select class="form-select" name="term" id="term">
                    <option value="midterm">Midterm</option>
                    <option value="endterm">Endterm</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" name="status" id="status">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Settings</button>
            <button type="button" class="btn btn-secondary" onclick="cancelSettings()">Cancel</button>
        </form>
        </div>

        <div class="container mt-5 text-center">
        <h2 class="mt-5">Display <span>Settings</span></h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>School Year</th>
                    <th>Term</th>
                    <th>Status</th>
                    <th>Action</th> <!-- New column for delete and set buttons -->
                </tr>
            </thead>
            <tbody>
                <?php foreach ($settings as $setting) : ?>
                    <tr>
                        <td><?php echo $setting['semester']; ?></td>
                        <td><?php echo $setting['school_year']; ?></td>
                        <td><?php echo $setting['term']; ?></td>
                        <td><?php echo $setting['status']; ?></td>
                        <td>
                            <div style="display: inline-block;">
                                <form method="post" action="">
                                    <input type="hidden" name="set_id" value="<?php echo $setting['id']; ?>">
                                    <button type="submit" class="btn btn-primary mx-auto" onclick="return confirm('Are you sure you want to set this as active?')">Set</button>
                                </form>
                            </div>
                                <div style="display: inline-block;">
                                    <form method="post" action="">
                                        <input type="hidden" name="delete_id" value="<?php echo $setting['id']; ?>">
                                        <button type="submit" class="btn btn-danger mx-auto" onclick="return confirm('Are you sure you want to delete this setting?')">Delete</button>
                                    </form>
                                </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        function cancelSettings() {
            window.location.href = "term.php";
        }
    </script>
</body>

</html>