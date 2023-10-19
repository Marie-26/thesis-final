<?php
session_start();

include 'dbconn.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function deleteSubject($conn, $subject_code)
{
    $delete_sql = "DELETE FROM subjects WHERE subject_code = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("s", $subject_code);

    if ($delete_stmt->execute()) {
        return true;
    } else {
        return false;
    }
}

// Function to check if a subject code exists in the database
function isSubjectCodeExists($conn, $subject_code)
{
    $check_sql = "SELECT COUNT(*) AS count FROM subjects WHERE subject_code = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $subject_code);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return ($row["count"] > 0);
}

// Function to check if a subject title exists in the database
function isSubjectTitleExists($conn, $subject_title)
{
    $check_sql = "SELECT COUNT(*) AS count FROM subjects WHERE subject_title = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $subject_title);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return ($row["count"] > 0);
}

// Check if the form has been submitted to add a subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["subject_code"]) && isset($_POST["subject_title"]) && isset($_POST["designated_department"])) {
    // Get the selected values from the form
    $subject_code = $_POST["subject_code"];
    $subject_title = $_POST["subject_title"];
    $designated_department = $_POST["designated_department"];

    // TODO: Sanitize and validate the input data before inserting into the database

    // Check if the subject code already exists in the database
    if (isSubjectCodeExists($conn, $subject_code)) {
        echo "<script>alert('Subject code already exists.');</script>";
    } elseif (isSubjectTitleExists($conn, $subject_title)) {
        echo "<script>alert('Subject title already exists.');</script>";
    } else {
        // Insert the subject into the database
        $insert_sql = "INSERT INTO subjects (subject_code, subject_title, department) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $subject_code, $subject_title, $designated_department);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Subject added successfully.');</script>";
        } else {
            echo "<script>alert('Error adding subject: " . $insert_stmt->error . "');</script>";
        }

        $insert_stmt->close();
    }
}

// Delete subject if the delete button is clicked
if (isset($_POST["delete_subject_code"])) {
    $subject_code = $_POST["delete_subject_code"];
    if (deleteSubject($conn, $subject_code)) {
        echo "<script>alert('Subject deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting subject.');</script>";
    }
}

// Retrieve and display the subjects in a table
$retrieve_sql = "SELECT subject_code, subject_title, department FROM subjects";
$result = $conn->query($retrieve_sql);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/subjects.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Add Subject</title>
</head>

<body>
<?php include 'nav.php';?>
    <div class="container">
        <h2>Add <span>Subject</span></h2>
        <form action="subject.php" method="POST">
            <div class="mb-3">
                <label for="subject_code" class="form-label">Subject Code</label>
                <input type="text" class="form-control" name="subject_code" id="subject_code" required>
            </div>
            <div class="mb-3">
                <label for="subject_title" class="form-label">Subject Title</label>
                <input type="text" class="form-control" name="subject_title" id="subject_title" required>
            </div>
            <div class="mb-3">
                <label for="designated_department" class="form-label">Designated Department</label>
                <select class="form-select" name="designated_department" id="designated_department">
                    <option value="BSCS">(BSCS) BS Computer Science</option>
                    <option value="BEED">(BEED) BE Elementary Education</option>
                    <option value="BSOA">(BSOA) BS Office Administration</option>
                    <option value="BSED">(BSED) BS Secondary Education</option>
                    <option value="BSBA">(BSBA) BS Business Administration</option>
                    <option value="ABREED">(ABREED) AB Religious Education</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Subject</button>
        </form>
    </div>

    <div class="container mt-5 text-center">
        <h2 class="files">Subj<span>ects</span></h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Subject Code</th>
                    <th>Subject Title</th>
                    <th>Designated Department</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Display the subjects in the table
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <tr>
                            <td><?php echo $row['subject_code']; ?></td>
                            <td><?php echo $row['subject_title']; ?></td>
                            <td><?php echo $row['department']; ?></td>
                            <td>
                                <form method="post" action="">
                                    <input type="hidden" name="delete_subject_code" value="<?php echo $row['subject_code']; ?>">
                                    <!-- Add an onclick attribute to trigger the confirmation dialog -->
                                    <button type="button" class="btn btn-danger mx-auto" onclick="confirmDelete('<?php echo $row['subject_code']; ?>')">Delete</button>
                                </form>
                            </td>
                        </tr>
                <?php
                    }
                } else {
                ?>
                    <tr>
                        <td colspan="4">No subjects added yet.</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
    <script>
        function confirmDelete(subject_code) {
            if (confirm("Are you sure you want to delete this subject?")) {
                // If the user confirms, submit the form to delete the subject
                var form = document.createElement("form");
                form.method = "post";
                form.action = "";
                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "delete_subject_code";
                input.value = subject_code;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>

</html>

<?php
$conn->close();
?>
