const button = document.getElementById("generateButton");
const container = document.getElementById("workoutList");

button.addEventListener("click", loadWorkout);

async function loadWorkout() {

    container.textContent = "Generating your workout...";

    try {

        const response = await fetch("api/workout.php");
        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Unable to generate workout");
        }

        container.replaceChildren();

        result.plan.forEach(day => {

            const card = document.createElement("div");
            card.className = "item-card";

            const title = document.createElement("h3");
            title.textContent = day.day + " - " + day.title;

            const warmup = document.createElement("p");
            warmup.textContent = "Warm-up: " + day.warmup;

            card.append(title, warmup);

            day.exercises.forEach(exercise => {

                const name = document.createElement("h4");
                name.textContent = exercise.name;

                const details = document.createElement("p");
                details.textContent =
                    exercise.sets + " sets × " +
                    exercise.reps + " | Rest: " +
                    exercise.rest;

                card.append(name, details);

            });

            const cooldown = document.createElement("p");
            cooldown.textContent = "Cool-down: " + day.cooldown;

            card.append(cooldown);
            container.appendChild(card);

        });

    } catch (error) {

        container.textContent = error.message;

    }
}
