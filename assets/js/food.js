const foodForm = document.getElementById("foodForm");
const foodSelect = document.getElementById("food_id");

async function loadFoods() {

    try {

        const response = await fetch("api/food.php?action=foods");
        const foods = await response.json();

        foodSelect.replaceChildren();

        const placeholder = document.createElement("option");
        placeholder.value = "";
        placeholder.textContent = "Select Food";
        foodSelect.appendChild(placeholder);

        foods.forEach(food => {

            const option = document.createElement("option");

            option.value = food.id;
            option.textContent =
                `${food.food_name} (${food.calories} kcal, ${food.protein}g protein)`;

            foodSelect.appendChild(option);

        });

    } catch (error) {

        foodSelect.textContent = "Unable to load foods.";

    }
}

foodForm.addEventListener("submit", async function(e) {

    e.preventDefault();

    const data = {
        food_id: foodSelect.value,
        quantity: document.getElementById("quantity").value
    };

    const message = document.getElementById("foodMessage");

    try {

        const response = await fetch("api/food.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error);
        }

        message.textContent = result.message;
        message.style.color = "green";

        foodForm.reset();

        await loadLogs();

    } catch (error) {

        message.textContent = error.message;
        message.style.color = "red";

    }

});

async function loadLogs() {

    const container = document.getElementById("foodLogs");

    try {

        const response = await fetch("api/food.php?action=logs");
        const logs = await response.json();

        if (!response.ok) {
            throw new Error("Unable to retrieve food logs.");
        }

        container.replaceChildren();

        let calories = 0;
        let protein = 0;

        logs.forEach(food => {

            calories += Number(food.total_calories);
            protein += Number(food.total_protein);

            const card = document.createElement("div");
            card.className = "item-card";

            const name = document.createElement("h3");
            name.textContent = food.food_name;

            const serving = document.createElement("p");
            serving.textContent = "Servings: " + food.quantity;

            const details = document.createElement("p");
            details.textContent =
                Number(food.total_calories).toFixed(1) +
                " kcal | " +
                Number(food.total_protein).toFixed(1) + "g protein";

            card.append(name, serving, details);
            container.appendChild(card);

        });

        if (logs.length === 0) {
            container.textContent = "No food recorded today.";
        }

        document.getElementById("totalCalories").textContent =
            "Calories: " + calories.toFixed(1) + " kcal";

        document.getElementById("totalProtein").textContent =
            "Protein: " + protein.toFixed(1) + " g";

    } catch (error) {

        container.textContent = error.message;

    }
}

loadFoods();
loadLogs();
