// const renderQuestions = () => {
//     questionContainer.innerHTML = "";
//     const startIndex = currentPage * questionsPerPage;
//     const endIndex = Math.min(startIndex + questionsPerPage, totalQuestions);

//     questions.slice(startIndex, endIndex).forEach((question, index) => {
//         const questionIndex = startIndex + index;
//         const formComponent = document.createElement("div");
//         formComponent.classList.add("form-component");

//         formComponent.innerHTML = `
//             <p class="form-question">${question}</p>
//             <div class="form-options">
//                 ${[1, 2, 3, 4, 5]
//                     .map(
//                         value => `
//                         <label>
//                             <input type="radio" name="question-${questionIndex}" value="${value}" ${
//                             userResponses[questionIndex] === value ? "checked" : ""
//                         } />
//                             <span class="radio-label">${
//                                 value === 1
//                                     ? "Never"
//                                     : value === 2
//                                     ? "Rarely"
//                                     : value === 3
//                                     ? "Sometimes"
//                                     : value === 4
//                                     ? "Often"
//                                     : "Always"
//                             }</span>
//                         </label>
//                     `
//                     )
//                     .join("")}
//             </div>
//         `;

//         formComponent.addEventListener("change", () => {
//             userResponses[questionIndex] = Number(
//                 formComponent.querySelector("input:checked").value
//             );
//             updateNavigationState();
//         });

//         questionContainer.appendChild(formComponent);
//     });
// };

// async function fetchDiagnosis() {
//     const consultationId = localStorage.getItem("consultation_id");

//     if (!consultationId) {
//         alert("No consultation ID found. Please start a consultation first.");
//         return;
//     }

//     try {
//         const response = await fetch('../../backend/api.php?endpoint=calculate-fuzzy', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: new URLSearchParams({ consultation_id: consultationId }),
//         });

//         if (!response.ok) {
//             throw new Error("Failed to fetch diagnosis.");
//         }

//         const result = await response.json();
//         if (result.debug) {
//             console.group("Backend Debug Logs");
//             result.debug.forEach(log => console.log(log));
//             console.groupEnd();
//         }

//         if (result.error) {
//             console.error("Backend error:", result.error);
//             alert("Error: " + result.error);
//             return;
//         }

//         console.log("Diagnosis Results:", result.diagnosis);

//         // Save diagnosis results for display on the results page
//         localStorage.setItem("diagnosis_results", JSON.stringify(result.diagnosis));
//         localStorage.setItem("user_name", result.user_name);

//         // Redirect to results page
//         window.location.href = "../hasil/hasil.html";
//     } catch (error) {
//         console.error("Error fetching diagnosis:", error);
//         alert("An error occurred while fetching the diagnosis.");
//     }
// }

// async function fetchDiagnosis() {
//     const consultationId = localStorage.getItem("consultation_id");

//     if (!consultationId) {
//         alert("No consultation ID found. Please start a consultation first.");
//         return;
//     }

//     try {
//         const response = await fetch('../../backend/api.php?endpoint=calculate-fuzzy', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: new URLSearchParams({ consultation_id: consultationId }),
//         });

//         if (!response.ok) {
//             throw new Error("Failed to fetch diagnosis.");
//         }

//         console.log(response);
//         console.log(response.json());

//         const result = await response.json();

//         // Log debug data to the console
//         if (result.debug) {
//             console.group("Backend Debug Logs");
//             result.debug.forEach(log => console.log(log));
//             console.groupEnd();
//         }

//         if (result.error) {
//             console.error("Backend error:", result.error);
//             alert("Error: " + result.error);
//             return;
//         }

//         console.log("Diagnosis Results:", result.diagnosis);

//         // Save diagnosis results for display on the results page
//         localStorage.setItem("diagnosis_results", JSON.stringify(result.diagnosis));
//         localStorage.setItem("user_name", result.user_name);

//         // Redirect to results page
//         window.location.href = "../hasil/hasil.html";
//     } catch (error) {
//         console.error("Error fetching diagnosis:", error);
//         alert("An error occurred while fetching the diagnosis.");
//     }
// }