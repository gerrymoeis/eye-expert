// Load Navbar and Footer Components
document.addEventListener('DOMContentLoaded', () => {
    // Navbar Component
    fetch('../../components/navbar/navbar.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('navbar').innerHTML = html;

            const toggleButton = document.querySelector('.navbar__toggle');
            const navbarLinks = document.querySelector('.navbar__links');

            toggleButton.addEventListener('click', function () {
                navbarLinks.classList.toggle('show');
                toggleButton.classList.toggle('active');
            });
        });

    fetch('../../components/footer/footer.html')
        .then(response => response.text())
        .then(html => document.getElementById('footer').innerHTML = html);
});

document.addEventListener('DOMContentLoaded', () => {
    const sections = document.querySelectorAll('section');

    const fadeInOnScroll = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('fade-in');
                observer.unobserve(entry.target);
            }
        });
    };

    const observer = new IntersectionObserver(fadeInOnScroll, {
        threshold: 0.1
    });

    sections.forEach(section => observer.observe(section));
});

// document.addEventListener("DOMContentLoaded", () => {
//     // Load data from localStorage
//     const userName = localStorage.getItem("user_name") || "Anonymous";
//     const consultationDate = localStorage.getItem("consultation_date") || "Unknown date";
//     const diagnosisResults = JSON.parse(localStorage.getItem("diagnosis_results")) || [];
//     console.log(diagnosisResults);

//     // Populate user info
//     document.getElementById("user-name").textContent = userName;
//     document.getElementById("consultation-date").textContent = consultationDate;

//     // Populate diagnosis results
//     const diagnosisList = document.getElementById("diagnosis-list");
//     let highestFuzzyScore = 0;
//     let mostLikelyDisease = null;

//     diagnosisResults.forEach(diagnosis => {
//         const listItem = document.createElement("li");
//         listItem.innerHTML = `
//             <h3>${diagnosis.disease_name}</h3>
//             <p>${diagnosis.description}</p>
//             <p><strong>Fuzzy Score:</strong> ${diagnosis.fuzzy_score.toFixed(2)}</p>
//         `;
//         diagnosisList.appendChild(listItem);

//         // Determine the most likely disease
//         if (diagnosis.fuzzy_score > highestFuzzyScore) {
//             highestFuzzyScore = diagnosis.fuzzy_score;
//             mostLikelyDisease = diagnosis;
//         }
//     });

//     // Add a conclusion
//     const conclusionDiv = document.createElement("div");
//     conclusionDiv.innerHTML = `
//         <h2>Conclusion</h2>
//         <p>${mostLikelyDisease
//             ? `Based on the analysis, you are most likely experiencing <strong>${mostLikelyDisease.disease_name}</strong>. ${mostLikelyDisease.description}`
//             : "No conclusive diagnosis could be determined based on your symptoms."}</p>
//     `;
//     document.querySelector(".hasil-page").appendChild(conclusionDiv);
// });

document.addEventListener("DOMContentLoaded", () => {
    // Retrieve the consultation_id from localStorage
    const consultationId = localStorage.getItem("consultation_id");

    if (!consultationId) {
        alert("Consultation ID not found.");
        return;
    }

    // Step 1: Fetch the consultation results from the server
    fetch(`../../backend/api.php?endpoint=get-consultation-results&consultation_id=${consultationId}`)
        .then(response => response.json())
        .then(data => {
            // Log the full response to inspect the data structure
            console.log("Full response:", data);

            if (data.error) {
                console.error("Error:", data.error);
                alert(data.error);
                return;
            }

            // Log the raw results for debugging
            console.log("Raw Results:", data.raw_results); // Inspect the raw data

            // Step 2: Display user name and results
            const userName = localStorage.getItem("user_name") || "Anonymous";
            const consultationDate = localStorage.getItem("consultation_date") || "Unknown date";

            document.getElementById("user-name").textContent = userName;
            document.getElementById("consultation-date").textContent = consultationDate;

            const diagnosisList = document.getElementById("diagnosis-list");

            data.diagnosis.forEach(diagnosis => {
                const listItem = document.createElement("li");
                listItem.innerHTML = `
                    <h3>${diagnosis.disease_name}</h3>
                    <p>${diagnosis.description}</p>
                    <p><strong>Fuzzy Score:</strong> ${diagnosis.fuzzy_score.toFixed(2)}</p>
                `;
                diagnosisList.appendChild(listItem);
            });

            // Add a conclusion based on the highest fuzzy score
            let highestFuzzyScore = 0;
            let mostLikelyDisease = null;

            data.diagnosis.forEach(diagnosis => {
                if (diagnosis.fuzzy_score > highestFuzzyScore) {
                    highestFuzzyScore = diagnosis.fuzzy_score;
                    mostLikelyDisease = diagnosis;
                }
            });

            const conclusionDiv = document.createElement("div");
            conclusionDiv.innerHTML = `
                <h2>Conclusion</h2>
                <p>${mostLikelyDisease
                    ? `Based on the analysis, you are most likely experiencing <strong>${mostLikelyDisease.disease_name}</strong>. ${mostLikelyDisease.description}`
                    : "No conclusive diagnosis could be determined based on your symptoms."}</p>
            `;
            document.querySelector(".hasil-page").appendChild(conclusionDiv);
        })
        .catch(error => {
            console.error("Error fetching consultation results:", error);
            alert("An error occurred while fetching the diagnosis.");
        });
});
