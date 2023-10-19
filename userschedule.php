<?php
session_start();

include 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["day_timeframe"])) {
    $professor = $_SESSION["salutation"] . " " . $_SESSION["firstname"] . " " . $_SESSION["lastname"];
    
    // Get other form data
    $dayTimeframes = $_POST["day_timeframe"];
    $section = $_POST["section"];
    $subject = $_POST["subject"];

    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $existingSchedules = [];

    $existing_schedule_query = "SELECT day_of_week, timeframe FROM scheduler WHERE section = ?";
    $existing_stmt = $conn->prepare($existing_schedule_query);
    $existing_stmt->bind_param("s", $section);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();

    // Store existing schedules in the array
    while ($row = $existing_result->fetch_assoc()) {
        $existingSchedules[$row['day_of_week']][] = $row['timeframe'];
    }

    // Check if a professor already has the same day and timeframe
    $professor_schedule_query = "SELECT day_of_week, timeframe FROM scheduler WHERE professor = ? AND day_of_week = ? AND timeframe = ?";
    $professor_stmt = $conn->prepare($professor_schedule_query);

    // Initialize an array to keep track of unavailable schedules
    $unavailableSchedules = [];

    // Initialize an array to keep track of added schedules
    $addedSchedules = [];

    // Initialize the insert statement outside the loop
    $insert_sql = "INSERT INTO scheduler (day_of_week, timeframe, professor, section, subject) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);

    // Iterate through selected timeframes and insert them into the database
    foreach ($dayTimeframes as $dayTimeframe) {
        // Split the combined value into dayOfWeek and timeframe
        list($dayOfWeek, $timeframe) = explode(", ", $dayTimeframe);

        if (isset($existingSchedules[$dayOfWeek]) && in_array($timeframe, $existingSchedules[$dayOfWeek])) {
            $unavailableSchedules[] = "$dayOfWeek at $timeframe already exists.";
            continue; 
        }

        // Check if the professor already has the same day and timeframe
        $professor_stmt->bind_param("sss", $professor, $dayOfWeek, $timeframe);
        $professor_stmt->execute();
        $professor_result = $professor_stmt->get_result();

        if ($professor_result->num_rows > 0) {
            $unavailableSchedules[] = "$dayOfWeek at $timeframe already exists.";
            continue; 
        }

        // Bind parameters and execute the statement
        $insert_stmt->bind_param("sssss", $dayOfWeek, $timeframe, $professor, $section, $subject);

        if ($insert_stmt->execute()) {
            // Schedule added successfully
            $addedSchedules[] = $dayOfWeek . " " . $timeframe;
        } else {
            echo "<script>alert('Error adding schedule: " . $insert_stmt->error . "');</script>";
        }
    }

    // Close the statement and database connection
    $insert_stmt->close();
    $professor_stmt->close();
    $conn->close();

    // Check for unavailable schedules and display an alert
    if (!empty($unavailableSchedules)) {
        $unavailableSchedulesText = implode(", ", $unavailableSchedules);
        echo "<script>alert('$unavailableSchedulesText');</script>";
    }

    // Check if any schedules were added and display an alert
    if (!empty($addedSchedules)) {
        $addedSchedulesText = implode(", ", $addedSchedules);
        echo "<script>alert('Schedules added successfully: $addedSchedulesText');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/usersched.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Scheduler</title>
</head>

<body>
    <?php include 'usernav.php'; ?>
    <div class="container">
        <h2>Sche<span>dule</span></h2>
        <form action="userschedule.php" method="POST">
            <div class="mb-3">
                <label for="user_info" class="form-label"></label>
                <h4>
                    Good Day! <?php echo $_SESSION["salutation"]; ?> <?php echo $_SESSION["firstname"]; ?> <?php echo $_SESSION["lastname"]; ?>
                </h4>
            </div>
            
                <label for="timeframe" class="form-label">Select Timeframe</label>
                <div class="mb-3 text-center">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>8am-9am</th>
                            <th>9am-10am</th>
                            <th>10am-11am</th>
                            <th>11am-12pm</th>
                            <th>12pm-1pm</th>
                            <th>1pm-2pm</th>
                            <th>2pm-3pm</th>
                            <th>3pm-4pm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Monday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Monday, 3pm-4pm"></td>
                        </tr>
                        <tr>
                            <td><strong>Tuesday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Tuesday, 3pm-4pm"></td>
                        </tr>
                        <tr>
                            <td><strong>Wednesday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Wednesday, 3pm-4pm"></td>
                        </tr>
                        <tr>
                            <td><strong>Thursday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Thursday, 3pm-4pm"></td>
                        </tr>
                        <tr>
                            <td><strong>Friday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Friday, 3pm-4pm"></td>
                        </tr>
                        <tr>
                            <td><strong>Saturday</strong></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 8am-9am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 9am-10am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 10am-11am"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 11am-12pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 12pm-1pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 1pm-2pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 2pm-3pm"></td>
                            <td><input type="checkbox" name="day_timeframe[]" value="Saturday, 3pm-4pm"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Select Subject</label>
                <select class="form-select" name="subject" id="subject">
                    <?php
                    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Query to fetch subjects from the database
                    $subject_query = "SELECT subject_code, subject_title FROM subjects";
                    $result = $conn->query($subject_query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Combine subject_code and subject_title to display in dropdown
                            $subject_option = $row["subject_code"] . " - " . $row["subject_title"];
                            echo "<option value='" . $subject_option . "'>" . $subject_option . "</option>";
                        }
                    }

                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="section" class="form-label">Select Section</label>
                <select class="form-select" name="section" id="section">
                    <?php
                    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    // Query to fetch sections from the database
                    $section_query = "SELECT course, school_year, section FROM sections";
                    $result = $conn->query($section_query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            // Combine course, school_year, and section to display in dropdown
                            $section_info = $row["course"] . " - " . $row["school_year"] . " - " . $row["section"];
                            echo "<option value='" . $section_info . "'>" . $section_info . "</option>";
                        }
                    }

                    $conn->close();
                    ?>
                </select>
            </div>


            <button type="submit" class="btn btn-primary">Add Schedule</button>
        </form>
    </div>
</body>

</html>