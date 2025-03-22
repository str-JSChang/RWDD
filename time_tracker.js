let timer;
let elapsedTime = 0;
let running = false;
let pauseCount = 0;
const pauseLimit = 3;

function StartTimer() {
    if (!running) {
        running = true;
        document.getElementById("pauseButton").disabled = false;
        document.getElementById("resetButton").disabled = false;

        timer = setInterval(() => {
            elapsedTime++;
            document.getElementById('timer').textContent = new Date(elapsedTime * 1000).toISOString().substr(11, 8);
        }, 1000);
    }
}

function PauseTimer() {
    if (pauseCount < pauseLimit) {
        clearInterval(timer);
        running = false;
        pauseCount++;

        if (pauseCount >= pauseLimit) {
            document.getElementById("pauseButton").disabled = true;
        }
    } else {
        alert("Pause limit reached");
    }
}

function ResetTimer() {
    clearInterval(timer);
    running = false;
    elapsedTime = 0;
    pauseCount = 0;
    
    document.getElementById('timer').textContent = "00:00:00";
    document.getElementById("pauseButton").disabled = true;
    document.getElementById("resetButton").disabled = true;
}

function toggleWhiteNoise() {
    let audio = document.getElementById("whiteNoise");
    
    if (audio.paused) {
        audio.play();
    } else {
        audio.pause();
    }
}
