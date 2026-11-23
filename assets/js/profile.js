const profileForm = document.getElementById("profileForm");

profileForm.addEventListener("submit", async function(e) {
    e.preventDefault();

    const message = document.getElementById("profileMessage");

    const data = {
        name: document.getElementById("name").value,
        age: document.getElementById("age").value,
        sex: document.getElementById("sex").value,
        height: document.getElementById("height").value,
        weight: document.getElementById("weight").value,
        activity_level: document.getElementById("activity_level").value,
        fitness_goal: document.getElementById("fitness_goal").value,
        workout_days: document.getElementById("workout_days").value
    };

    message.textContent = "Saving your profile...";

    try {
        const response = await fetch("api/profile.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Unable to save profile");
        }

        message.textContent = result.message;
        message.style.color = "green";

        displayTargets(result.targets);

        // First-time profile setup goes to dashboard
        setTimeout(() => {
            window.location.href = "index.html";
        }, 1200);

    } catch (error) {
        message.textContent = error.message;
        message.style.color = "red";
    }
});

function displayTargets(targets) {
    document.getElementById("targets").hidden = false;

    document.getElementById("bmr").textContent =
        "BMR: " + targets.bmr + " kcal";

    document.getElementById("tdee").textContent =
        "Maintenance Calories: " + targets.tdee + " kcal";

    document.getElementById("calories").textContent =
        "Daily Calories: " + targets.daily_calories + " kcal";

    document.getElementById("protein").textContent =
        "Daily Protein: " + targets.daily_protein + " g";
}

// Load existing profile when editing
async function loadProfile() {
    try {
        const response = await fetch("api/profile.php");
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Unable to load profile");
        }

        if (!result.profile) return;

        const p = result.profile;

        document.getElementById("name").value = p.name;
        document.getElementById("age").value = p.age;
        document.getElementById("sex").value = p.sex;
        document.getElementById("height").value = p.height;
        document.getElementById("weight").value = p.weight;
        document.getElementById("activity_level").value = p.activity_level;
        document.getElementById("fitness_goal").value = p.fitness_goal;
        document.getElementById("workout_days").value = p.workout_days;

        displayTargets(result.targets);

    } catch (error) {
        console.error(error);
    }
}

loadProfile();
