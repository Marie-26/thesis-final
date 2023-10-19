<?php
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["course"]) && isset($_POST["year"]) && isset($_POST["section"])) {
    // Get the selected values from the form
    $course = $_POST["course"];
    $year = $_POST["year"];
    $section = $_POST["section"];

    include 'dbconn.php';

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $check_sql = "SELECT COUNT(*) AS count FROM sections WHERE course = ? AND school_year = ? AND section = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("sss", $course, $year, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row["count"] > 0) {
        echo "<script>alert('Section already exists.');</script>";
    } else {
        // Insert the section into the database
        $insert_sql = "INSERT INTO sections (course, school_year, section) VALUES (?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sss", $course, $year, $section);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Section added successfully.');</script>";
        } else {
            echo "<script>alert('Error adding section: " . $insert_stmt->error . "');</script>";
        }

        $insert_stmt->close();
    }

    $stmt->close();
    $conn->close();
}

// Check if the "Delete" button is clicked for a section
if (isset($_GET["delete_id"])) {
    $delete_id = $_GET["delete_id"];

    // TODO: Validate and sanitize the input data before deleting from the database

    // Delete the section from the database
    include 'dbconn.php';

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $delete_sql = "DELETE FROM sections WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        echo "<script>alert('Section deleted successfully.');</script>";
    } else {
        echo "<script>alert('Error deleting section: " . $stmt->error . "');</script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/sections.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Manage Sections</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Add <span>Section</span></h2>
        <form action="sections.php" method="POST">
            <div class="mb-3">
                <label for="course" class="form-label">Select Course</label>
                <!-- Replace this with the list of courses from your database -->
                <select class="form-select" name="course" id="course">
                    <option value="BSCS">(BSCS) BS Computer Science</option>
                    <option value="BEED">(BEED) BE Elementary Education</option>
                    <option value="BSOA">(BSOA) BS Office Administration</option>
                    <option value="BSED">(BSED) BS Secondary Education</option>
                    <option value="BSBA">(BSBA) BS Business Administration</option>
                    <option value="ABREED">(ABREED) AB Religious Education</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">Select School Year</label>
                <select class="form-select" name="year" id="year">
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Select Section</label>
                <select class="form-select" name="section" id="section">
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                    <!-- Add other section options here -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Add Section</button>
        </form>
    </div>

    <!-- Display Sections Table -->
    <div class="container mt-5">
        <h2 class="files">Sect<span>ions</span></h2>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="text-center">Course</th>
                    <th class="text-center">School Year</th>
                    <th class="text-center">Section</th>
                    <th class="text-center">Operator</th>
                </tr>
            </thead>
            <tbody>
                <?php
                include 'dbconn.php';

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT id, course, school_year, section FROM sections";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td class='text-center'>" . $row["course"] . "</td>";
                        echo "<td class='text-center'>" . $row["school_year"] . "</td>";
                        echo "<td class='text-center'>" . $row["section"] . "</td>";
                        echo "<td class='text-center'>
                            <a href='sections.php?delete_id=" . $row["id"] . "' onclick='return confirmDelete()' class='btn btn-danger btn-sm'>Delete</a>
                          </td>";
                        echo "</tr>";
                    }
                } else {
                    // No sections found
                    echo "<tr><td colspan='4' class='text-center'>No sections found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <script>
        function confirmDelete() {
            return confirm("Do you want to proceed removing this section?");
        }
    </script>
</body>

</html>
