<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AtendimentoClinico extends Model
{
    use HasFactory;

    protected $table = 'atendimento_clinicos';

    protected $fillable = [
        'paciente_id',
        'data_hora_atendimento',
        'medico_id',
        'tipo_atendimento',
        'qp',
        'hdp',
        'doenca_preexistente',
        'data_inicio_sintomas',
        'cirurgias_hospitalizacoes',
        'medicamento_alergias_id',
        'alimento_alergias',
        'outros_alergias',
        'medicamento_uso_id',
        'medicamento_uso_detalhes',
        'doenca_familiar_id',
        'doenca_familiar_parentesco',
        'tabagismo',
        'alcoolismo',
        'drogas',
        'atividade_fisica',
        'dieta',
        'obs_estilo_vida',
        'dum',
        'pa',
        'peso',
        'altura',
        'imc',
        'fc',
        'fr',
        'temperatura',
        'saturacao',
        'obs_exame_fisico',
        'hipotese_diagnostica_id',
        'hipotese_diagnostica_detalhes',
        'medicamentos_id',
        'exames_id',
        'resultados_exames',
        'encaminhamentos_id',
        'orientacoes',
        'evolucao',
        'status',
        'observacoes',
        'anexos_resultados'
    ];

    protected $casts = [
        'data_hora_atendimento' => 'datetime',
        'medicamentos_id' => 'array',
        'exames_id' => 'array',
        'anexos_resultados' => 'json',
        'doenca_preexistente' => 'array',
        'hipotese_diagnostica_id' => 'array',
        'encaminhamentos_id' => 'array',
        // 'hipotese_diagnostica_id' should not be cast to array or json
    ];  
    
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico()
    {
        return $this->belongsTo(User::class);
    }

    public function doenca()
    {
        return $this->belongsToMany(Doenca::class)
            ->withPivot('data_inicio'); // Adiciona o campo extra 'texto' da tabela pivô
            
    }

    public function medicamentoAlergias()
    {
        return $this->belongsToMany(Medicamento::class);
    }

    public function medicamentoUso()
    {
        return $this->belongsToMany(Medicamento::class,);
    }

    public function doencaFamiliar()
    {
        // Referencia explicitamente a tabela pivô e as chaves estrangeiras
        return $this->belongsToMany(
            Doenca::class,
            'atendimento_clinico_doenca_familiar', // nome da tabela pivô
            'atendimento_clinico_id', // chave estrangeira deste modelo na tabela pivô
            'doenca_id' // chave estrangeira do modelo relacionado na tabela pivô
        ); // exemplo de campo extra na tabela pivô
    }

    public function hipoteseDiagnostica()
    {
        return $this->belongsTo(Doenca::class);
    }

    public function exames()
    {
        return $this->belongsTo(Exame::class);
    }

    public function encaminhamentos()
    {
        return $this->belongsTo(Especialidade::class);
    }

    public function medicamentos()
    {
        return $this->belongsTo(Medicamento::class);
    }

}
