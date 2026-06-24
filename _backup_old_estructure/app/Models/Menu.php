<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\SubMenu;

class Menu extends Model
{
    use HasFactory;
    protected $table='menu';
    /**
     * Get all of the submenu for the Menu
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function submenu(): HasMany
    {
        return $this->hasMany(SubMenu::class, 'menu_id')->select('menu_id','url','name','icon','slug');
    }
    public function scopeSltMenu($query){
        return $query->select('id','name','badge','badgeClass','icon','slug','dropdown','url','navheader');
    }
}
