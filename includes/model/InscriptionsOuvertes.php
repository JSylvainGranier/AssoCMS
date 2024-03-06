<?php
class InscriptionsOuvertes {
    
    
    public function getPrimaryKey() {
        return 0;
    }
    public function setPrimaryKey($newId) {
        
    }
    private static $memberDeclaration;
    static function getMembersDeclaration() {
        
    }
    public function getShortToString() {
        return "InscriptionsOuvertes";
    }
    protected function getNaturalOrderColumn() {
        return "debutDisponibilite";
    }
}