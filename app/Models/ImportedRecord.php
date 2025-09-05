<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedRecord extends Model
{
    protected $fillable = ['form_id', 'original_columns', 'mapping_used', 'raw_row', 'mapped_row', 'quality_score'];
    protected $casts = [
        'original_columns' => 'array',
        'mapping_used' => 'array',
        'raw_row' => 'array',
        'mapped_row' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
