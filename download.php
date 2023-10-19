<?php
session_start();
include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function deleteRow($conn, $id)
{
    // First, retrieve the filename associated with the row
    $get_filename_sql = "SELECT filename FROM syllabus WHERE id = $id";
    $result = $conn->query($get_filename_sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $filename = $row['filename'];

        // Delete the row from the database
        $delete_sql = "DELETE FROM syllabus WHERE id = $id";

        if ($conn->query($delete_sql) === TRUE) {
            // Now, delete the associated file from the "uploads" folder
            $file_path = __DIR__ . "/uploads/" . $filename;
            if (file_exists($file_path)) {
                if (unlink($file_path)) {
                    return true;
                } else {
                    // Handle file deletion error
                    return false;
                }
            }
        }
    }
    return false;
}



// Check if a delete request is sent
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_id"])) {
    $delete_id = $_POST["delete_id"];
    if (deleteRow($conn, $delete_id)) {
        // Redirect back to the same page
        header("Location: " . $_SERVER['PHP_SELF']);
        exit(); // Ensure no further code execution
    } else {
        echo '<script>alert("Error deleting row: ' . $conn->error . '");</script>';
    }
}

// Initialize search criteria
$selected_course = "";
$selected_year = "";
$selected_semester = "";

// Check if a search form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selected_course = $_POST['course'];
    $selected_year = $_POST['year'];
    $selected_semester = $_POST['semester'];
}

// Fetch the uploaded files and additional information from the database based on search criteria
$sql = "SELECT id, filename, subject_title, course, year, semester, DATE_FORMAT(upload_date, '%Y-%m-%d') AS upload_date, DATE_FORMAT(upload_date, '%H:%i:%s') AS upload_time FROM syllabus WHERE 1";

if (!empty($selected_course)) {
    $sql .= " AND course = '$selected_course'";
}
if (!empty($selected_year)) {
    $sql .= " AND year = '$selected_year'";
}
if (!empty($selected_semester)) {
    $sql .= " AND semester = '$selected_semester'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Syllabus</title>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/download.css">
</head>

<body>
    <?php include 'nav.php'; ?>

    <div class="container">
        <h2>Search Syll<span>abus Files</span></h2>
        <form action="download.php" method="POST">
            <div class="mb-3">
                <label for="course" class="form-label">Select Course</label>
                <select class="form-select" name="course" id="course">
                    <option value="">All Courses</option>
                    <option value="BSCS">(BSCS) BS Computer Science</option>
                    <option value="BEED">(BEED) BE Elementary Education</option>
                    <option value="BSOA">(BSOA) BS Office Administration</option>
                    <option value="BSED">(BSED) BS Secondary Education</option>
                    <option value="BSBA">(BSBA) BS Business Administration</option>
                    <option value="ABREED">(ABREED) AB Religious Education</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">Select Year</label>
                <select class="form-select" name="year" id="year">
                    <option value="">All Years</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Select Semester</label>
                <select class="form-select" name="semester" id="semester">
                    <option value="">All Semesters</option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="container mt-5 text-center">
        <h2 class="files">Uploaded <span>Files</span></h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Subject Title</th>
                    <th>File Name</th>
                    <th>Course</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Upload Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the uploaded files, download links, and delete buttons
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $file_path = "uploads/" . $row['filename'];
                ?>
                        <tr>
                            <td><?php echo $row['subject_title']; ?></td>
                            <td><?php echo $row['filename']; ?></td>
                            <td><?php echo $row['course']; ?></td>
                            <td><?php echo $row['year']; ?></td>
                            <td><?php echo $row['semester']; ?></td>
                            <td><?php echo $row['upload_date']; ?></td>
                            <td>
                                <div style="display: inline-block;">
                                    <a href="<?php echo $file_path; ?>" class="btn btn-primary mx-auto" download>Download</a>
                                </div>
                                <div style="display: inline-block;">
                                    <form method="post" action="">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="button" class="btn btn-danger mx-auto" onclick="confirmDelete(<?php echo $row['id']; ?>)">Delete</button>
                                    </form>
                                </div>
                            </td>
                        <?php
                    }
                } else {
                        ?>
                        <tr>
                            <td colspan="8">No files uploaded yet.</td>
                        </tr>
                    <?php
                }
                    ?>
            </tbody>
        </table>
    </div>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this file?")) {
                // If the user confirms, submit the form to delete the file
                var form = document.createElement("form");
                form.method = "post";
                form.action = "";
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "delete_id";
                input.value = id;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script>
        // Enable Bootstrap dropdown
        $(document).ready(function() {
            $(".dropdown-toggle").dropdown();
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
