<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/userupload.css">
    <title>Syllabus Safe</title>
</head>

<body>
<?php include 'usernav.php';?>

    <div class="container">
        <h2>Upload <span>Syllabus</span></h2>
        <form action="upload.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="file" class="form-label">Select file</label>
                <input type="file" class="form-control" name="file" id="file">
                <small class="form-text text-muted">Allowed file extensions: .jpg, .jpeg, .png, .gif, .pdf, .doc, .docx</small>
            </div>
            <div class="mb-3">
                <label for="subject_title" class="form-label">Subject Title</label>
                <input type="text" class="form-control" name="subject_title" id="subject_title">
            </div>
            <div class="mb-3">
                <label for="course" class="form-label">Course</label>
                <select class="form-select" name="course" id="course">
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
                <label for="year" class="form-label">Year</label>
                <select class="form-select" name="year" id="year">
                    <option value="" disabled selected></option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="semester" class="form-label">Semester</label>
                <select class="form-select" name="semester" id="semester">
                    <option value="" disabled selected></option>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Upload file</button>
        </form>
    </div>
    <script>
        // Enable Bootstrap dropdown
        $(document).ready(function () {
            $(".dropdown-toggle").dropdown();
        });
    </script>
</body>

</html>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if a file was uploaded without errors
    if (isset($_FILES["file"]) && $_FILES["file"]["error"] == 0) {
        $target_dir = "uploads/"; // Change this to the desired directory for uploaded files
        $target_file = $target_dir . basename($_FILES["file"]["name"]);
        $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if the file is allowed (you can modify this to allow specific file types)
        $allowed_types = array("jpg", "jpeg", "png", "gif", "pdf", "doc", "docx");
        if (!in_array($file_type, $allowed_types)) {
            echo '<script>
                    alert("Sorry, only JPG, JPEG, PNG, GIF, PDF, DOC, and DOCX files are allowed.");
                    window.location.href = "upload.php";
                </script>';
            exit();
        } else {
            // Check if a file with the same name already exists in the target directory
            if (file_exists($target_file)) {
                echo '<script>
                        alert("Sorry, a file with the same name already exists in the target directory. Please choose a different file name.");
                        window.location.href = "upload.php";
                    </script>';
                exit();
            } else {
                // Move the uploaded file to the specified directory
                if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                    // File upload success, now store information in the database
                    $filename = $_FILES["file"]["name"];
                    $filesize = $_FILES["file"]["size"];
                    $filetype = $_FILES["file"]["type"];

                    // Additional fields from the form
                    $subject_title = $_POST['subject_title'];
                    $course = $_POST['course'];
                    $year = $_POST['year'];
                    $semester = $_POST['semester'];

                    include 'dbconn.php';

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Check if a file with the same name already exists in the database
                    $sql_check = "SELECT filename FROM syllabus WHERE filename = '$filename'";
                    $result_check = $conn->query($sql_check);

                    if ($result_check->num_rows > 0) {
                        echo '<script>
                                alert("Sorry, a file with the same name already exists in the database. Please choose a different file name.");
                                window.location.href = "upload.php";
                            </script>';
                        exit();
                    } else {
                        // Insert the file information along with additional information into the database
                        $sql = "INSERT INTO syllabus (filename, filesize, filetype, subject_title, course, year, semester) 
                                VALUES ('$filename', $filesize, '$filetype', '$subject_title', '$course', '$year', '$semester')";

                        if ($conn->query($sql) === TRUE) {
                            echo '<script>
                                    alert("The file ' . basename($_FILES["file"]["name"]) . ' has been uploaded, and the information has been stored in the database.");
                                    setTimeout(function() {
                                        window.location.href = "upload.php";
                                    }, 1000); // Redirect after 1 second
                                </script>';
                            exit();
                        } else {
                            echo '<script>
                                    alert("Sorry, there was an error uploading your file and storing information in the database: ' . $conn->error . '");
                                    window.location.href = "upload.php";
                                </script>';
                            exit();
                        }
                    }

                    $conn->close();
                } else {
                    echo '<script>
                            alert("Sorry, there was an error uploading your file.");
                            window.location.href = "upload.php";
                        </script>';
                    exit();
                }
            }
        }
    } else {
        echo '<script>
                alert("No file was uploaded.");
                window.location.href = "upload.php";
            </script>';
        exit();
    }
}
?>
