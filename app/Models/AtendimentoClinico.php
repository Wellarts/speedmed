<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AtendimentoClinico extends Model
{
    use HasFactory, LogsActivity;

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
        'conduta',
        'evolucao',
        'data_hora_retorno',
        'status',
        'anexos_resultados',
        'anexos_pre_exames',
        'resultado_exames',
    ];

    protected $casts = [
        'data_hora_atendimento' => 'datetime',        
        'anexos_resultados' => 'json',
        'anexos_pre_exames' => 'json',        
        'doenca_preexistente' => 'array',
        'hipotese_diagnostica_id' => 'array',
       
        // 'hipotese_diagnostica_id' should not be cast to array or json
    ];  
    
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function localAtendimento()
    {
        return $this->belongsTo(LocalAtendimento::class);
    }

    
    public function doenca()
    {
        return $this->belongsToMany(Doenca::class);
            
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

    // public function exames()
    // {
    //     return $this->belongsTo(Exame::class);
    // }

    public function encaminhamento()
    {
        return $this->hasMany(Encaminhamento::class);
    }

    // public function medicamentos()
    // {
    //     return $this->belongsTo(Medicamento::class);
    // }

    public function receituario()
    {
        return $this->hasMany(Receituario::class);
    }

    public function medico() 
    {
        return $this->belongsTo(Medico::class);
    }

    public function solicitacaoExames()
    {
        return $this->hasMany(SolicitacaoExame::class);
    }   
    
    

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }    

}
