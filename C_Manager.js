function addComment() {
    let comment = document.getElementById("commentInput").value;
    if (comment) {
        let list = document.getElementById("commentList");
        let item = document.createElement("li");
        item.innerHTML = comment + ' <button class="delete-btn" onclick="this.parentElement.remove()">Delete</button>';
        list.appendChild(item);
        document.getElementById("commentInput").value = "";
    }
}

function uploadFile() {
    let fileInput = document.getElementById("fileUpload");
    let file = fileInput.files[0];

    if (file) {
        let fileURL = URL.createObjectURL(file);
        let fileList = document.getElementById("fileList");

        let listItem = document.createElement("li");
        listItem.innerHTML = `
            <span>${file.name}</span>
            <div class="file-actions">
                <button class="open-btn" onclick="openFile('${fileURL}')">Open</button>
                <button class="delete-btn" onclick="this.parentElement.parentElement.remove()">Delete</button>
            </div>
        `;

        fileList.appendChild(listItem);
    }
}

function openFile(fileURL) {
    window.open(fileURL, "_blank");
}

function addTask() {
    let task = document.getElementById("taskInput").value;
    if (task) {
        let list = document.getElementById("taskList");
        let item = document.createElement("li");
        item.innerHTML = task + ' <button class="delete-btn" onclick="this.parentElement.remove()">Remove</button>';
        list.appendChild(item);
        document.getElementById("taskInput").value = "";
    }
}

function addMember() {
    let member = document.getElementById("memberInput").value;
    if (member) {
        let list = document.getElementById("memberList");
        let item = document.createElement("li");
        item.innerHTML = member + ' <button class="delete-btn" onclick="this.parentElement.remove()">Remove</button>';
        list.appendChild(item);
        document.getElementById("memberInput").value = "";
    }
}

function scheduleMeeting() {
    let meetingTime = document.getElementById("meetingTime").value;
    if (meetingTime) {
        let formattedTime = new Date(meetingTime).toLocaleString();
        document.getElementById("meetingScheduled").innerText = "Meeting scheduled for: " + formattedTime;
    }
}
