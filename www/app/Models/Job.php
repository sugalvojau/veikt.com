<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    protected $table = 'job';

    public function addNote(Note $note)
    {
        return $this->notes()->save($note);
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}
