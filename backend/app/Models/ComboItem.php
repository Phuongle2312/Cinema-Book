<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    use HasFactory;

    /**
     * The primary key associated with the table.
     */
    protected $primaryKey = 'combo_item_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'combo_id',
        'item_name',
        'item_size',
        'quantity',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'quantity' => 'integer',
    ];

    /**
     * Get the combo that owns this item.
     */
    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id', 'combo_id');
    }
}
