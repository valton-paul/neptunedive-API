-- Insert bateau

INSERT INTO bateau (immatriculation, nb_places)
VALUES ('ABC12345', 8);

INSERT INTO bateau (immatriculation, nb_places)
VALUES ('XYZ67890', 12);

INSERT INTO bateau (immatriculation, nb_places)
VALUES ('LMN54321', 10);

-- Insert Plongeur

INSERT INTO plongeur (email, roles, password, nom, prenom, formation, niveau_plongee_autonome, niveau_plongee_encadre, niveau_moniteur, is_moniteur)
VALUES ('jean.dupont@example.com', '["ROLE_USER"]', 'password_hash_1', 'Dupont', 'Jean', 1, 3, 4, 'Niveau 2', 0);

INSERT INTO plongeur (email, roles, password, nom, prenom, formation, niveau_plongee_autonome, niveau_plongee_encadre, niveau_moniteur, is_moniteur)
VALUES ('admin@admin.com', '["ROLE_USER", "ROLE_ADMIN"]', 'Azerty123!', 'Curie', 'Marie', 1, 5, 6, 'E3', 1);

INSERT INTO plongeur (email, roles, password, nom, prenom, formation, niveau_plongee_autonome, niveau_plongee_encadre, niveau_moniteur, is_moniteur)
VALUES ('albert.einstein@example.com', '["ROLE_USER"]', 'password_hash_3', 'Einstein', 'Albert', 1, 2, NULL, 'Niveau 1', 0);

-- Insert Sortie

INSERT INTO sortie (directeur_plongee_id, securite_surface_id, type, date_sortie, lieu, creneau, bateau_id, pilote_bateau_id)
VALUES (1, 2, 'Plongée de nuit', '2024-10-15', 'Côte Sud', '08:00 - 12:00', 1, 3);

INSERT INTO sortie (directeur_plongee_id, securite_surface_id, type, date_sortie, lieu, creneau, bateau_id, pilote_bateau_id)
VALUES (2, 1, 'Plongée profonde', '2024-10-18', 'Cap Est', '14:00 - 18:00', 2, 1);

INSERT INTO sortie (directeur_plongee_id, securite_surface_id, type, date_sortie, lieu, creneau, bateau_id, pilote_bateau_id)
VALUES (3, 4, 'Exploration', '2024-10-20', 'Baie Ouest', '10:00 - 13:00', 3, 2);
