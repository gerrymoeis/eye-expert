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

            const link_active = document.querySelector('#konsultasi');
            link_active.classList.add('active');
        });

    fetch('../../components/footer/footer.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('footer').innerHTML = html;

            const link_active = document.querySelectorAll('#konsultasi');
            link_active[1].classList.add('active');
        });
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

// index.js

// const questions = [
//     "Do you experience dryness?",
//     "Do you have redness in your eyes?",
//     "Is there itchiness in your eyes?",
//     "Do you have blurred vision?",
//     "Do you experience eye pain?",
//     "Do you see floaters or flashes?",
//     "Do your eyes feel tired quickly?",
//     "Do you experience excessive tearing?"
// ];
let questions = [];

// const questionsPerPage = 4;
// const totalQuestions = questions.length;
const questionsPerPage = 4;
let totalQuestions = 0;
let totalPages = Math.ceil(totalQuestions / questionsPerPage);

let currentPage = 0;
const userResponses = new Array(totalQuestions).fill(null);

const questionContainer = document.getElementById("question-container");
const progressBar = document.querySelector(".progress");
const backBtn = document.getElementById("back-btn");
const nextBtn = document.getElementById("next-btn");
const submitBtn = document.getElementById("submit-btn");

const renderForm = async () => {
    const response = await fetch('../../backend/api.php?endpoint=get-symptoms');
    const result = await response.json();
    console.log(result.symptoms)
    questions = result.symptoms;
    totalQuestions = questions.length;
    totalPages = Math.ceil(totalQuestions / questionsPerPage);
    console.log(totalQuestions)

    goToPage(0); // Start at the first page
};

const renderQuestions = () => {
    questionContainer.innerHTML = "";
    const startIndex = currentPage * questionsPerPage;
    const endIndex = Math.min(startIndex + questionsPerPage, totalQuestions);

    questions.slice(startIndex, endIndex).forEach((question, index) => {
        const questionIndex = startIndex + index;
        const formComponent = document.createElement("div");
        formComponent.classList.add("form-component");

        formComponent.innerHTML = `
            <p class="form-question">${question.name}</p>
            <div class="form-options">
                ${[1, 2, 3, 4, 5]
                    .map(
                        value => `
                        <label>
                            <input type="radio" name="question-${question.id}" value="${value}" />
                            <span class="radio-label">${
                                value === 1
                                    ? "Never"
                                    : value === 2
                                    ? "Rarely"
                                    : value === 3
                                    ? "Sometimes"
                                    : value === 4
                                    ? "Often"
                                    : "Always"
                            }</span>
                        </label>
                    `
                    )
                    .join("")}
            </div>
        `;

        formComponent.addEventListener("change", () => {
            userResponses[questionIndex] = Number(
                formComponent.querySelector("input:checked").value
            );
            updateNavigationState();
        });

        questionContainer.appendChild(formComponent);
    });
};

document.addEventListener('DOMContentLoaded', renderForm);


const updateProgress = () => {
    const answeredCount = userResponses.filter(response => response !== null).length;
    const progress = (answeredCount / totalQuestions) * 100;
    progressBar.style.width = `${progress}%`;
};

const updateNavigationState = () => {
    const currentPageAnswered = userResponses
        .slice(currentPage * questionsPerPage, (currentPage + 1) * questionsPerPage)
        .every(response => response !== null);

    nextBtn.disabled = !currentPageAnswered && currentPage < totalPages - 1;
    backBtn.disabled = currentPage === 0;

    // Show Submit button on the last page, hide Next button
    if (currentPage === totalPages - 1) {
        submitBtn.style.display = "inline-block";
        nextBtn.style.display = "none";
    } else {
        submitBtn.style.display = "none";
        nextBtn.style.display = "inline-block";
    }
};

const goToPage = (page) => {
    currentPage = page;
    renderQuestions();
    updateProgress();
    updateNavigationState();
};

backBtn.addEventListener("click", () => goToPage(currentPage - 1));
nextBtn.addEventListener("click", () => {
    if (currentPage < totalPages - 1) {
        goToPage(currentPage + 1);
    }
});

document.addEventListener("DOMContentLoaded", () => {
    goToPage(0);
});

document.getElementById("username-form").addEventListener("submit", async function (e) {
    e.preventDefault(); // Prevent form from refreshing the page

    const userName = document.getElementById("user_name").value;

    try {
        // Start consultation by calling the backend API
        const response = await fetch('../../backend/api.php?endpoint=start-consultation', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ user_name: userName })
        });

        const result = await response.json();

        if (result.consultation_id) {
            alert("Consultation started successfully!");

            // Store consultation ID in local storage for later use
            localStorage.setItem('consultation_id', result.consultation_id);

            // Hide the username form and show the question form
            document.getElementById("start-consultation-form").style.display = "none";
            document.getElementById("consultation-main").style.display = "flex";
        } else {
            alert("Error: " + result.error);
        }
    } catch (err) {
        console.error(err);
        alert("An error occurred while starting the consultation.");
    }
});

async function fetchDiagnosis() {
    const consultationId = localStorage.getItem("consultation_id");

    if (!consultationId) {
        alert("No consultation ID found. Please start a consultation first.");
        return;
    }

    try {
        const response = await fetch('../../backend/api.php?endpoint=calculate-fuzzy', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ consultation_id: consultationId }),
        });

        if (!response.ok) {
            throw new Error("Failed to fetch diagnosis.");
        }

        // Ensure the response body is read only once
        const result = await response.json();
        console.log(result);

        // Log debug data to the console
        if (result.debug) {
            console.group("Backend Debug Logs");
            result.debug.forEach(log => console.log(log));
            console.groupEnd();
        }

        if (result.error) {
            console.error("Backend error:", result.error);
            alert("Error: " + result.error);
            return;
        }

        const consultationDate = new Date().toLocaleString(); // Get current date/time

        console.log("Diagnosis Results:", result.diagnosis);

        // Save diagnosis results for display on the results page
        // localStorage.setItem("diagnosis_results", JSON.stringify(result.diagnosis));
        localStorage.setItem("user_name", result.user_name);
        localStorage.setItem("consultation_id", result.consultation_id)
        localStorage.setItem("consultation_date", consultationDate);


        // Redirect to results page
        window.location.href = "../hasil/hasil.html";
    } catch (error) {
        console.error("Error fetching diagnosis:", error);
        alert("An error occurred while fetching the diagnosis.");
    }
}


submitBtn.addEventListener("click", async () => {
    const consultationId = localStorage.getItem("consultation_id");
    if (!consultationId) {
        alert("No consultation ID found.");
        return;
    }

    try {
        // Save each answer to the backend
        for (let i = 0; i < userResponses.length; i++) {
            const response = await fetch("../../backend/api.php?endpoint=save-answer", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({
                    consultation_id: consultationId,
                    symptom_id: i + 1, // Assuming symptom IDs are 1-based
                    severity: userResponses[i],
                }),
            });

            const result = await response.json();
            if (result.error) {
                console.error("Error saving answer:", result.error);
            }
        }

        alert("Consultation answers saved successfully!");

        // Proceed to calculate diagnosis
        fetchDiagnosis();
    } catch (err) {
        console.error("Error submitting consultation:", err);
        alert("An error occurred while submitting the consultation.");
    }
});



// const questions = [
//     { id: 1, text: "Do you experience dryness?" },
//     { id: 2, text: "Do you have redness in your eyes?" },
//     { id: 3, text: "Is there any itchiness in your eyes?" },
//     // More questions...
// ];

// const renderForm = () => {
//     const formContainer = document.getElementById('form-container'); // Target where to append forms
//     formContainer.innerHTML = ''; // Clear previous content

//     questions.forEach(question => {
//         const formComponent = document.createElement('div');
//         formComponent.classList.add('form-component');
        
//         formComponent.innerHTML = `
//             <label class="form-label" for="symptom-${question.id}">${question.text}</label>
//             <select id="symptom-${question.id}" class="symptom-select">
//                 <option value="0">None</option>
//                 <option value="1">Low</option>
//                 <option value="2">Moderate</option>
//                 <option value="3">High</option>
//                 <option value="4">Very High</option>
//             </select>
//         `;
        
//         formContainer.appendChild(formComponent);
//     });
// };

// document.addEventListener('DOMContentLoaded', renderForm);


// async function submitSymptoms() {
//     const symptoms = {};

//     // Collect each symptom level
//     document.querySelectorAll('select[name="symptom"]').forEach((select) => {
//         const symptomName = select.getAttribute('data-symptom-name');
//         const severityLevel = parseInt(select.value, 10);
        
//         if (severityLevel > 0) {  // Only include symptoms with a level greater than 0
//             symptoms[symptomName] = severityLevel;
//         }
//     });

//     console.log('Selected Symptoms with Levels:', symptoms);

//     try {
//         const response = await fetch('../../backend/fetchDiagnosis.php', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json'
//             },
//             body: JSON.stringify({ symptoms })
//         });

//         if (!response.ok) {
//             throw new Error(`Network response was not ok: ${response.statusText}`);
//         }

//         const data = await response.json();

//         // Display the diagnosis result
//         const resultDiv = document.getElementById('result');
//         resultDiv.innerHTML = '';  // Clear previous results

//         if (data.error) {
//             resultDiv.innerHTML = `<p style="color:red;">${data.error}</p>`;
//             return;
//         }

//         if (data.length === 0) {
//             resultDiv.innerHTML = '<p>No matching diagnosis found for the selected symptoms.</p>';
//         } else {
//             data.forEach(disease => {
//                 const diseaseInfo = document.createElement('p');
//                 diseaseInfo.textContent = `${disease.name}: ${disease.description}`;
//                 resultDiv.appendChild(diseaseInfo);
//             });
//         }
//     } catch (error) {
//         console.error('Error fetching diagnosis:', error);
//         alert('There was an error processing your request. Please try again.');
//     }
// }
