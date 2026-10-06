<?php

require_once __DIR__ . '/../../Core/Controller.php';

class FileController extends Controller {
    
    public function serveUpload($type, $filename) {
        $allowedTypes = ['etudiants', 'enseignants', 'documents'];
        
        if (!in_array($type, $allowedTypes)) {
            abort(404, 'Type de fichier non autorisé');
        }
        
        // Valider le nom de fichier (éviter les path traversal)
        if (preg_match('/\.\./', $filename) || !preg_match('/^[a-zA-Z0-9_.-]+$/', $filename)) {
            abort(404, 'Nom de fichier invalide');
        }
        
        $filepath = __DIR__ . '/../../..' . '/storage/uploads/' . $type . '/' . $filename;
        
        if (!file_exists($filepath) || !is_file($filepath)) {
            abort(404, 'Fichier non trouvé');
        }
        
        // Déterminer le type MIME
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        
        // Envoyer le fichier
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filepath));
        header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
        header('Cache-Control: public, max-age=86400'); // Cache 1 jour
        
        readfile($filepath);
        exit;
    }
}
