CREATE DATABASE EyeDiagnosticSystem;
GO

USE EyeDiagnosticSystem;
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


INSERT INTO symptoms (name) VALUES ('Red Eyes'), ('Itchiness'), ('Blurred Vision');
INSERT INTO diseases (name, description, symptom_ids) VALUES
    ('Conjunctivitis', 'Eye inflammation usually due to infection', '1,2'),
    ('Glaucoma', 'Group of eye conditions that damage the optic nerve', '3');


-- Insert sample symptoms
INSERT INTO symptoms (id, name)
VALUES
(1, 'Blurry vision'),
(2, 'Itchiness'),
(3, 'Eye redness'),
(4, 'Dryness or irritation'),
(5, 'Eye pain'),
(6, 'Sensitivity to light');

INSERT INTO symptoms (id, name) VALUES
(7, 'Excessive Tearing'),
(8, 'Eye Strain'),
(9, 'Double Vision');

-- Insert sample diseases
INSERT INTO diseases (id, name, description)
VALUES
(1, 'Cataracts', 'Clouding of the normally clear lens of the eye.'),
(2, 'Conjunctivitis', 'Inflammation of the outermost layer of the eye and eyelid.'),
(3, 'Dry Eye Syndrome', 'A condition where the eyes do not produce enough tears.'),
(4, 'Glaucoma', 'A group of eye conditions that damage the optic nerve.'),
(5, 'Migraine with aura', 'Recurring headaches with visual disturbances.');

INSERT INTO diseases (id, name, description) VALUES
(6, 'Macular Degeneration', 'A chronic eye disease causing central vision loss.'),
(7, 'Retinal Detachment', 'A condition where the retina detaches from the eye.');

INSERT INTO fuzzy_rules (symptom_id, severity_level, disease_id, impact)
VALUES
(1, 'High', 3, 0.8), -- High severity of blurry vision strongly suggests Dry Eye Syndrome
(3, 'Medium', 2, 0.6), -- Medium redness suggests Conjunctivitis
(5, 'Low', 1, 0.4); -- Low eye pain weakly suggests Cataracts

-- Insert consultation history
INSERT INTO consultations (user_name, progress)
VALUES
('John Doe', 0),
('Jane Smith', 25);
INSERT INTO consultations (user_name, created_at) VALUES ('Test User', GETDATE());


-- Insert consultation answers
INSERT INTO consultation_answers (consultation_id, symptom_id, severity)
VALUES
(1, 1, 5),
(1, 2, 3),
(2, 3, 4),
(2, 4, 2);

-- Insert fuzzy results (assume fuzzy logic processed these)
INSERT INTO consultation_results (consultation_id, disease_id, fuzzy_score, certainty)
VALUES
(1, 1, 0.8, 90.5),
(2, 3, 0.6, 75.0);


SELECT * FROM symptoms;
SELECT * FROM diseases;
SELECT * FROM fuzzy_rules;

SELECT * FROM consultations;
SELECT * FROM consultation_answers;
SELECT * FROM consultation_results;

DELETE FROM consultations
WHERE id IN (1, 2, 3)

SELECT user_name FROM consultations WHERE id = 8;

DELETE FROM symptoms
WHERE id IN (1,2,3,4,5,6,7,8,9);

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
