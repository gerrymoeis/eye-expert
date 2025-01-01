async function submitSymptoms() {
    const selectedSymptoms = [];
    document.querySelectorAll('input[name="symptom"]:checked').forEach((checkbox) => {
        selectedSymptoms.push(checkbox.value);
    });

    const response = await fetch('backend/fetchDiagnosis.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ symptoms: selectedSymptoms })
    });

    const data = await response.json();

    // Display the diagnosis result
    const resultDiv = document.getElementById('result');
    resultDiv.innerHTML = '';  // Clear previous results
    data.forEach(disease => {
        const diseaseInfo = document.createElement('p');
        diseaseInfo.textContent = `${disease.name}: ${disease.description}`;
        resultDiv.appendChild(diseaseInfo);
    });
}