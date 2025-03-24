<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Tracker</title>
    <link rel="stylesheet" href="time_tracker.css">
    <link rel="stylesheet" href="sidebar.css">
</head>
<body>
    <video autoplay muted loop class="background">
        <source src="background/video.mp4" type="video/mp4">
    </video>

    <div class="overlay"></div>
    <!-- include it here so it render first. -->
    <?php include 'sidebar.php'; ?>


    <div class="main-content" id="mainContent">
        <h1>Time Tracker</h1>
        <div class="timer" id="timer">00:00:00</div>

        <div class="buttons">
            <button id="startButton" onclick="StartTimer()">Start</button>
            <button id="pauseButton" onclick="PauseTimer()" disabled>Pause</button>
            <button id="resetButton" onclick="ResetTimer()" disabled>Reset</button>
        </div>

        <button id="whiteNoiseButton" onclick="toggleWhiteNoise()">Toggle White Noise</button>

        <audio id="whiteNoise" loop>
            <source src="https://www.soundjay.com/nature/rain-01.mp3" type="audio/mpeg">
        </audio>
    </div>

    <script src="time_tracker.js"></script>
    <script src="sidebar.js"></script>
    
</body>
</html>
