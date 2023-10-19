<?php
session_start();

include 'dbconn.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["class"])) {
    $selectedClass = $_POST["class"];

    // Create a database connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to fetch schedules for the selected class
    $schedule_query = "SELECT day_of_week, timeframe, professor, subject FROM scheduler WHERE section = ?";
    $stmt = $conn->prepare($schedule_query);
    $stmt->bind_param("s", $selectedClass);
    $stmt->execute();
    $result = $stmt->get_result();

    $conn->close();
}

// Initialize an array to hold the schedule data
$scheduleData = [];

// Populate the scheduleData array with professor and subject data
if (isset($result)) {
    while ($row = $result->fetch_assoc()) {
        $dayOfWeek = $row["day_of_week"];
        $timeframe = $row["timeframe"];
        $professor = $row["professor"];
        $subject = $row["subject"];
        $scheduleData[$timeframe][$dayOfWeek] = "<div class='professor'>" . $professor . "</div><div class='subject'>" . $subject . "</div>";
    }
}

// Fetch the active term, school year, and semester from the settings table
$activeTerm = "";
$activeSchoolYear = "";
$activeSemester = "";
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$activeTermQuery = "SELECT term, school_year, semester FROM settings WHERE status = 'active' LIMIT 1";
$activeTermResult = $conn->query($activeTermQuery);
if ($activeTermResult->num_rows > 0) {
    $activeData = $activeTermResult->fetch_assoc();
    $activeTerm = $activeData["term"];
    $activeSchoolYear = $activeData["school_year"];
    $activeSemester = $activeData["semester"];
}
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
    <title>Class Schedule</title>
    <style>
        .schedule-table {
            border-collapse: collapse;
            width: 100%;
            border: 1px solid black;
            text-align: center;
            padding: 5px;
        }

        .schedule-table th,
        .schedule-table td {
            border: 1px solid black;
            text-align: center;
            padding: 5px;
            vertical-align: middle;
        }

        .schedule-table th:nth-child(1),
        .schedule-table td:nth-child(1) {
            width: 15%;
        }

        .schedule-table th:nth-child(2),
        .schedule-table td:nth-child(2) {
            width: 15%;
        }

        .schedule-table th:nth-child(3),
        .schedule-table td:nth-child(3) {
            width: 15%;
        }

        .schedule-table th:nth-child(4),
        .schedule-table td:nth-child(4) {
            width: 15%;
        }

        .schedule-table th:nth-child(5),
        .schedule-table td:nth-child(5) {
            width: 15%;
        }

        .schedule-table th:nth-child(6),
        .schedule-table td:nth-child(6) {
            width: 15%;
        }

        .schedule-table th:nth-child(7),
        .schedule-table td:nth-child(7) {
            width: 15%;
        }

        .schedule-table tbody tr {
            height: 60px;
        }

        .professor {
            margin-bottom: 5px;
        }

        h5 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h5>CLASS SCHEDULE</h5>
        <?php
        if (isset($selectedClass)) {
            echo '<h5>Section: ' . htmlspecialchars($selectedClass) . '</h5>';
            echo '<h5>Semester: ' . htmlspecialchars($activeSemester) . '</h5>';
            echo '<h5>School Year: ' . htmlspecialchars($activeSchoolYear) . '</h5>';
            echo '<table class="table table-bordered schedule-table">';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Timeframe</th>';
            echo '<th>Monday</th>';
            echo '<th>Tuesday</th>';
            echo '<th>Wednesday</th>';
            echo '<th>Thursday</th>';
            echo '<th>Friday</th>';
            echo '<th>Saturday</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            $timeframes = array(
                "8am-9am",
                "9am-10am",
                "10am-11am",
                "11am-12pm",
                "12pm-1pm",
                "1pm-2pm",
                "2pm-3pm",
                "3pm-4pm"
            );

            foreach ($timeframes as $timeframe) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($timeframe) . '</td>';
                foreach (array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') as $dayOfWeek) {
                    echo '<td>';
                    if (isset($scheduleData[$timeframe][$dayOfWeek])) {
                        echo $scheduleData[$timeframe][$dayOfWeek];
                    }
                    echo '</td>';
                }
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<p>Please select a class to view its schedule.</p>';
        }
        ?>
    </div>
    <div style="margin-left: 10%; margin-top: 20px;">
        Prepared by:<br><br>
        Recommending Approval:<br><br>
        Approved:
    </div>
</body>

</html>
