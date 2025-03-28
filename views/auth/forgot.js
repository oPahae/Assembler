document.addEventListener('DOMContentLoaded', function () {

//^.................................................................../
//* Frontend :
//^.................................................................../

    let currentStep = 0
    const steps = document.querySelectorAll(".step")
    const progressBar = document.getElementById("progress-bar")
    const nextBtn = document.getElementById("next-btn")
    const prevBtn = document.getElementById("prev-btn")
    const form = document.getElementById("forgot-form")

    let userEmail = ''
    let userCode = ''

    function updateStep(direction) {
        gsap.to(steps[currentStep], {
            opacity: 0, x: -60, duration: 0.5, onComplete: () => {
                steps[currentStep].classList.add("hidden")
                currentStep += direction
                steps[currentStep].classList.remove("hidden")
                gsap.fromTo(steps[currentStep], { opacity: 0, x: 60 }, { opacity: 1, x: 0, duration: 0.5 })
                progressBar.style.width = `${(currentStep / (steps.length - 1)) * 100}%`
                prevBtn.classList.toggle("hidden", currentStep === 0)
                nextBtn.textContent = currentStep === steps.length - 1 ? "Reset" : "Next"
            }
        })
    }

    async function showErrorPane(txt) {
        gsap.killTweensOf("#errorPane")
        gsap.set("#errorPane", { clearProps: "all" })
        document.getElementById("errorMsg").textContent = txt
        gsap.from("#errorPane", { opacity: 0, x: 50, duration: 1, ease: "elastic.out" })
        document.getElementById("errorPane").classList.remove("hidden")
        setTimeout(() => {
            hideErrorPane()
        }, 5000)
    }

    function hideErrorPane() {
        gsap.killTweensOf("#errorPane")
        gsap.set("#errorPane", { clearProps: "all" })
        gsap.to("#errorPane", { opacity: 0, x: 50, duration: 0.8, ease: "elastic.in" })
        setTimeout(() => {
            document.getElementById("errorPane").classList.add("hidden")
        }, 1000)
    }

    document.getElementById("closeError").onclick = () => document.getElementById("errorPane").classList.add("hidden")

    function showSuccessPane(txt) {
        gsap.killTweensOf("#successPane")
        gsap.set("#successPane", { clearProps: "all" })
        document.getElementById("successMsg").textContent = txt
        gsap.from("#successPane", { opacity: 0, x: 50, duration: 1, ease: "elastic.out" })
        document.getElementById("successPane").style.opacity = 1
        document.getElementById("successPane").classList.remove("hidden")
        setTimeout(() => {
            hideSuccessPane()
        }, 5000)
    }

    function hideSuccessPane() {
        gsap.killTweensOf("#successPane")
        gsap.set("#successPane", { clearProps: "all" })
        gsap.to("#successPane", { opacity: 0, x: 50, duration: 0.8, ease: "elastic.in" })
        setTimeout(() => {
            document.getElementById("successPane").classList.add("hidden")
        }, 1000)
    }

    document.getElementById("closeSuccess").onclick = () => document.getElementById("errorPane").classList.add("hidden")

//^.................................................................../
//* Backend :
//^.................................................................../

const inputs = document.querySelectorAll("#codeInput")
    inputs.forEach((input, index) => {
        input.addEventListener("input", (e) => {
            if (e.target.value.length === 1) {
                if (index < inputs.length - 1) {
                    inputs[index + 1].focus()
                }
            }
        })

        input.addEventListener("keydown", (e) => {
            if (e.key === "Backspace" && e.target.value === "") {
                if (index > 0) {
                    inputs[index - 1].focus()
                }
            }
        })
    })

    function getVerificationCode() {
        let code = ''
        inputs.forEach(input => {
            code += input.value
        })
        return code
    }

    function countDown() {
        let counter = document.getElementById("counter")
        let timer = counter.querySelector("span")
        counter.classList.remove("hidden")
        let [min, sec] = [9, 59]
    
        let interval = setInterval(() => {
            timer.textContent = String(min).padStart(2, "0") + ":" + String(sec).padStart(2, "0")
            if (min === 0 && sec === 0) {
                clearInterval(interval)
                return
            }
            if (sec === 0) {
                min--
                sec = 59
            }
            else sec--
        }, 1000)
    }    

    async function callApi(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            const result = await response.json()
            if (!response.ok) {
                throw new Error(result.error || 'Error')
            }
            return result
        } catch (error) {
            showErrorPane(error.message)
            throw error
        }
    }

    nextBtn.addEventListener("click", async () => {
        try {
            if (currentStep === 0) {
                const emailInput = steps[0].querySelector('input[type="email"]')
                userEmail = emailInput.value.trim()
                if (!userEmail) {
                    showErrorPane('Please enter an email')
                    return
                }
                await callApi('../../controllers/recover.php?action=request', { email: userEmail })
                countDown()
                updateStep(1)

            } else if (currentStep === 1) {
                userCode = getVerificationCode()
                if (userCode.length !== 6) {
                    showErrorPane('Code should contain 6 numbers')
                    return
                }
                await callApi('../../controllers/recover.php?action=verify', {
                    email: userEmail,
                    code: userCode
                })
                document.getElementById("counter").classList.add("hidden")
                updateStep(1)

            } else if (currentStep === 2) {
                const passwordInput = steps[2].querySelector('input[type="password"]')
                const password = passwordInput.value.trim()
                if (password.length < 6) {
                    showErrorPane('Password should be at least 6 characters')
                    return
                }
                updateStep(1)

            } else if (currentStep === 3) {
                const passwordInput = steps[2].querySelector('input[type="password"]')
                const confirmInput = steps[3].querySelector('input[type="password"]')
                const password = passwordInput.value.trim()
                const confirmPassword = confirmInput.value.trim()
                if (password !== confirmPassword) {
                    showErrorPane('Passwords don\'t match')
                    return
                }
                await callApi('../../controllers/recover.php?action=reset', {
                    email: userEmail,
                    code: userCode,
                    password: password,
                    confirm_password: confirmPassword
                })

                showSuccessPane('Password was reset successfuly !')
                setTimeout(() => {
                    window.location.href = 'login.html'
                }, 500)
            }
        } catch (error) {
            console.error('Error:', error)
        }
    })

    prevBtn.addEventListener("click", () => {
        if (currentStep > 0) {
            updateStep(-1)
        }
    })
})