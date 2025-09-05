<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'version', 'raw_json', 'file_hash'];

    protected $casts = ['raw_json' => 'array'];

    public function forms()
    {
        return $this->hasMany(Form::class);
    }
}
