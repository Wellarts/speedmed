<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Exame extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'exames';

    protected $fillable = [
        'nome',
        'descricao',
        'tipo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function atendimentoClinicos()
    {
        return $this->hasMany(AtendimentoClinico::class, 'exame_id');
    }

    public function solicitacaoExames()
    {
        return $this->hasMany(SolicitacaoExame::class, 'exames_id');
    }

    public function grupoExames()
    {
        return $this->hasMany(GrupoExame::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
