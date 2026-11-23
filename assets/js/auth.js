async function checkAuthentication() {
    try {
        const response = await fetch("api/auth.php");
        const result = await response.json();

        const page = window.location.pathname.split("/").pop();

        const publicPages = ["login.html", "register.html"];

        if (!result.logged_in) {
            if (!publicPages.includes(page)) {
                window.location.replace("login.html");
            }
            return;
        }

        if (publicPages.includes(page)) {
            window.location.replace(
                result.has_profile ? "index.html" : "profile.html"
            );
            return;
        }

        if (page === "profile.html") {
            // Allow profile page for both first-time setup and editing.
            return;
        }

        if (!result.has_profile && page !== "profile.html") {
            window.location.replace("profile.html");
        }

    } catch (error) {
        console.error("Authentication error:", error);
    }
}

checkAuthentication();

async function logout() {
    try {
        const response = await fetch("api/auth.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ action: "logout" })
        });

        if (!response.ok) {
            throw new Error("Unable to logout");
        }

        window.location.replace("login.html");

    } catch (error) {
        alert(error.message);
    }
}
