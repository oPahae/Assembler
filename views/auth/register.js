//^.................................................................../
//* Frontend :
//^.................................................................../

let currentStep = 0;
const steps = document.querySelectorAll(".step");
const progressBar = document.getElementById("progress-bar");
const nextBtn = document.getElementById("next-btn");
const prevBtn = document.getElementById("prev-btn");

function updateStep(direction) {
    gsap.to(steps[currentStep], {
        opacity: 0, x: -60, duration: 0.5, onComplete: () => {
            steps[currentStep].classList.add("hidden");
            currentStep += direction;
            steps[currentStep].classList.remove("hidden");
            gsap.fromTo(steps[currentStep], { opacity: 0, x: 60 }, { opacity: 1, x: 0, duration: 0.5 });
            progressBar.style.width = `${(currentStep / (steps.length - 1)) * 100}%`;
            prevBtn.classList.toggle("hidden", currentStep === 0);
            // nextBtn.textContent = currentStep === steps.length - 1 ? "Register" : "Next";
        }
    });
}

nextBtn.addEventListener("click", () => {
    if (currentStep < steps.length - 1) updateStep(1);
    if (currentStep >= steps.length - 2) nextBtn.style.display = "none"
});

prevBtn.addEventListener("click", () => {
    nextBtn.style.display = "block"
    if (currentStep > 0) updateStep(-1);
});

function showErrorPane(txt) {
    gsap.killTweensOf("#errorPane");
    gsap.set("#errorPane", { clearProps: "all" });
    document.getElementById("errorMsg").textContent = txt
    gsap.from("#errorPane", { opacity: 0, x: 50, duration: 1, ease: "elastic.out" });
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

function showSuccessPane(txt) {
    gsap.killTweensOf("#successPane");
    gsap.set("#successPane", { clearProps: "all" });
    document.getElementById("successMsg").textContent = txt
    gsap.from("#successPane", { opacity: 0, x: 50, duration: 1, ease: "elastic.out" });
    document.getElementById("successPane").classList.remove("hidden")
    setTimeout(() => {
        hideSuccessPane()
    }, 5000);
}

function hideSuccessPane() {
    gsap.killTweensOf("#successPane");
    gsap.set("#successPane", { clearProps: "all" });
    gsap.to("#successPane", { opacity: 0, x: 50, duration: 0.8, ease: "elastic.in" });
    setTimeout(() => {
        document.getElementById("successPane").classList.add("hidden")
    }, 1000);
}

document.getElementById("closeSuccess").onclick = () => document.getElementById("errorPane").classList.add("hidden");

//^.................................................................../
//* Backend :
//^.................................................................../

document.getElementById("register-form").addEventListener("submit", function (e) {
    e.preventDefault()
    const steps = document.querySelectorAll(".step");
    const formData = {
        firstName: steps[0].querySelector("input").value.trim(),
        lastName: steps[1].querySelector("input").value.trim(),
        email: steps[2].querySelector("input").value.trim(),
        password: steps[3].querySelector("input").value.trim(),
        confirmPassword: steps[4].querySelector("input").value.trim()
    }
    if (formData.password.length < 6) {
        showErrorPane('Password should be at least 6 characters');
        return;
    }
    fetch("../../controllers/register.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === "success") {
            showSuccessPane("Registration Successful!");
            setTimeout(() => {
                window.location.href = "login.html";
            }, 500);
        } else {
            showErrorPane(data.message);
            console.log(data.message)
        }
    })
    .catch(error => { showErrorPane("Server error. Try again later."); console.log(error) });
});