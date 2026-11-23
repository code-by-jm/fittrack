document.getElementById("registerForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const message = document.getElementById("message");
    message.textContent = "Creating your account...";

    try {
        const response = await fetch("api/auth.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "register",
                name: document.getElementById("name").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.error || "Registration failed.");
        }

        window.location.href = result.redirect;

    } catch (error) {
        message.textContent = error.message;
    }
});
