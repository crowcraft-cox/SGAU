<?php

class Controller {
    protected function render($view, $params = []) {
        $pageTitle = $params['pageTitle'] ?? null;
        extract($params, EXTR_SKIP);
        $view = APP_ROOT . '/app/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($view)) {
            abort(404, "La vue requise est introuvable : $view");
        }

        require APP_ROOT . '/app/Views/Layouts/main.php';
    }
}
