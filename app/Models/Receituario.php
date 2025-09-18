<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receituario extends Model
{
    use HasFactory;

    protected $table = 'receituarios';
    
    protected $fillable = [
        'atendimento_clinico_id',
        'medicamento_id',
        'dosagem',
        'qtd',
        'forma_uso',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function atendimentoClinico()
    {
        return $this->belongsTo(AtendimentoClinico::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }   
    
}
