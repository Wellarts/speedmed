<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SolicitacaoExame extends Model
{
    use HasFactory;

    protected $fillable = [
        'atendimento_clinico_id',
        'exames_id',
        'resultado'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'exame_id' => 'array', // Assuming exame_id can be an array of IDs
    ];

    public function atendimentoClinico()
    {
        return $this->belongsTo(AtendimentoClinico::class);
    }

    
    public function exames()
    {
        return $this->belongsToMany(Exame::class, 'atendimento_clinico_exame', 'solicitacao_exame_id', 'exame_id')->withTimestamps();
    }

    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function($solicitacaoExame) {
            $solicitacaoExame->exames()->detach();
        });
    }
}
