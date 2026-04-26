<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacation2026Setting extends Model
{
    use HasFactory;

    protected $table = 'vacation_2026_settings';

    protected $fillable = [
        'entete',
        'considerant',
        'note_titre',
        'decision_titre',
        'presence_titre',
        'decompte_titre',
        'decision_reference',
        'decision_article_1',
        'decision_article_2',
        'signature',
    ];
}
