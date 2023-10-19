<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/schedule.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Schedule Search</title>
</head>

<body>
    <?php include 'nav.php'; ?>
    <div class="container">
        <h2>Professor <span>Schedule</span></h2>
        <form action="profsched.php" method="POST" target="_blank">
            <div class="mb-3">
                <label for="professor" class="form-label">Select Professor</label>
                <select class="form-select" name="professor" id="professor">
                    <?php
                    include 'dbconn.php';

                    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $professor_query = "SELECT DISTINCT professor FROM scheduler";
                    $result = $conn->query($professor_query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $professor_name = $row["professor"];
                            echo "<option value='" . $professor_name . "'>" . $professor_name . "</option>";
                        }
                    }

                    $conn->close();
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <div class="container mt-5">
        <h2>Class <span>Schedule</span></h2>
        <form action="classsched.php" method="POST" target="_blank">
            <div class="mb-3">
                <label for="class" class="form-label">Select Class</label>
                <select class="form-select" name="class" id="class">
                    <?php
                    include 'dbconn.php';

                    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $class_query = "SELECT DISTINCT section FROM scheduler";
                    $result = $conn->query($class_query);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $class_section = $row["section"];
                            echo "<option value='" . $class_section . "'>" . $class_section . "</option>";
                        }
                    }

                    $conn->close();
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
</body>

</html>
