//? ____________________________________________________________________
//* SETUP ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//! ********************************************************************

let shortcut = ""
window.addEventListener("keydown", (e) => {
    shortcut += e.key
    if (shortcut === "ControlAlt")
    {startListening(); document.getElementById("recognition").classList.remove("hidden")}
    setTimeout(() => {
        shortcut = ""
    }, 5000)
})

function startListening() {
    if (!(window.SpeechRecognition || window.webkitSpeechRecognition)) {
        alert("Your navigator don't support SpeechRecognition")
    }
    
    const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)()
    recognition.lang = "en-US"
    recognition.start()

    recognition.onresult = (event) => execute(event.results[0][0].transcript)
    recognition.onerror = (event) => { alert("Error : " + event.error); document.getElementById("recognition").classList.add("hidden") }
}

function compare(str1, str2) {
    const normalize = str => str
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/\s+/g, ' ').trim();

    const a = normalize(str1);
    const b = normalize(str2);

    if (a === b || a.includes(b) || b.includes(a)) return true;

    function jaroWinklerDistance(s1, s2) {
        const matchDistance = Math.floor(Math.max(s1.length, s2.length) / 2) - 1;

        const matches1 = Array(s1.length).fill(false);
        const matches2 = Array(s2.length).fill(false);

        let matchingCharacters = 0;

        for (let i = 0; i < s1.length; i++) {
            const start = Math.max(0, i - matchDistance);
            const end = Math.min(i + matchDistance + 1, s2.length);

            for (let j = start; j < end; j++) {
                if (!matches2[j] && s1[i] === s2[j]) {
                    matches1[i] = true;
                    matches2[j] = true;
                    matchingCharacters++;
                    break;
                }
            }
        }

        if (matchingCharacters === 0) return 0;

        let transpositions = 0;
        let k = 0;

        for (let i = 0; i < s1.length; i++) {
            if (matches1[i]) {
                while (!matches2[k]) k++;

                if (s1[i] !== s2[k]) transpositions++;
                k++;
            }
        }

        const jaroSimilarity = (
            (matchingCharacters / s1.length) +
            (matchingCharacters / s2.length) +
            ((matchingCharacters - transpositions / 2) / matchingCharacters)
        ) / 3;

        const prefixLength = Math.min(4, [...s1].reduce((count, char, i) =>
            (i < s2.length && char === s2[i]) ? count + 1 : count, 0));

        const scalingFactor = 0.1;

        return jaroSimilarity + (prefixLength * scalingFactor * (1 - jaroSimilarity));
    }

    function nGramSimilarity(s1, s2, n = 2) {
        if (s1.length < n || s2.length < n) return 0;

        const createNGrams = str => {
            const ngrams = new Set();
            for (let i = 0; i <= str.length - n; i++) {
                ngrams.add(str.substring(i, i + n));
            }
            return ngrams;
        };

        const ngrams1 = createNGrams(s1);
        const ngrams2 = createNGrams(s2);

        const intersection = [...ngrams1].filter(gram => ngrams2.has(gram));
        const union = new Set([...ngrams1, ...ngrams2]);

        return (2 * intersection.length) / (ngrams1.size + ngrams2.size);
    }

    const wordsA = a.split(/\s+/);
    const wordsB = b.split(/\s+/);

    function findBestWordMatches(wordsSource, wordsTarget) {
        return wordsSource.map(word => {
            const matches = wordsTarget.map(targetWord => {
                const editSimilarity = jaroWinklerDistance(word, targetWord);
                const ngramSim = nGramSimilarity(word, targetWord, 2);
                return Math.max(editSimilarity, ngramSim);
            });

            return Math.max(...matches, 0);
        });
    }

    const wordMatchesA = findBestWordMatches(wordsA, wordsB);
    const wordMatchesB = findBestWordMatches(wordsB, wordsA);

    const avgWordMatchScore =
        [...wordMatchesA, ...wordMatchesB].reduce((sum, score) => sum + score, 0) /
        (wordMatchesA.length + wordMatchesB.length);

    function getWordRoot(word) {
        return word.length > 4 ? word.substring(0, 4) : word;
    }

    const rootsA = wordsA.map(getWordRoot);
    const rootsB = wordsB.map(getWordRoot);

    const commonRoots = rootsA.filter(root => rootsB.includes(root));
    const rootSimilarity = commonRoots.length / Math.max(rootsA.length, rootsB.length);

    const lengthRatio = Math.min(a.length, b.length) / Math.max(a.length, b.length);

    const globalJaroWinkler = jaroWinklerDistance(a, b);
    const globalNGram = nGramSimilarity(a, b, 3);

    const weights = {
        jaroWinkler: 0.25,
        nGram: 0.20,
        wordMatch: 0.30,
        rootSimilarity: 0.15,
        lengthRatio: 0.10
    };

    const finalScore =
        globalJaroWinkler * weights.jaroWinkler +
        globalNGram * weights.nGram +
        avgWordMatchScore * weights.wordMatch +
        rootSimilarity * weights.rootSimilarity +
        lengthRatio * weights.lengthRatio;

    const dynamicThreshold = 0.55 - (0.05 * Math.min(1, Math.max(a.length, b.length) / 20));

    return finalScore >= dynamicThreshold;
}

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

//? ____________________________________________________________________
//* EXECUTE ||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
//! ********************************************************************

function execute(speech) {
    console.log(speech)
    speech = speech.toLowerCase()
    const iframe = document.querySelector("iframe");
    document.getElementById("recognition").classList.add("hidden")

    //& pages :::::::::::::::::::::::::::::::::::::

    if (compare(speech, 'create')) {
        switchPage('create', null)
    }

    if (compare(speech, 'join')) {
        switchPage('join', null)
    }

    if (compare(speech, 'your')) {
        switchPage('your', null)
    }

    if (compare(speech, 'analytics')) {
        switchPage('analytics', null)
    }

    if (compare(speech, 'profile')) {
        switchPage('profile', null)
    }

    //& tabs :::::::::::::::::::::::::::::::::::::

    if (compare(speech, 'dashboard')) {
        switchFrame('dashboard')
    }

    if (compare(speech, 'tasks')) {
        switchFrame('tasks')
    }

    if (compare(speech, 'chat')) {
        switchFrame('chat')
    }

    if (compare(speech, 'team')) {
        switchFrame('team')
    }

    if (compare(speech, 'settings')) {
        switchFrame('settings')
    }

    //& events :::::::::::::::::::::::::::::::::::::

    if (compare(speech, 'log') && compare(speech, 'out')) {
        logout()
    }

    if (compare(speech, 'scroll')) {
        iframe.contentWindow.scrollTo({ top: 500, behavior: "smooth" });
    }

    //& chat :::::::::::::::::::::::::::::::::::::

    if (compare(speech, 'send')) {
        iframe.contentWindow.document.getElementById("msgInp").value = speech.split("message")[1]
        iframe.contentWindow.handleSendMsg()
    }
    
    if (compare(speech, 'voice')) {
        iframe.contentWindow.voiceCall()
    }

    //& tasks :::::::::::::::::::::::::::::::::::::
    
    if (compare(speech, 'add')) {
        iframe.contentWindow.document.location.href = `addTask.html?projectID=${new URLSearchParams(iframe.contentWindow.window.location.search).get('projectID')}`
    }
    
    if ((compare(speech, 'type') || compare(speech, 'put')) && iframe.src.includes("task")) {
        if(speech.includes("name")) iframe.contentWindow.document.querySelectorAll("input")[0].value = speech.split("name")[1]
        if(speech.includes("deadline")) iframe.contentWindow.document.querySelectorAll("input")[1].value = speech.split("deadline")[1].replaceAll("s", "").replaceAll(" ", "-").replaceAll(":", "-").replaceAll("/", "-")
        if(speech.includes("description")) iframe.contentWindow.document.querySelector("textarea").value = speech.split("description")[1]     
    }
}