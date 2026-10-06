<?php

class Releve {
    public $id;
    public $etudiant_id;
    public $semestre;
    public $moyenne;
    public $statut;
    public $created_at;
    public $updated_at;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->etudiant_id = $data['etudiant_id'] ?? null;
        $this->semestre = $data['semestre'] ?? null;
        $this->moyenne = $data['moyenne'] ?? null;
        $this->statut = $data['statut'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public function isValid() {
        return !empty($this->etudiant_id) && !empty($this->semestre) && $this->moyenne !== null && !empty($this->statut);
    }

    public function getStatutBadge() {
        return $this->statut === 'Réussi' ? '<span class="badge badge-success">Réussi</span>' : '<span class="badge badge-danger">Échoué</span>';
    }
}
