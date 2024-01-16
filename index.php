<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PTR Checker</title>
</head>
<body>
    <h2>PTR Checker</h2>

    <form action="index.php" method="post">
        <label for="ip_address">IP Address:</label>
        <input type="text" name="ip_address" required>
        <button type="submit" name="add_record">Check PTR</button>
    </form>

    <?php
    $db_host = "localhost";
    $db_user = "root";
    $db_password = "";
    $db_name = "ping";

    $conn = mysqli_connect($db_host, $db_user, $db_password, $db_name);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    $message = "";
    $delete_message = "";

    function getPTR($ip_address) {
        $ptr_data = gethostbyaddr($ip_address);
        return $ptr_data;
    }

    function addRecord($conn, $ip_address, $ptr_data) {
        $sql = "INSERT INTO ptr_records (ip_address, ptr_data) VALUES ('$ip_address', '$ptr_data')";
        if (mysqli_query($conn, $sql)) {
            return "PTR Record added successfully! PTR Data: $ptr_data";
        } else {
            return "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    function deleteRecord($conn, $id) {
        $sql = "DELETE FROM ptr_records WHERE id = $id";
        if (mysqli_query($conn, $sql)) {
            return "Record with ID $id deleted successfully.";
        } else {
            return "Error deleting record: " . mysqli_error($conn);
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["add_record"])) {
            $ip_address = $_POST["ip_address"];
            $ptr_data = getPTR($ip_address);
            $message = addRecord($conn, $ip_address, $ptr_data);
        } elseif (isset($_POST["delete_record"])) {
            $delete_id = $_POST["delete_id"];
            $delete_message = deleteRecord($conn, $delete_id);
        }
    }

    $sql = "SELECT * FROM ptr_records";
    $result = mysqli_query($conn, $sql);

    $ptr_records = array();

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $ptr_records[] = $row;
        }
    }

    mysqli_close($conn);
    ?>

    <?php
    if (!empty($message)) {
        echo "<p>$message</p>";
    }

    if (!empty($ptr_records)) {
        echo "<h3>Existing PTR Records:</h3>";
        echo "<ul>";
        foreach ($ptr_records as $record) {
            echo "<li>ID: {$record['id']} - {$record['ip_address']} - {$record['ptr_data']} 
                  [<a href=\"javascript:void(0);\" onclick=\"deleteRecord({$record['id']})\">Delete</a>]</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No PTR records found.</p>";
    }
    ?>

    <h3>Delete Record by ID</h3>
    <form action="index.php" method="post">
        <label for="delete_id">Enter ID to Delete:</label>
        <input type="text" name="delete_id" required>
        <button type="submit" name="delete_record">Delete Record</button>
    </form>

    <?php
    if (!empty($delete_message)) {
        echo "<p>$delete_message</p>";
    }
    ?>

    <script>
        function deleteRecord(id) {
            var confirmDelete = confirm("Are you sure you want to delete this record?");
            if (confirmDelete) {
                document.querySelector('input[name="delete_id"]').value = id;
                document.querySelector('button[name="delete_record"]').click();
            }
        }
    </script>
</body>
</html>
