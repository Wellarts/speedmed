<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PacienteResource\Pages;
use App\Filament\Resources\PacienteResource\RelationManagers;
use App\Models\Paciente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\Estado;

class PacienteResource extends Resource
{
    protected static ?string $model = Paciente::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Cadastros';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Fieldset::make('Dados Pessoais')
                    ->schema([
                        Forms\Components\TextInput::make('nome')
                            ->required(true)
                            ->maxLength(100)
                            ->label('Nome Completo')
                            ->columnSpanFull(),
                        Forms\Components\Grid::make([
                            'default' => 1, // 1 coluna em dispositivos móveis
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
                                            // Remove non-numeric characters
                                            $cpf = preg_replace('/\D/', '', $value);

                                            // CPF must have 11 digits
                                            if (strlen($cpf) !== 11) {
                                                return $fail('O CPF deve conter 11 dígitos.');
                                            }

                                            // Invalid known CPFs
                                            if (preg_match('/(\d)\1{10}/', $cpf)) {
                                                return $fail('CPF inválido.');
                                            }

                                            // Validate CPF digits
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
                            'sm' => 1, // 1 coluna em dispositivos móveis
                            'md' => 1,      // 2 colunas em tablets
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
                    ->columns([
                        'default' => 1, // 1 coluna em dispositivos móveis
                        'md' => 2,      // 2 colunas em tablets
                        'lg' => 3,      // 3 colunas em desktops
                    ]),

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
                                    ->options(Estado::all()->pluck('nome', 'id')->toArray())
                                    ->live(),
                                Forms\Components\Select::make('cidade_id')
                                    ->label('Cidade')
                                    ->native(false)
                                    ->searchable()
                                    ->required(false)
                                    ->options(function (callable $get) {
                                        $estado = Estado::find($get('estado_id'));
                                        if (!$estado) {
                                            return [];
                                        }
                                        return $estado->cidade->pluck('nome', 'id');
                                    })
                                    ->reactive(),
                            ]),
                    ])
                    ->columns([
                        'default' => 1, // 1 coluna em dispositivos móveis
                        'md' => 2,      // 2 colunas em tablets
                        'lg' => 3,      // 3 colunas em desktops
                    ]),

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
                    ->columns(['default' => 1, 'md' => 1]),

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
            ])
            ->columns([
                'default' => 1, // 1 coluna em dispositivos móveis
                'md' => 2,      // 2 colunas em tablets
                'lg' => 3,      // 3 colunas em desktops
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome Completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('genero')
                    ->label('Gênero')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(
                        function ($record) {
                            return match ($record->genero) {
                                1 => 'Masculino',
                                2 => 'Feminino',
                                3 => 'Outro',
                                default => 'Não Informado',
                            };
                        }
                    ),
                Tables\Columns\TextColumn::make('idade')
                    ->label('Idade')
                    ->alignCenter()
                    ->default('-')
                    ->formatStateUsing(function ($record) {
                        if ($record->data_nascimento) {
                            $birthDate = \Carbon\Carbon::parse($record->data_nascimento);
                            $age = \Carbon\Carbon::now()->diffInYears($birthDate);
                            return $age . ' anos';
                        }
                        return '-';
                    }),


                Tables\Columns\TextColumn::make('cpf')
                    ->label('CPF')
                    ->alignCenter()
                    ->searchable()
                    ->sortable(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManagePacientes::route('/'),
        ];
    }
}
