<?php
session_start();
require_once 'db_connect.php';

// Function to check database connection
function checkDatabaseConnection() {
    global $conn;
    
    if ($conn->connect_error) {
        echo "Database Connection Failed: " . $conn->connect_error . "<br>";
        return false;
    }
    
    echo "Database Connection Successful!<br>";
    return true;
}

// Function to check table existence
function checkTableExistence($tableName) {
    global $conn;
    
    $query = "SHOW TABLES LIKE '$tableName'";
    $result = $conn->query($query);
    
    if ($result === false) {
        echo "Error checking table $tableName: " . $conn->error . "<br>";
        return false;
    }
    
    if ($result->num_rows > 0) {
        echo "Table $tableName exists.<br>";
        
        // Show table structure
        $structureQuery = "DESCRIBE $tableName";
        $structureResult = $conn->query($structureQuery);
        
        echo "Table Structure for $tableName:<br>";
        echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        while ($row = $structureResult->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $key => $value) {
                echo "<td>" . htmlspecialchars($value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br>";
        
        return true;
    } else {
        echo "Table $tableName does not exist.<br>";
        return false;
    }
}

// Check database connection
checkDatabaseConnection();

// List of tables to check
$tablesToCheck = ['category', 'task', 'tracker', 'task_categories'];

foreach ($tablesToCheck as $table) {
    checkTableExistence($table);
}

// If logged in, try to fetch some sample data
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $userId = $_SESSION['user_id'];
    
    // Try to fetch tasks
    $taskQuery = "SELECT * FROM task WHERE creator_id = ? LIMIT 5";
    $stmt = $conn->prepare($taskQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "Sample Tasks:<br>";
    echo "<table border='1'><tr><th>Task ID</th><th>Task Name</th><th>Category ID</th><th>Status</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['task_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['task_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

$conn->close();
?>