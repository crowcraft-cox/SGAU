<?php

require_once __DIR__ . '/../Repositories/NoteRepository.php';
require_once __DIR__ . '/../Repositories/EtudiantRepository.php';
require_once __DIR__ . '/../Core/Database.php';

class ReleveService {
    private $noteRepository;
    private $etudiantRepository;
    private $db;

    public function __construct() {
        $this->noteRepository = new NoteRepository();
        $this->etudiantRepository = new EtudiantRepository();
        $this->db = (new Database())->connect();
    }

    /**
     * Récupère les données pour générer un relevé d'étudiant
     * @param int $etudiantId
     * @param int $semestre
     * @return array Données du relevé avec les notes et statistiques
     */
    public function getEtudiantReleveData($etudiantId, $semestre) {
        try {
            $etudiant = $this->etudiantRepository->findById($etudiantId);
            
            if (!$etudiant) {
                return null;
            }

            // Récupérer toutes les notes de l'étudiant pour ce semestre
            $notes = $this->getNotesParSemestre($etudiantId, $semestre);

            // Calculer la moyenne générale
            $moyenne = $this->calculerMoyenne($notes);

            // Déterminer le statut
            $statut = $this->determinerStatut($moyenne);

            return [
                'etudiant' => $etudiant,
                'semestre' => $semestre,
                'notes' => $notes,
                'moyenne' => round($moyenne, 2),
                'statut' => $statut,
                'nombreCours' => count($notes),
                'detailNotes' => $this->genererDetailNotes($notes)
            ];
        } catch (Exception $e) {
            error_log('Erreur getEtudiantReleveData: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les notes d'un étudiant pour un semestre donné
     */
    private function getNotesParSemestre($etudiantId, $semestre) {
        try {
            $stmt = $this->db->prepare(
                'SELECT n.*, c.nom AS cours_nom, c.credit, c.code AS cours_code 
                 FROM notes n 
                 LEFT JOIN cours c ON n.cours_id = c.id 
                 WHERE n.etudiant_id = ? AND n.semestre = ? 
                 ORDER BY c.nom ASC'
            );
            $stmt->execute([$etudiantId, $semestre]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Erreur getNotesParSemestre: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcule la moyenne générale à partir des notes
     */
    private function calculerMoyenne($notes) {
        if (empty($notes)) {
            return 0;
        }

        $somme = 0;
        $count = 0;

        foreach ($notes as $note) {
            // Si note finale n'existe pas ou est 0, calculer à partir des composantes
            $noteFinale = isset($note['note']) && is_numeric($note['note']) && floatval($note['note']) > 0 
                ? floatval($note['note'])
                : $this->calculerNoteFinale(
                    isset($note['interro']) ? floatval($note['interro']) : 0,
                    isset($note['tp']) ? floatval($note['tp']) : 0,
                    isset($note['examen']) ? floatval($note['examen']) : 0
                );
            $somme += $noteFinale;
            $count++;
        }

        return $count > 0 ? $somme / $count : 0;
    }

    /**
     * Calcule la note finale à partir des composantes
     * Formule: (interro * 0.2) + (tp * 0.3) + (examen * 0.5)
     */
    private function calculerNoteFinale($interro, $tp, $examen) {
        return ($interro * 0.2) + ($tp * 0.3) + ($examen * 0.5);
    }

    /**
     * Détermine le statut de réussite basé sur la moyenne
     */
    private function determinerStatut($moyenne) {
        return $moyenne >= 10 ? 'Réussi' : 'Échoué';
    }

    /**
     * Génère un détail formaté des notes
     */
    private function genererDetailNotes($notes) {
        $detail = [];
        foreach ($notes as $note) {
            // Calculer la note finale si elle n'existe pas ou est 0
            $noteFinale = isset($note['note']) && is_numeric($note['note']) && floatval($note['note']) > 0 
                ? floatval($note['note'])
                : $this->calculerNoteFinale(
                    isset($note['interro']) ? floatval($note['interro']) : 0,
                    isset($note['tp']) ? floatval($note['tp']) : 0,
                    isset($note['examen']) ? floatval($note['examen']) : 0
                );
            
            $cr = isset($note['credit']) && is_numeric($note['credit']) ? floatval($note['credit']) : 0;
            $vh = (isset($note['volume_horaire']) && is_numeric($note['volume_horaire'])) ? $note['volume_horaire'] : ($cr > 0 ? ($cr * 25) : '-');
            
            $detail[] = [
                'cours_nom' => $note['cours_nom'] ?? 'Sans titre',
                'cours_code' => $note['cours_code'] ?? '-',
                'credit' => $note['credit'] ?? '-',
                'volume_horaire' => $vh,
                'interro' => isset($note['interro']) ? floatval($note['interro']) : '-',
                'tp' => isset($note['tp']) ? floatval($note['tp']) : '-',
                'examen' => isset($note['examen']) ? floatval($note['examen']) : '-',
                'note' => $noteFinale
            ];
        }
        return $detail;
    }

    /**
     * Récupère les semestres disponibles pour un étudiant
     */
    public function getSemestresDisponibles($etudiantId) {
        try {
            // Vérifier que l'étudiant existe
            $etudiant = $this->etudiantRepository->findById($etudiantId);
            if (!$etudiant) {
                return [];
            }
            
            $stmt = $this->db->prepare(
                'SELECT DISTINCT semestre FROM notes 
                 WHERE etudiant_id = ? 
                 ORDER BY semestre ASC'
            );
            $stmt->execute([$etudiantId]);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            return $result ?: [];
        } catch (Exception $e) {
            error_log('Erreur getSemestresDisponibles: ' . $e->getMessage());
            return [];
        }
    }
}
