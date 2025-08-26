<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $fillable = ['project_id', 'external_id', 'title', 'meta'];
    protected $casts = ['meta' => 'array'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
    public function fields()
    {
        return $this->hasMany(FormField::class);
    }
    public function importedRecords()
    {
        return $this->hasMany(ImportedRecord::class);
    }
}
