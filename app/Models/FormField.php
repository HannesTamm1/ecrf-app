<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = ['form_id', 'name', 'label', 'type', 'required', 'options', 'logic', 'meta'];
    protected $casts = [
        'required' => 'boolean',
        'options' => 'array',
        'logic' => 'array',
        'meta' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
