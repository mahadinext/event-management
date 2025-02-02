<?php
namespace App\Core;

class View {
    private $layout = 'master';
    private $sections = [];
    
    public function extend($layout) {
        $this->layout = $layout;
    }
    
    public function section($name) {
        ob_start();
    }
    
    public function endSection($name) {
        $this->sections[$name] = ob_get_clean();
    }
    
    public function renderSection($name) {
        echo $this->sections[$name] ?? '';
    }
    
    public function render($view, $data = []) {
        // Extract data to make variables available in view
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once(__DIR__ . "/../../views/{$view}.php");
        
        // Get the content
        $content = ob_get_clean();
        
        // If using a layout
        if ($this->layout) {
            ob_start();
            require_once(__DIR__ . "/../../views/layouts/{$this->layout}.php");
            return ob_get_clean();
        }
        
        return $content;
    }
}