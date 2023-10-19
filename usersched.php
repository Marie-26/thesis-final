<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/sched.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.1/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <title>Schedule Search</title>
</head>

<body>
      <?php include 'usernav.php'; ?>
    <div class="center-container">
        <div class="Card">
            <div class="CardInner">
                <h2>My <span>Schedule</span></h2>
                <div class="container">
                    <div class="Icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="#657789" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
                            class="feather feather-search">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                    </div>
                    <div class="InputContainer">
                        <form action="profsched.php" method="POST" target="_blank">
                            <div class="mb-3">
                                <label for="professor" class="form-label">Select Professor</label>
                                <select class="form-select" name="professor" id="professor">
                                    <?php
                                    session_start();

                                    if (isset($_SESSION["salutation"]) && isset($_SESSION["firstname"]) && isset($_SESSION["lastname"])) {
                                        $professor_option = $_SESSION["salutation"] . " " . $_SESSION["firstname"] . " " . $_SESSION["lastname"];
                                        echo "<option value='" . $professor_option . "'>" . $professor_option . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Search</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>