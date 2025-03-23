<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management (Member)</title>
    <link rel="stylesheet" href="TMS_Member.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <div class="container">
            <h2>Task Management (Member View)</h2>
            <table id="taskTable">
                <tr>
                    <th>Task</th>
                    <th>Owner</th>
                    <th>Progress</th>
                    <th>Due Date</th>
                </tr>
            </table>
        </div>
    </div>

    <script src="TMS_Member.js"></script>
    <script src="sidebar.js"></script>
</body>
</html>
