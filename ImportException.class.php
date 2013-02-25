<?php

/**
 * Manage Import exception
 * @package OpenM 
 * @author Gael SAUNIER
 */
class ImportException extends Exception {
    
    public function __construct($className) {
        parent::__construct("$className class or interface or library not found in classPath");
    }
}

?>