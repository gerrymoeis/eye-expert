async function getAllDiseases() {
    const response = await fetch(`../../backend/fetchDiagnosis.php`);
    const data = await response.json();

    // Display the results in the frontend
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '';  // Clear previous results

    data.forEach(disease => {
        const diseaseInfo = document.createElement('p');
        diseaseInfo.textContent = `${disease.name}: ${disease.description}`;
        resultDiv.appendChild(diseaseInfo);
    });
}

async function getDiagnosis(symptoms) {
    // Convert symptoms array to a comma-separated list
    const symptomList = symptoms.join(',');

    // Fetch data from the PHP backend
    const response = await fetch(`backend/fetchDiagnosis.php?symptoms=${symptomList}`);
    const data = await response.json();

    // Display the diagnosis result
    console.log(data);
    // Here, add logic to show data in HTML
}

function handleDiagnosis() {
    // Get selected symptoms
    const symptoms = [];
    if (document.getElementById('redEyes').checked) symptoms.push('redEyes');
    if (document.getElementById('itchiness').checked) symptoms.push('itchiness');
    // Call the diagnosis function
    getDiagnosis(symptoms);
}