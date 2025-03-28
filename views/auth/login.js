//^.................................................................../
//* Frontend :
//^.................................................................../

gsap.from(".summon", { opacity: 0, y: 50, duration: 1, ease: "power2.out" });

function showErrorPane(txt) {
    gsap.killTweensOf("#errorPane");
    gsap.set("#errorPane", { clearProps: "all" });
    document.getElementById("errorMsg").textContent = txt
    gsap.fromTo("#errorPane", { opacity: 0, x: 50, duration: 1, ease: "elastic.out" }, { opacity: 1, x: 0, duration: 1, ease: "elastic.out" });
    document.getElementById("errorPane").classList.remove("hidden")
    setTimeout(() => {
        hideErrorPane()
    }, 5000);
}

function hideErrorPane() {
    gsap.killTweensOf("#errorPane");
    gsap.set("#errorPane", { clearProps: "all" });
    gsap.to("#errorPane", { opacity: 0, x: 50, duration: 0.8, ease: "elastic.in" });
    setTimeout(() => {
        document.getElementById("errorPane").classList.add("hidden")
    }, 1000);
}

document.getElementById("closeError").onclick = () => document.getElementById("errorPane").classList.add("hidden");

//^.................................................................../
//* Backend :
//^.................................................................../

document.querySelector("form").addEventListener("submit", function (e) {
    e.preventDefault();
    
    const formData = {
        email: document.querySelector("input[type='email']").value.trim(),
        password: document.querySelector("input[type='password']").value
    };

    if (!formData.email || !formData.password) {
        showErrorPane("All fields are required.");
        return;
    }

    fetch("../../controllers/login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            window.parent.location.href = "../home/";
        } else {
            showErrorPane(data.message);
            console.log(data.message);
        }
    })
    .catch(error => {
        console.error("Error:", error);
        showErrorPane("Server error. Try again later.");
    });
});

//! GOOGLE LOGIN *************************************

window.addEventListener("DOMContentLoaded", () => {
    document.getElementById("googleBlackBtn").onclick = () => {
        google.accounts.id.prompt();
    }
})

function googleLogin(response) {
    console.log("Google Response:", response);
    
    fetch("../../controllers/google.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ token: response.credential })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            localStorage.setItem("user", JSON.stringify(data.user));
            window.parent.location.href = "../home/"
        } else {
            showErrorPane(data.message);
            console.log(data.message);
        }
    })
    .catch(error => {
        console.error("Google Login Error:", error);
        showErrorPane("Google Login failed. Try again.");
    });
}

//^.................................................................../
//* Security :
//^.................................................................../

if (document.cookie.includes("banned=true")) {
    window.location.href = "banned.html";
}

function detectHack(input) {
    const patterns = [/select/i, /insert/i, /update/i, /delete/i, /drop/i, /union/i, /--/i, /<script>/i, /javascript:/i, /onerror=/i, /alert\(/i, /\.\.\//i, /etc\/passwd/i, /file:\/\//i, /cmd=/i, /wget /i, /curl /i];
    return patterns.some(pattern => pattern.test(input));
}

function monitorInputs() {
    const emailInput = document.querySelector("input[type='email']");
    const passwordInput = document.querySelector("input[type='password']");

    if (emailInput) {
        emailInput.addEventListener("input", (e) => {
            if (detectHack(e.target.value)) {
                banUser();
            }
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener("input", (e) => {
            if (detectHack(e.target.value)) {
                banUser();
            }
        });
    }
}

function banUser() {
    document.cookie = "banned=true; path=/; max-age=" + (365 * 24 * 60 * 60);
    window.parent.location.href = "banned.html";
}

monitorInputs();