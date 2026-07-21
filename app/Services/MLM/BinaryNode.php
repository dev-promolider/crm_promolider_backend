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
        $now = now();
        $isRequestApproved = isset($this->rawUserData['request']) && $this->rawUserData['request'] == 2;
        
        $opcActive = $isRequestApproved && (
            empty($this->rawUserData['expiration_date']) || 
            \Carbon\Carbon::parse($this->rawUserData['expiration_date']) > $now
        );
        
        $membershipActive = $isRequestApproved && (
            !empty($this->rawUserData['expiration_membership_date']) && 
            \Carbon\Carbon::parse($this->rawUserData['expiration_membership_date']) > $now
        );
        $leftArray = $this->left ? $this->left->toArray() : null;
        $rightArray = $this->right ? $this->right->toArray() : null;
        
        $qualified = $this->rawUserData['qualified'] ?? false;

        return array_merge($this->rawUserData, [
            'id' => $this->userId,
            'name' => $this->name,
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
