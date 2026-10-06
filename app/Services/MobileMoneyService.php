<?php

class MobileMoneyService {
    /**
     * Simule l'initiation d'un paiement via Mobile Money
     */
    public static function initiatePayment($phone, $amount, $provider) {
        // Dans une vraie intégration, on ferait un appel cURL vers l'API de l'opérateur
        
        $reference = strtoupper(substr($provider, 0, 3)) . '-' . date('YmdHis') . '-' . rand(1000, 9999);
        
        // Simuler le délai réseau
        usleep(500000); // 0.5s
        
        return [
            'success' => true,
            'reference' => $reference,
            'message' => 'Demande de paiement envoyée. Veuillez valider sur votre téléphone (Simulation).'
        ];
    }
    
    /**
     * Simule la vérification du statut d'une transaction.
     */
    public static function checkStatus($reference) {
        // Simulation : on valide systématiquement pour la démo
        return [
            'status' => 'Validé',
            'provider_reference' => 'OP-' . rand(100000, 999999)
        ];
    }
}
