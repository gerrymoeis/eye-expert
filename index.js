// Load Navbar and Footer Components
document.addEventListener('DOMContentLoaded', () => {
    // Navbar Component
    fetch('components/navbar/navbar.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('navbar').innerHTML = html;

            const toggleButton = document.querySelector('.navbar__toggle');
            const navbarLinks = document.querySelector('.navbar__links');

            toggleButton.addEventListener('click', function () {
                navbarLinks.classList.toggle('show');
                toggleButton.classList.toggle('active');
            });

            const link_active = document.querySelector('#home');
            link_active.classList.add('active');
        });

    fetch('pages/home/home.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('page-content').innerHTML = html;

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

    fetch('components/footer/footer.html')
        .then(response => response.text())
        .then(html => {
            document.getElementById('footer').innerHTML = html;
            
            const link_active = document.querySelectorAll('#home');
            link_active[1].classList.add('active');
        });
});

// Smooth scrolling for internal links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Basic fade-in animation for content
window.addEventListener('scroll', function() {
    document.querySelectorAll('.fade-in').forEach(element => {
        const position = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;

        if (position < screenPosition) {
            element.classList.add('appear');
        }
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

// Define fuzzy membership functions
function getSymptomWeight(symptom) {
    switch(symptom) {
        case 'redEyes': return 0.8; // Example weight for red eyes
        case 'itchiness': return 0.7;
        // Add more symptoms as per your data
        default: return 0;
    }
}

// Define fuzzy rules and apply Mamdani inference
function diagnose(symptoms) {
    let conjunctivitis = 0, cataract = 0, glaucoma = 0;

    symptoms.forEach(symptom => {
        let weight = getSymptomWeight(symptom);

        // Example fuzzy rule for Conjunctivitis
        if (symptom === 'redEyes' || symptom === 'itchiness') {
            conjunctivitis += weight;
        }
        // Additional rules for other diseases
    });

    // Normalize and calculate the probabilities
    const total = conjunctivitis + cataract + glaucoma;
    return {
        conjunctivitis: (conjunctivitis / total) * 100,
        cataract: (cataract / total) * 100,
        glaucoma: (glaucoma / total) * 100
    };
}
