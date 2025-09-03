<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AtendimentoClinicoNewResource\Pages;
use App\Filament\Resources\AtendimentoClinicoNewResource\RelationManagers\EncaminhamentoRelationManager;
use App\Filament\Resources\AtendimentoClinicoNewResource\RelationManagers\ReceituarioRelationManager;
use App\Filament\Resources\AtendimentoClinicoNewResource\RelationManagers\SolicitacaoExameRelationManager;
use App\Models\AtendimentoClinico;
//use App\Models\AtendimentoClinicoNew;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Doenca;
use App\Models\Estado;
use Filament\Forms\Set;
use App\Models\Medicamento;
use App\Models\SolicitacaoExame;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Modal;
use Illuminate\Support\Carbon;

class AtendimentoClinicoNewResource extends Resource
{
    protected static ?string $model = AtendimentoClinico::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationGroup = 'Atendimentos';

    protected static ?string $navigationLabel = 'Atendimentos Clínicos';

    protected static string $modalWidth = 'full';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Dados do Paciente')
                    ->schema([
                        Forms\Components\Select::make('medico_id')
                            ->label('Médico')
                            ->relationship('medico', 'nome')
                            ->default('1')
                            ->searchable()
                            ->required(),

                        Forms\Components\Select::make('paciente_id')
                            ->label('Paciente')
                            ->relationship('paciente', 'nome')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, $get) {
                                $paciente = $get('paciente_id');
                                $set('paciente', $paciente);

                                $ultimoAtendimento = AtendimentoClinico::where('paciente_id', $paciente)
                                    ->orderBy('created_at', 'desc')
                                    ->first();

                                if ($ultimoAtendimento) {
                                    $receituario = optional($ultimoAtendimento->receituario)->isNotEmpty() ? $ultimoAtendimento->receituario->map(function ($item) {
                                        return $item->medicamento ? $item->medicamento->nome : 'Medicamento não encontrado';
                                    })->implode(', ') : 'Nenhum medicamento';

                                    $solicitacaoExames = optional($ultimoAtendimento->solicitacaoExames)->isNotEmpty() ? $ultimoAtendimento->solicitacaoExames->map(function ($item) {
                                        return $item->resultado ? $item->resultado : 'Exame não encontrado';
                                    })->implode(', ') : 'Nenhum exame';

                                    // --- Corrected Encaminhamentos Logic ---
                                    $encaminhamentos = optional($ultimoAtendimento->encaminhamento)->isNotEmpty()
                                        ? $ultimoAtendimento->encaminhamento->map(function ($item) {
                                            // Check if the single 'especialidade' model exists before accessing its 'nome'
                                            return $item->especialidade ? $item->especialidade->nome : 'Especialidade não encontrada';
                                        })->implode(', ')
                                        : 'Nenhum encaminhamento';
                                    // ------------------------------------

                                    Notification::make()
                                        ->title('Último atendimento')
                                        ->body(
                                            '<b>Data</b>: ' . $ultimoAtendimento->data_hora_atendimento->format('d/m/Y') . '<br>
                                                <br><b>Queixa principal</b>: ' . $ultimoAtendimento->qp . '<br>
                                                <br><b>História Clínica</b>: ' . $ultimoAtendimento->hdp . '<br>
                                                <br><b>Receituário</b>: ' . $receituario . '<br>
                                                <br><b>Exames Solicitados</b>: ' . $solicitacaoExames . '<br>
                                                <br><b>Encaminhamentos</b>: ' . $encaminhamentos . '<br>
                                                <br><b>Evolução</b>: ' . $ultimoAtendimento->evolucao . '<br>'
                                        )
                                        ->info()
                                        ->persistent()
                                        ->send();
                                }
                            })
                            ->createOptionForm([
                                Forms\Components\Fieldset::make('Dados Pessoais')
                                    ->schema([
                                        Forms\Components\TextInput::make('nome')
                                            ->required(true)
                                            ->maxLength(100)
                                            ->label('Nome Completo')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make(['
                                                default' => 1, // 1 coluna em dispositivos móveis
                                                'md' => 2,      // 2 colunas em tablets
                                                'lg' => 3,      // 3 colunas em desktops
                                            ])
                                            ->schema([
                                                Forms\Components\DatePicker::make('data_nascimento')
                                                    ->required(false)
                                                    ->label('Data de Nascimento'),
                                                Forms\Components\TextInput::make('cpf')
                                                    ->required(false)
                                                    ->maxLength(14)
                                                    ->mask('999.999.999-99')
                                                    ->unique(ignoreRecord: true)
                                                    ->label('CPF')
                                                    ->rule(function () {
                                                        return function ($attribute, $value, $fail) {
                                                            $cpf = preg_replace('/\D/', '', $value);
                                                            if (strlen($cpf) !== 11) {
                                                                return $fail('O CPF deve conter 11 dígitos.');
                                                            }
                                                            if (preg_match('/(\d)\1{10}/', $cpf)) {
                                                                return $fail('CPF inválido.');
                                                            }
                                                            for ($t = 9; $t < 11; $t++) {
                                                                $d = 0;
                                                                for ($c = 0; $c < $t; $c++) {
                                                                    $d += $cpf[$c] * (($t + 1) - $c);
                                                                }
                                                                $d = ((10 * $d) % 11) % 10;
                                                                if ($cpf[$c] != $d) {
                                                                    return $fail('CPF inválido.');
                                                                }
                                                            }
                                                        };
                                                    }),
                                                Forms\Components\TextInput::make('rg')
                                                    ->maxLength(20)
                                                    ->nullable()
                                                    ->label('RG'),
                                            ]),
                                        Forms\Components\Grid::make([
                                            'default' => 1, // 1 coluna em dispositivos móveis
                                            'md' => 2,      // 2 colunas em tablets
                                            'lg' => 3,      // 3 colunas em desktops
                                        ])
                                            ->schema([
                                                Forms\Components\Select::make('genero')
                                                    ->options([
                                                        1 => 'Masculino',
                                                        2 => 'Feminino',
                                                        3 => 'Outro',
                                                    ])
                                                    ->required(false)
                                                    ->label('Gênero'),
                                                Forms\Components\Select::make('estado_civil')
                                                    ->options([
                                                        1 => 'Solteiro',
                                                        2 => 'Casado',
                                                        3 => 'Divorciado',
                                                        4 => 'Viúvo',
                                                    ])
                                                    ->required(false)
                                                    ->label('Estado Civil'),
                                                Forms\Components\TextInput::make('profissao')
                                                    ->maxLength(100)
                                                    ->nullable()
                                                    ->label('Profissão'),
                                            ]),
                                    ])
                                    ->columns(['default' => 1]),

                                Forms\Components\Fieldset::make('Endereço')
                                    ->schema([
                                        Forms\Components\TextInput::make('endereco_completo')
                                            ->required(false)
                                            ->maxLength(200)
                                            ->label('Endereço Completo')
                                            ->columnSpanFull(),
                                        Forms\Components\Grid::make([
                                            'default' => 1, // 1 coluna em dispositivos móveis
                                            'md' => 2,      // 2 colunas em tablets
                                            'lg' => 2,      // 3 colunas em desktops
                                        ])
                                            ->schema([
                                                Forms\Components\Select::make('estado_id')
                                                    ->label('Estado')
                                                    ->native(false)
                                                    ->searchable()
                                                    ->required(false)
                                                    ->options(\App\Models\Estado::all()->pluck('nome', 'id')->toArray())
                                                    ->live(),
                                                Forms\Components\Select::make('cidade_id')
                                                    ->label('Cidade')
                                                    ->native(false)
                                                    ->searchable()
                                                    ->required(false)
                                                    ->options(function (callable $get) {
                                                        $estado = \App\Models\Estado::find($get('estado_id'));
                                                        if (!$estado) {
                                                            return [];
                                                        }
                                                        return $estado->cidade->pluck('nome', 'id');
                                                    })
                                                    ->reactive(),
                                            ]),
                                    ])
                                    ->columns(['default' => 1]),

                                Forms\Components\Fieldset::make('Contato')
                                    ->schema([
                                        Forms\Components\Grid::make([
                                            'default' => 1, // 1 coluna em dispositivos móveis
                                            'md' => 2,      // 2 colunas em tablets
                                            'lg' => 2,      // 3 colunas em desktops
                                        ])
                                            ->schema([
                                                Forms\Components\TextInput::make('telefone')
                                                    ->required(false)
                                                    ->maxLength(20)
                                                    ->mask('(99) 99999-9999')
                                                    ->label('Telefone'),
                                                Forms\Components\TextInput::make('email')
                                                    ->email()
                                                    ->required(false)
                                                    ->maxLength(100)
                                                    ->unique(ignoreRecord: true)
                                                    ->label('Email'),
                                            ]),
                                    ])
                                    ->columns([
                                        'default' => 1, // 1 coluna em dispositivos móveis
                                        'md' => 2,      // 2 colunas em tablets
                                        'lg' => 3,      // 3 colunas em desktops
                                    ]),

                                Forms\Components\Fieldset::make('Emergência')
                                    ->schema([
                                        Forms\Components\Grid::make([
                                            'default' => 1, // 1 coluna em dispositivos móveis
                                            'md' => 2,      // 2 colunas em tablets
                                            'lg' => 2,      // 3 colunas em desktops
                                        ])
                                            ->schema([
                                                Forms\Components\TextInput::make('contato_emergencia')
                                                    ->required(false)
                                                    ->maxLength(100)
                                                    ->mask('(99) 99999-9999')
                                                    ->label('Contato de Emergência'),
                                                Forms\Components\Select::make('grau_parentesco')
                                                    ->options([
                                                        1 => 'Pai/Mãe',
                                                        2 => 'Filho(a)',
                                                        3 => 'Cônjuge',
                                                        4 => 'Outro',
                                                    ])
                                                    ->required(false)
                                                    ->label('Grau de Parentesco'),
                                            ]),
                                    ])
                                    ->columns([
                                        'default' => 1, // 1 coluna em dispositivos móveis
                                        'md' => 2,      // 2 colunas em tablets
                                        'lg' => 3,      // 3 colunas em desktops
                                    ]),

                                Forms\Components\Fieldset::make('Convênio')
                                    ->schema([
                                        Forms\Components\TextInput::make('convenio')
                                            ->maxLength(100)
                                            ->nullable()
                                            ->label('Convênio'),
                                    ])
                                    ->columns([
                                        'default' => 1, // 1 coluna em dispositivos móveis
                                        'md' => 2,      // 2 colunas em tablets
                                        'lg' => 3,      // 3 colunas em desktops
                                    ]),
                            ]),
                        Forms\Components\DateTimePicker::make('data_hora_atendimento')
                            ->label('Data/Hora do Atendimento')
                            ->default(now()->format('Y-m-d H:i:s'))
                            ->required(),

                        // Define o status como 1 ao criar
                        Forms\Components\Hidden::make('status')
                            ->default('1'),

                        Forms\Components\Fieldset::make('Queixa Principal e História')
                            ->schema([
                                Forms\Components\Textarea::make('qp')
                                    ->label('Queixa Principal')
                                    ->autosize(),
                                Forms\Components\Textarea::make('hdp')
                                    ->label('História da Doença Atual')
                                    ->autosize(),

                                Forms\Components\CheckboxList::make('doencas_preexistentes')
                                    ->label('Doenças Preexistentes')
                                    ->relationship('doenca', 'nome', fn($query) => $query->where('grave', 1))
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome . ' (CID: ' . $record->cid . ')')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        // Recupera o valor atual do campo
                                        $valorAtual = $get('data_inicio_sintomas') ?? '';

                                        if (is_array($state) && count($state)) {
                                            // Busca os nomes das doenças selecionadas
                                            $doencas = Doenca::whereIn('id', $state)->pluck('nome')->toArray();
                                            // Junta os nomes em uma string, cada um em uma linha terminando com " -"
                                            $novasLinhas = array_map(fn($nome) => trim($nome) . ' - ', $doencas);

                                            // Quebra o valor atual em linhas e remove espaços extras
                                            $linhasExistentes = array_map('trim', explode("\n", $valorAtual));

                                            // Adiciona apenas as linhas que ainda não existem (comparando até o " -")
                                            foreach ($novasLinhas as $linha) {
                                                $existe = false;
                                                $parteLinha = strtolower(trim(strtok($linha, '-')));
                                                foreach ($linhasExistentes as $existente) {
                                                    $parteExistente = strtolower(trim(strtok($existente, '-')));
                                                    if ($parteLinha === $parteExistente) {
                                                        $existe = true;
                                                        break;
                                                    }
                                                }
                                                if ($linha && !$existe) {
                                                    $linhasExistentes[] = $linha;
                                                }
                                            }

                                            // Atualiza o campo com todas as linhas únicas
                                            $set('data_inicio_sintomas', implode("\n", array_filter($linhasExistentes)));
                                        }
                                    }),



                                Forms\Components\TextArea::make('data_inicio_sintomas')
                                    ->autosize()
                                    ->label('Data de Início dos Sintomas'),
                                Forms\Components\Textarea::make('cirurgias_hospitalizacoes')
                                    ->label('Cirurgias/Hospitalizações')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make('Alergias e Medicamentos')
                            ->schema([
                                Forms\Components\CheckboxList::make('medicamento_alergia')
                                    ->label('Alergia Medicamentosa')
                                    ->relationship('medicamentoAlergias', 'nome', fn($query) => $query->where('alergia', 1))
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome)
                                    ->searchable()
                                    ->columns(2),
                                Forms\Components\TextArea::make('outros_alergias')
                                    ->autosize()
                                    ->label('Outras Alergias'),
                                Forms\Components\CheckboxList::make('medicamento_uso')
                                    ->label('Medicamentos em Uso Contínuo')
                                    ->relationship('medicamentoUso', 'nome', fn($query) => $query->where('uso_continuo', 1))
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        // Recupera o valor atual do campo
                                        $valorAtual = $get('medicamento_uso_detalhes') ?? '';

                                        if (is_array($state) && count($state)) {
                                            // Busca os nomes das doenças selecionadas
                                            $medicamento = Medicamento::whereIn('id', $state)->pluck('nome')->toArray();
                                            // Junta os nomes em uma string, cada um em uma linha terminando com " -"
                                            $novasLinhas = array_map(fn($nome) => trim($nome) . ' - ', $medicamento);

                                            // Quebra o valor atual em linhas e remove espaços extras
                                            $linhasExistentes = array_map('trim', explode("\n", $valorAtual));

                                            // Adiciona apenas as linhas que ainda não existem (comparando até o " -")
                                            foreach ($novasLinhas as $linha) {
                                                $existe = false;
                                                $parteLinha = strtolower(trim(strtok($linha, '-')));
                                                foreach ($linhasExistentes as $existente) {
                                                    $parteExistente = strtolower(trim(strtok($existente, '-')));
                                                    if ($parteLinha === $parteExistente) {
                                                        $existe = true;
                                                        break;
                                                    }
                                                }
                                                if ($linha && !$existe) {
                                                    $linhasExistentes[] = $linha;
                                                }
                                            }

                                            // Atualiza o campo com todas as linhas únicas
                                            $set('medicamento_uso_detalhes', implode("\n", array_filter($linhasExistentes)));
                                        }
                                    })

                                    ->searchable()
                                    ->columns(2),
                                Forms\Components\Textarea::make('medicamento_uso_detalhes')
                                    ->label('Detalhes dos Medicamentos em Uso')
                                    ->autosize(),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make('Histórico Familiar')
                            ->schema([
                                Forms\Components\CheckboxList::make('doencas_familiares')
                                    ->label('Doenças em Famíliares')
                                    ->relationship('doencaFamiliar', 'nome', fn($query) => $query->where('grave', 1))
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome . ' (CID: ' . $record->cid . ')')
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, $get) {
                                        // Recupera o valor atual do campo
                                        $valorAtual = $get('doenca_familiar_parentesco') ?? '';

                                        if (is_array($state) && count($state)) {
                                            // Busca os nomes das doenças selecionadas
                                            $doencas = Doenca::whereIn('id', $state)->pluck('nome')->toArray();
                                            // Junta os nomes em uma string, cada um em uma linha terminando com " -"
                                            $novasLinhas = array_map(fn($nome) => trim($nome) . ' - ', $doencas);

                                            // Quebra o valor atual em linhas e remove espaços extras
                                            $linhasExistentes = array_map('trim', explode("\n", $valorAtual));

                                            // Adiciona apenas as linhas que ainda não existem (comparando até o " -")
                                            foreach ($novasLinhas as $linha) {
                                                $existe = false;
                                                $parteLinha = strtolower(trim(strtok($linha, '-')));
                                                foreach ($linhasExistentes as $existente) {
                                                    $parteExistente = strtolower(trim(strtok($existente, '-')));
                                                    if ($parteLinha === $parteExistente) {
                                                        $existe = true;
                                                        break;
                                                    }
                                                }
                                                if ($linha && !$existe) {
                                                    $linhasExistentes[] = $linha;
                                                }
                                            }

                                            // Atualiza o campo com todas as linhas únicas
                                            $set('doenca_familiar_parentesco', implode("\n", array_filter($linhasExistentes)));
                                        }
                                    })
                                    ->searchable()
                                    ->columns(2),
                                Forms\Components\TextArea::make('doenca_familiar_parentesco')
                                    ->autosize()
                                    ->label('Doenças na Família e Parentesco'),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make('Estilo de Vida')
                            ->schema([
                                Forms\Components\ToggleButtons::make('tabagismo')
                                    ->label('Tabagismo')
                                    ->boolean()
                                    ->grouped()
                                    ->default(false)
                                    ->inline(false),
                                Forms\Components\ToggleButtons::make('alcoolismo')
                                    ->label('Alcoolismo')
                                    ->boolean()
                                    ->default(false)
                                    ->grouped()
                                    ->inline(false),
                                Forms\Components\ToggleButtons::make('drogas')
                                    ->label('Drogas')
                                    ->boolean()
                                    ->default(false)
                                    ->grouped()
                                    ->inline(false),
                                Forms\Components\ToggleButtons::make('atividade_fisica')
                                    ->label('Atividade Física')
                                    ->boolean()
                                    ->default(false)
                                    ->grouped()
                                    ->inline(false),
                                Forms\Components\ToggleButtons::make('dieta')
                                    ->label('Dieta')
                                    ->boolean()
                                    ->default(false)
                                    ->grouped()
                                    ->inline(false),
                                Forms\Components\Textarea::make('obs_estilo_vida')
                                    ->label('Observações sobre Estilo de Vida')
                                    ->autosize(),
                                // ->columnSpanFull(),
                            ])
                            ->columns([
                                'default' => 1,
                                'sm' => 2,
                                'md' => 2,
                                'lg' => 2,
                                'xl' => 2,
                                '2xl' => 2,
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make('Exame Físico')
                            ->schema([
                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'sm' => 2,
                                    'md' => 3,
                                    'lg' => 4,
                                ])->schema([
                                    Forms\Components\DatePicker::make('dum')
                                        ->label('DUM')
                                        ->visible(function ($get, $record) {
                                            // Se estiver editando um registro existente
                                            if ($record && $record->paciente) {
                                                return $record->paciente->genero == 2;
                                            }

                                            // Se estiver criando um novo registro
                                            $pacienteId = $get('paciente_id');
                                            if (!$pacienteId) {
                                                return false;
                                            }

                                            $paciente = \App\Models\Paciente::find($pacienteId);
                                            return $paciente && $paciente->genero == 2;
                                        }),
                                    Forms\Components\TextInput::make('pa')
                                        ->label('PA(mmHg)'),
                                    Forms\Components\TextInput::make('peso')
                                        ->label('Peso')
                                        ->hint('kg' . ' (Ex: 70.5)')
                                        ->numeric()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Set $set, $get) {
                                            $peso = floatval($state);
                                            $altura = floatval($get('altura'));
                                            if ($peso > 0 && $altura > 0) {
                                                $imc = $peso / ($altura * $altura);
                                                $set('imc', number_format($imc, 2, '.', ''));
                                            }
                                        }),
                                    Forms\Components\TextInput::make('altura')
                                        ->label('Altura (cm)')
                                        ->numeric()
                                        ->hint('cm' . ' (Ex: 175)')
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, Set $set, $get) {
                                            $alturaCm = floatval($state);
                                            $peso = floatval($get('peso'));
                                            // Converte altura de cm para metros
                                            $altura = $alturaCm > 0 ? $alturaCm / 100 : 0;
                                            if ($peso > 0 && $altura > 0) {
                                                $imc = $peso / ($altura * $altura);
                                                $set('imc', number_format($imc, 2, '.', ''));
                                            }
                                        }),
                                    Forms\Components\TextInput::make('imc')
                                        ->readOnly()
                                        ->label('IMC')
                                        ->suffix(function ($state) {
                                            $imc = floatval($state);
                                            if ($imc > 0 && $imc < 18) {
                                                return ' 🟨'; // amarelo
                                            } elseif ($imc >= 18.5 && $imc <= 24.9) {
                                                return '🟩';
                                            } elseif ($imc >= 25 && $imc <= 29.9) {
                                                return '🟧';
                                            } elseif ($imc > 29.9) {
                                                return '🟥';
                                            }
                                            return '';
                                        })
                                        ->hint(function ($state) {
                                            $imc = floatval($state);
                                            if ($imc > 0 && $imc < 18) {
                                                return 'Abaixo do peso';
                                            } elseif ($imc >= 18.5 && $imc <= 24.9) {
                                                return 'Peso Normal';
                                            } elseif ($imc >= 25 && $imc <= 29.9) {
                                                return 'Sobrepeso';
                                            } elseif ($imc > 29.9) {
                                                return 'Obesidade';
                                            }
                                            return '';
                                        })
                                        ->extraAttributes(function ($state) {
                                            $imc = floatval($state);
                                            if ($imc > 0 && $imc < 18) {
                                                return ['style' => 'color: #eab308; font-weight: bold;']; // amarelo
                                            } elseif ($imc >= 18.5 && $imc <= 24.9) {
                                                return ['style' => 'color: #22c55e; font-weight: bold;']; // verde
                                            } elseif ($imc >= 25 && $imc <= 29.9) {
                                                return ['style' => 'color: #f97316; font-weight: bold;']; // laranja
                                            } elseif ($imc > 29.9) {
                                                return ['style' => 'color: #ef4444; font-weight: bold;']; // vermelho
                                            }
                                            return [];
                                        }),
                                    Forms\Components\TextInput::make('fc')
                                        ->label('FC (bpm)'),
                                    Forms\Components\TextInput::make('fr')
                                        ->label('FR (/min)'),
                                    Forms\Components\TextInput::make('temperatura')
                                        ->label('Temperatura (°C)'),
                                    Forms\Components\TextInput::make('saturacao')
                                        ->label('Saturação  (%)'),
                                ]),
                                Forms\Components\Grid::make([
                                    'default' => 1,
                                    'md' => 2,
                                ])->schema([
                                    Forms\Components\Textarea::make('obs_exame_fisico')
                                        ->label('Observações do Exame Físico')
                                        ->columnSpan(3)
                                        ->autosize(),

                                ]),
                            ])
                            ->columnSpanFull(),

                        Forms\Components\Fieldset::make('Diagnóstico e Conduta')
                            ->schema([
                                Forms\Components\Select::make('hipotese_diagnostica_id')
                                    ->label('Hipótese Diagnóstica')
                                    ->relationship('hipoteseDiagnostica', 'nome')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->nome . ' (CID: ' . $record->cid . ')')
                                    ->required(false)
                                    ->preload()
                                    ->searchable(['nome', 'cid'])
                                    ->multiple(),

                                Forms\Components\Textarea::make('hipotese_diagnostica_detalhes')
                                    ->label('Detalhes da Hipótese Diagnóstica')
                                    ->autosize(),
                                Forms\Components\Textarea::make('conduta')
                                    ->label('Conduta')
                                    ->autosize(),
                                Forms\Components\FileUpload::make('anexos_pre_exames')
                                    ->label('Anexos/Pré-Exames')
                                    ->panelLayout('grid')
                                    ->maxSize(2048)
                                    ->openable()
                                    ->directory('atendimentos/anexos_pre_exames')
                                    ->multiple(),


                            ])
                            ->columnSpanFull(),
                        Forms\Components\Fieldset::make('Retorno de Consulta')
                            ->visible(fn($context) => $context == 'edit')
                            ->schema([
                                Forms\Components\FileUpload::make('anexos_resultados')
                                    ->label('Anexos/Resultados')
                                    ->visible(fn($context) => $context == 'edit')
                                    ->panelLayout('grid')
                                    ->maxSize(2048)
                                    ->openable()
                                    ->directory('atendimentos/anexos_resultados')
                                    ->multiple(),
                                Forms\Components\Textarea::make('evolucao')
                                    ->label('Evolução')
                                    ->visible(fn($context) => $context == 'edit')
                                    ->autosize(),
                                Forms\Components\DateTimePicker::make('data_hora_retorno')
                                    ->label('Data/Hora do Retorno')
                                    ->default(now()->format('Y-m-d H:i:s'))
                                    ->visible(fn($context) => $context == 'edit')
                                    ->required(false),
                                Forms\Components\ToggleButtons::make('status')
                                    ->label('Status do Atendimento')
                                    ->inline()
                                    ->visible(fn($context) => $context == 'edit')
                                    ->options([
                                        '1' => 'Iniciado',
                                        '2' => 'Finalizado',
                                        '0' => 'Cancelado',
                                    ])
                                    ->icons([
                                        '1' => 'heroicon-o-check-circle',
                                        '2' => 'heroicon-o-x-circle',
                                        '0' => 'heroicon-o-exclamation-circle',
                                    ])
                                    ->colors([
                                        '1' => 'info',
                                        '2' => 'success',
                                        '0' => 'danger',
                                    ])
                                    ->required(),
                            ])
                            ->columnSpanFull(),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('paciente.nome')
                    ->label('Paciente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('data_hora_atendimento')
                    ->label('Data/Hora do Atendimento')
                    ->alignCenter()
                    ->dateTime('d/m/Y H:i'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => match ($state) {
                        '1' => 'Iniciado',
                        '2' => 'Finalizado',
                        '0' => 'Cancelado',
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        '1' => 'warning',
                        '2' => 'success',
                        '0' => 'danger',
                    })
                    ->icon(fn($state) => match ($state) {
                        '1' => 'heroicon-o-check-circle',
                        '2' => 'heroicon-o-x-circle',
                        '0' => 'heroicon-o-exclamation-circle',
                    })
                    ->sortable()
                    ->searchable()

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        '1' => 'Iniciada',
                        '2' => 'Finalizada',
                        '0' => 'Cancelada',
                    ])
                    ->label('Status'),
                SelectFilter::make('paciente_id')
                    ->relationship('paciente', 'nome')
                    ->label('Paciente'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('Prontuário')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn(AtendimentoClinico $record) => route('documentos.prontuario', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('retorno_consulta')
                    ->icon('heroicon-o-check')
                    ->label(fn($record) => $record->status == '2' ? 'Atendimento Finalizado' : 'Finalizar Atendimento')
                    ->color(fn($record) => $record->status == '2' ? 'success' : 'info')
                    ->modalHeading('Finalização do Atendimento')
                    ->form([
                        Forms\Components\Textarea::make('evolucao')
                            ->label('Evolução')
                            ->autosize(),
                        Forms\Components\TextArea::make('resultado_exames')
                            ->label('Resultado dos Exames')
                            ->autosize(),
                        Forms\Components\FileUpload::make('anexos_resultados')
                            ->label('Anexos/Resultados')
                            ->directory('atendimentos/anexos_resultados')
                            ->openable()
                            ->maxSize(2048)
                            ->panelLayout('grid')
                            ->multiple(),
                        Forms\Components\DateTimePicker::make('data_hora_retorno')
                            ->label('Data/Hora do Retorno')

                            ->required(false),
                        Forms\Components\ToggleButtons::make('status')
                            ->label('Status do Atendimento')
                            ->inline()
                            ->default('2')
                            ->options([
                                '1' => 'Iniciado',
                                '2' => 'Finalizado',
                                '0' => 'Cancelado',
                            ])
                            ->icons([
                                '1' => 'heroicon-o-check-circle',
                                '2' => 'heroicon-o-x-circle',
                                '0' => 'heroicon-o-exclamation-circle',
                            ])
                            ->colors([
                                '1' => 'info',
                                '2' => 'success',
                                '0' => 'danger',
                            ])
                            ->required(),
                    ])
                    ->mountUsing(function (Forms\ComponentContainer $form, AtendimentoClinico $record) {
                        $form->fill([
                            'evolucao' => $record->evolucao,
                            'anexos_resultados' => $record->anexos_resultados,
                            'data_hora_retorno' => $record->data_hora_retorno == null
                                ? Carbon::now()->format('Y-m-d H:i:s')
                                : $record->data_hora_retorno,
                            'resultado_exames' => $record->resultado_exames,
                            'status' => $record->status,
                        ]);
                    })
                    ->action(function (AtendimentoClinico $record, array $data) {
                        $record->update([
                            'anexos_resultados' => $data['anexos_resultados'],
                            'evolucao' => $data['evolucao'],
                            'data_hora_retorno' => $data['data_hora_retorno'],
                            'resultado_exames' => $data['resultado_exames'],
                            'status' => $data['status'],
                        ]);
                    }),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ReceituarioRelationManager::class,
            SolicitacaoExameRelationManager::class,
            EncaminhamentoRelationManager::class,

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAtendimentoClinicoNews::route('/'),
            'create' => Pages\CreateAtendimentoClinicoNew::route('/create'),
            'edit' => Pages\EditAtendimentoClinicoNew::route('/{record}/edit'),
        ];
    }
}
