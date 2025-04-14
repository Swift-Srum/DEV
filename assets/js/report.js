document.getElementById("reportAreaBtn").addEventListener("click", function () {
    showForm("area");
});

document.getElementById("reportBowserBtn").addEventListener("click", function () {
    showForm("bowser");
});

function showForm(type) {
    const formContainer = document.getElementById("formContainer");
    formContainer.style.display = "block";

    if (type === "area") {
        formContainer.innerHTML = `
            <h2>Report Area</h2>
            <form action="/report/submit_area.php" method="POST">
                <label for="postcode">Postcode:</label>
                <input type="text" id="postcode" name="postcode" required>
                
                <label for="report">Report Details:</label>
                <textarea id="report" name="report" rows="5" required></textarea>
                
                <label for="reportType">Urgency:</label>
                <select id="reportType" name="reportType" required>
                    <option value="Urgent">Urgent</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
                
                <button type="submit">Submit Report</button>
            </form>
        `;
    } else if (type === "bowser") {
        formContainer.innerHTML = `
            <h2>Report Bowser</h2>
            <form action="/report/submit_bowser.php" method="POST">
                <label for="bowserId">Bowser ID:</label>
                <input type="text" id="bowserId" name="bowserId" required>
                
                <label for="report">Report Details:</label>
                <textarea id="report" name="report" rows="5" required></textarea>
                
                <label for="typeOfReport">Urgency:</label>
                <select id="typeOfReport" name="typeOfReport" required>
                    <option value="Urgent">Urgent</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                </select>
                
                <button type="submit">Submit Report</button>
            </form>
        `;
    }
}