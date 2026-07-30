<?php

namespace App\Services\MLM;

/**
 * Representación en memoria de un nodo del Árbol Binario MLM.
 * Esta clase NO toca la base de datos, está optimizada para la RAM.
 */
class BinaryNode
{
    public $userId;
    public $name;
    public $leftPoints;
    public $rightPoints;
    public $rank;
    public $rawUserData;
    
    /** @var BinaryNode|null */
    public $left = null;
    
    /** @var BinaryNode|null */
    public $right = null;

    public function __construct(array $userData)
    {
        $this->userId = $userData['id'] ?? null;
        $this->name = $userData['name'] ?? null;
        // Asumimos que podemos inyectar los puntos iniciales si vienen de la DB
        $this->leftPoints = $userData['left_points'] ?? 0;
        $this->rightPoints = $userData['right_points'] ?? 0;
        $this->rank = $userData['rank'] ?? 'Distribuidor';
        $this->rawUserData = $userData;
    }

    /**
     * Serializa el nodo y sus hijos de forma recursiva a un array.
     */
    public function toArray(): array
    {
        $opcActive = $this->rawUserData['is_approved'] == 1;
        $membershipActive = true; // Mock temporal
        
        $leftArray = $this->left ? $this->left->toArray() : null;
        $rightArray = $this->right ? $this->right->toArray() : null;
        
        $qualified = $this->rawUserData['qualified'] ?? false;

        $photoUrl = !empty($this->rawUserData['photo']) 
            ? \App\Helpers\ParseUrl::contacAtrrS3($this->rawUserData['photo']) 
            : null;

        return array_merge($this->rawUserData, [
            'id' => $this->userId,
            'name' => $this->name,
            'photoUrl' => $photoUrl,
            'left_points' => $this->leftPoints,
            'right_points' => $this->rightPoints,
            'rank' => $this->rank,
            'active' => $opcActive,
            'membershipActive' => $membershipActive,
            'qualified' => $qualified,
            'left' => $leftArray,
            'right' => $rightArray,
        ]);
    }
}
