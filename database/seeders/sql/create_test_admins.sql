USE amane;

-- First modify the table structure
ALTER TABLE administrateurs
    CHANGE mot_de_passe_hash password VARCHAR(255) NOT NULL;

-- Clear existing admins
TRUNCATE TABLE administrateurs;
delete from administrateurs;

-- Create owner admin
INSERT INTO administrateurs (
    email,
    password,
    prenom,
    nom,
    est_actif,
    est_proprietaire
) VALUES (
    'owner@amane.com',
    'admin123',
    'Ahmed',
    'Propriétaire',
    true,
    true
);

-- Create regular admin
INSERT INTO administrateurs (
    email,
    password,
    prenom,
    nom,
    est_actif,
    est_proprietaire
) VALUES (
    'admin@amane.com',
    'admin123',
    'Mohammed',
    'Admin',
    true,
    false
);

select * from administrateurs;
