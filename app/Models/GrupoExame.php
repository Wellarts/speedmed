<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrupoExame extends Model
{
    use HasFactory;

    protected $fillable = ['nome', 'exame_id'];

    protected $casts = [
        'exame_id' => 'array',
    ];

    public function exames()
    {
        return $this->belongsToMany(Exame::class, 'atendimento_clinico_exame', 'solicitacao_exame_id', 'exame_id')->withTimestamps();
    }

    public function listExames()
    {
        return $this->belongsTo(Exame::class, 'exame_id');
    }
}
