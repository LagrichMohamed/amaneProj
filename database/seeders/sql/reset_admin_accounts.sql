use amane;

-- Delete existing admins
TRUNCATE TABLE administrateurs;

delete from administrateurs;

-- Create new owner with simple bcrypt password
INSERT INTO administrateurs (
    email,
    mot_de_passe_hash,
    prenom,
    nom,
    est_actif,
    est_proprietaire,
    cree_le
) VALUES (
    'owner@amane.school',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password is "password"
    'Ahmed',
    'Owner',
    true,
    true,
    NOW()
);

-- Create regular admin with simple bcrypt password
INSERT INTO administrateurs (
    email,
    mot_de_passe_hash,
    prenom,
    nom,
    est_actif,
    est_proprietaire,
    cree_le
) VALUES (
    'admin@amane.school',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password is "password"
    'Mohammed',
    'Admin',
    true,
    false,
    NOW()
);
