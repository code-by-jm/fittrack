const progressForm = document.getElementById("progressForm");

progressForm.addEventListener("submit", async function(e) {

    e.preventDefault();

    const weight = document.getElementById("weight").value;

    try {

        const response = await fetch("api/progress.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ weight: weight })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error);
        }

        document.getElementById("progressMessage").textContent =
            result.message;

        progressForm.reset();

        loadProgress();

    } catch (error) {

        document.getElementById("progressMessage").textContent =
            error.message;

    }

});

async function loadProgress() {

    const container = document.getElementById("progressList");

    try {

        const response = await fetch("api/progress.php");
        const records = await response.json();

        if (!response.ok) {
            throw new Error("Unable to load progress.");
        }

        container.replaceChildren();

        records.forEach(record => {

            const card = document.createElement("div");
            card.className = "item-card";

            const weight = document.createElement("h3");
            weight.textContent = record.weight + " kg";

            const date = document.createElement("p");
            date.textContent = record.record_date;

            card.append(weight, date);
            container.appendChild(card);

        });

        if (records.length === 0) {
            container.textContent = "No progress records yet.";
        }

    } catch (error) {

        container.textContent = error.message;

    }
}

loadProgress();
