<?php
/**
 * View Class
 * Handles rendering of views
 */
class View {
    public static function render($view, $data = []) {
        extract($data);

        $viewFile = APP_PATH . '/views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            die("View not found: $view");
        }

        require_once $viewFile;
    }

    public static function renderWithLayout($view, $data = [], $layout = 'layouts/main') {
        ob_start();
        self::render($view, $data);
        $content = ob_get_clean();

        $data['content'] = $content;
        self::render($layout, $data);
    }
}
