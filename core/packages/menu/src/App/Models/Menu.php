<?php

namespace Bo\MenuCRUD\App\Models;

use Bo\Base\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use CrudTrait;

    protected $table = 'menus';
    protected $fillable = [
        'name',
        'description',
    ];

    public function menuItems()
    {
        return $this->belongsToMany(MenuItem::class, 'menu_items_pivot');
    }
}