CREATE DATABASE EyeDiagnosticSystem_2;
GO

USE EyeDiagnosticSystem_2;
GO

CREATE TABLE symptoms (
    id INT PRIMARY KEY,
    name VARCHAR(50)
);

ALTER TABLE symptoms
ADD low FLOAT DEFAULT 0.2,
    medium FLOAT DEFAULT 0.5,
    high FLOAT DEFAULT 0.8;

CREATE TABLE diseases (
    id INT PRIMARY KEY,
    name VARCHAR(50),
    description TEXT,
    symptom_ids VARCHAR(255) -- Assume symptom_ids is a comma-separated list of symptom IDs for simplicity
);

ALTER TABLE diseases
ADD relevance FLOAT DEFAULT 1.0;

CREATE TABLE fuzzy_rules (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    symptom_id INT NOT NULL,
    severity_level NVARCHAR(10) NOT NULL, -- Low, Medium, or High
    disease_id INT NOT NULL,
    impact FLOAT NOT NULL, -- Contribution of this rule to the disease
    FOREIGN KEY (symptom_id) REFERENCES symptoms(id),
    FOREIGN KEY (disease_id) REFERENCES diseases(id)
);

-- Create consultations table
CREATE TABLE consultations (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    user_name NVARCHAR(100) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    progress INT DEFAULT 0
);

-- Create consultation_answers table
CREATE TABLE consultation_answers (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    consultation_id INT NOT NULL,
    symptom_id INT NOT NULL,
    severity INT NOT NULL,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (symptom_id) REFERENCES symptoms(id)
);

-- Create consultation_results table
CREATE TABLE consultation_results (
    id INT IDENTITY(1, 1) PRIMARY KEY,
    consultation_id INT NOT NULL,
    disease_id INT NOT NULL,
    fuzzy_score FLOAT NOT NULL,
    certainty FLOAT DEFAULT 0.0,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id),
    FOREIGN KEY (disease_id) REFERENCES diseases(id)
);

INSERT INTO symptoms (id, name, low, medium, high) VALUES
(1, 'Blurry Vision', 0.2, 0.5, 0.8),
(2, 'Eye Redness', 0.2, 0.5, 0.8),
(3, 'Dryness or Irritation', 0.3, 0.6, 0.9),
(4, 'Eye Pain', 0.1, 0.4, 0.7),
(5, 'Sensitivity to Light', 0.2, 0.6, 0.9),
(6, 'Excessive Tearing', 0.3, 0.5, 0.8),
(7, 'Double Vision', 0.2, 0.5, 0.7),
(8, 'Eye Strain', 0.3, 0.5, 0.8),
(9, 'Floaters or Flashes', 0.1, 0.4, 0.6);

INSERT INTO diseases (id, name, description, relevance) VALUES
(1, 'Cataracts', 'Clouding of the eye lens.', 1.0),
(2, 'Conjunctivitis', 'Inflammation of the conjunctiva.', 0.9),
(3, 'Dry Eye Syndrome', 'Condition where the eyes don’t produce enough tears.', 1.0),
(4, 'Glaucoma', 'Eye disease causing optic nerve damage.', 1.0),
(5, 'Macular Degeneration', 'Loss of central vision.', 0.8),
(6, 'Retinal Detachment', 'Retina detaches from the back of the eye.', 0.7),
(7, 'Migraine with Aura', 'Headaches with visual disturbances.', 0.8);

INSERT INTO diseases (id, name, description, relevance) VALUES
(8, 'Astigmatism', 'Blurred vision caused by an irregularly shaped cornea.', 0.9),
(9, 'Retinitis Pigmentosa', 'Genetic disorder causing retinal degeneration.', 0.8),
(10, 'Macular Hole', 'A small hole in the macula of the retina.', 0.7),
(11, 'Optic Neuritis', 'Inflammation of the optic nerve.', 0.9),
(12, 'Diabetic Retinopathy', 'Damage to the retina caused by diabetes.', 1.0),
(13, 'Age-related Macular Degeneration', 'Deterioration of the central portion of the retina.', 0.9),
(14, 'Keratoconus', 'Degeneration of the cornea.', 0.8),
(15, 'Corneal Ulcer', 'An open sore on the cornea.', 0.9),
(16, 'Uveitis', 'Inflammation of the middle layer of the eye.', 0.8),
(17, 'Convergence Insufficiency', 'Difficulty focusing on near objects.', 0.7),
(18, 'Retinal Vein Occlusion', 'Blockage of the vein in the retina.', 0.8),
(19, 'Amblyopia (Lazy Eye)', 'Decreased vision in one or both eyes due to abnormal development.', 0.9),
(20, 'Strabismus', 'Crossed eyes or misalignment of the eyes.', 0.9);

INSERT INTO fuzzy_rules (symptom_id, severity_level, disease_id, impact) VALUES
(1, 'High', 3, 0.8), -- Blurry Vision -> Dry Eye Syndrome
(2, 'Medium', 2, 0.6), -- Eye Redness -> Conjunctivitis
(3, 'High', 3, 0.9), -- Dryness -> Dry Eye Syndrome
(4, 'Low', 1, 0.4), -- Eye Pain -> Cataracts
(5, 'High', 4, 0.8), -- Light Sensitivity -> Glaucoma
(6, 'Medium', 2, 0.7), -- Excessive Tearing -> Conjunctivitis
(7, 'High', 6, 0.8), -- Double Vision -> Retinal Detachment
(8, 'Medium', 7, 0.6), -- Eye Strain -> Migraine with Aura
(9, 'High', 4, 0.7); -- Floaters -> Glaucoma

INSERT INTO fuzzy_rules (symptom_id, severity_level, disease_id, impact) VALUES
-- Blurry Vision -> Diseases
(1, 'Medium', 1, 0.6), -- Blurry Vision -> Cataracts
(1, 'High', 10, 0.8),  -- Blurry Vision -> Astigmatism
(1, 'Medium', 13, 0.7), -- Blurry Vision -> Age-related Macular Degeneration

-- Eye Redness -> Diseases
(2, 'Medium', 8, 0.6), -- Eye Redness -> Astigmatism
(2, 'Low', 6, 0.5), -- Eye Redness -> Retinal Detachment

-- Dryness or Irritation -> Diseases
(3, 'Medium', 9, 0.7), -- Dryness -> Retinitis Pigmentosa
(3, 'Medium', 5, 0.6), -- Dryness -> Macular Degeneration

-- Eye Pain -> Diseases
(4, 'High', 4, 0.8), -- Eye Pain -> Glaucoma
(4, 'Medium', 15, 0.7), -- Eye Pain -> Retinal Vein Occlusion

-- Sensitivity to Light -> Diseases
(5, 'Medium', 12, 0.8), -- Sensitivity to Light -> Diabetic Retinopathy
(5, 'Low', 10, 0.6), -- Sensitivity to Light -> Retinitis Pigmentosa

-- Excessive Tearing -> Diseases
(6, 'Medium', 3, 0.6), -- Excessive Tearing -> Dry Eye Syndrome
(6, 'Low', 11, 0.5), -- Excessive Tearing -> Optic Neuritis

-- Double Vision -> Diseases
(7, 'Medium', 13, 0.7), -- Double Vision -> Convergence Insufficiency
(7, 'Low', 4, 0.6), -- Double Vision -> Glaucoma

-- Eye Strain -> Diseases
(8, 'Medium', 10, 0.7), -- Eye Strain -> Astigmatism
(8, 'Low', 12, 0.5), -- Eye Strain -> Diabetic Retinopathy

-- Floaters or Flashes -> Diseases
(9, 'Medium', 12, 0.7), -- Floaters -> Diabetic Retinopathy
(9, 'Low', 15, 0.6); -- Floaters -> Keratoconus

SELECT * FROM consultations;
SELECT * FROM consultation_answers;
SELECT * FROM consultation_results;

SELECT user_name FROM consultations WHERE id = 1;
SELECT symptom_id, severity FROM consultation_answers WHERE consultation_id = 1;
SELECT fr.symptom_id, fr.severity_level, fr.disease_id, fr.impact,
       s.low, s.medium, s.high, d.relevance
FROM fuzzy_rules fr
JOIN symptoms s ON fr.symptom_id = s.id
JOIN diseases d ON fr.disease_id = d.id;

SELECT name, description FROM diseases WHERE id = 1;

SELECT cr.disease_id, cr.fuzzy_score, d.name AS disease_name, d.description
        FROM consultation_results cr
        JOIN diseases d ON cr.disease_id = d.id
        WHERE cr.consultation_id = 26;