<?php
session_start();
include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if a delete request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    
    // Prepare the DELETE statement
    $delete_sql = "DELETE FROM scheduler WHERE id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("i", $delete_id);
    
    if ($delete_stmt->execute()) {
        // Delete successful
        header("Location: scheduleupdate.php"); // Redirect back to the same page
        exit();
    } else {
        echo '<script>alert("Error deleting schedule: ' . $delete_stmt->error . '");</script>';
    }
}

// Initialize search criteria
$search_section = "";
$search_professor = "";
$search_subject = "";

// Check if a search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search_section = $_POST['search_section'];
    $search_professor = $_POST['search_professor'];
    $search_subject = $_POST['search_subject'];
}

// Query to fetch schedule data from the database with search criteria
$schedule_query = "SELECT id, day_of_week, timeframe, professor, section, subject FROM scheduler WHERE 1";

if (!empty($search_section)) {
    $schedule_query .= " AND section = '$search_section'";
}
if (!empty($search_professor)) {
    $schedule_query .= " AND professor = '$search_professor'";
}
if (!empty($search_subject)) {
    $schedule_query .= " AND subject = '$search_subject'";
}

$result = $conn->query($schedule_query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Schedule Update</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/update.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <h2>Schedule <span>Update</span></h2>
        <form method="post" action="">
            <div class="mb-3">
                <label for="search_section" class="form-label">Search by Section</label>
                <select class="form-select" name="search_section" id="search_section">
                    <option value="">All Sections</option>
                    <?php
                    // Fetch distinct sections from the database
                    $section_query = "SELECT DISTINCT section FROM scheduler";
                    $section_result = $conn->query($section_query);
                    while ($section_row = $section_result->fetch_assoc()) {
                        $section_value = $section_row['section'];
                        $selected = ($section_value == $search_section) ? 'selected' : '';
                        echo "<option value='$section_value' $selected>$section_value</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="search_professor" class="form-label">Search by Professor</label>
                <select class="form-select" name="search_professor" id="search_professor">
                    <option value="">All Professors</option>
                    <?php
                    // Fetch distinct professors from the database
                    $professor_query = "SELECT DISTINCT professor FROM scheduler";
                    $professor_result = $conn->query($professor_query);
                    while ($professor_row = $professor_result->fetch_assoc()) {
                        $professor_value = $professor_row['professor'];
                        $selected = ($professor_value == $search_professor) ? 'selected' : '';
                        echo "<option value='$professor_value' $selected>$professor_value</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="search_subject" class="form-label">Search by Subject</label>
                <select class="form-select" name="search_subject" id="search_subject">
                    <option value="">All Subjects</option>
                    <?php
                    // Fetch distinct subjects from the database
                    $subject_query = "SELECT DISTINCT subject FROM scheduler";
                    $subject_result = $conn->query($subject_query);
                    while ($subject_row = $subject_result->fetch_assoc()) {
                        $subject_value = $subject_row['subject'];
                        $selected = ($subject_value == $search_subject) ? 'selected' : '';
                        echo "<option value='$subject_value' $selected>$subject_value</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mb-5">Search</button>
        </form>
        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Day of Week</th>
                    <th>Timeframe</th>
                    <th>Professor</th>
                    <th>Section</th>
                    <th>Subject</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the schedule data in a table with a delete button
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        ?>
                        <tr>
                            <td><?php echo $row['day_of_week']; ?></td>
                            <td><?php echo $row['timeframe']; ?></td>
                            <td><?php echo $row['professor']; ?></td>
                            <td><?php echo $row['section']; ?></td>
                            <td><?php echo $row['subject']; ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="6">No schedule data found in the database.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
$conn->close();
?>
