<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalAtendimento extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome',
        'endereco',
        'telefone',
        'email',
    ];

    public function atendimentos()
    {
        return $this->hasMany(AtendimentoClinico::class);
    }
}
