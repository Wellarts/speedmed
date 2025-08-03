<!DOCTYPE html>
<html>

<head>
    <title>Prontuário Médico</title>
    <style>
        :root {
            --primary-color: #ac2b38;
            --secondary-color: #6c757d;
            --success-color: #4CAF50;
            --info-color: #2196F3;
            --warning-color: #FFC107;
            --danger-color: #F44336;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #e9ecef;
            --section-bg: #f8f9fa;
        }

        body {
            font-family: 'Roboto', 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;

        }

        .header-section {
            width: 100%;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--border-color);
        }

        .header-section td {
            padding-bottom: 10px;
        }

        .section {
            margin-bottom: 20px;
            padding: 15px;
            font-size: 10pt;
            background: var(--section-bg);
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .section h2 {
            margin-top: 0;
            color: var(--primary-color);
            font-size: 1.0rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
            margin-bottom: 15px;
        }

        .section table {
            width: 100%;
            border-collapse: collapse;
            /* margin-top: 10px; */
            background: white;
        }

        .section table th,
        .section table td {
            padding: 8px 10px;
            /* Adjusted padding for table cells */
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: top;
            /* Ensure content aligns at the top */
        }

        .section table th {
            background-color: var(--light-color);
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }

        .section table td.label {
            font-weight: 600;
            color: var(--dark-color);
            width: 30%;
            /* Adjust label column width as needed */
        }

        .section table td.value {
            color: var(--secondary-color);
            width: 70%;
            /* Adjust value column width as needed */
        }

        /* Specific adjustments for sections with multiple columns of fields */
        .section-2-cols td.label,
        .section-3-cols td.label,
        .section-4-cols td.label {
            width: auto;
            /* Allow label width to adjust */
            white-space: nowrap;
            /* Prevent label wrapping */
            padding-right: 10px;
            /* Space between label and value */
        }

        .section-2-cols td.value,
        .section-3-cols td.value,
        .section-4-cols td.value {
            width: auto;
            /* Allow value width to adjust */
        }

        /* Combined label-value pair styling for flexible columns within tables */
        .field-pair {
            display: flex;
            align-items: baseline;
            /* Aligns text baselines */
            margin-bottom: 5px;
            /* Small margin between field pairs */
        }

        .field-pair .label {
            font-weight: 600;
            color: var(--dark-color);
            margin-right: 5px;
            /* Space between label and value */
            white-space: nowrap;
            /* Prevent label from wrapping */
        }

        .field-pair .value {
            color: var(--secondary-color);
            flex-grow: 1;
            /* Allows value to take up remaining space */
            word-wrap: break-word;
            /* Ensures long text breaks */
        }


        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .status-iniciada {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info-color);
        }

        .status-finalizada {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
        }

        .status-cancelada {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger-color);
        }

        .signature-area {
            margin-top: 50px;
            /* Adjusted margin-top, will be at the bottom of the page */
            text-align: center;
        }

        .signature-box {
            display: inline-block;
            text-align: center;
            margin-top: 100px;
            padding: 10
        }

        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            margin: 0 auto;
            padding-top: 5px;
        }

        @page {
            margin: 1cm;
            size: A4 portrait;
        }

        @media (max-width: 900px) {
            /* No need for grid template columns if using tables */
        }

        /* Estilos para impressão */
        @media print {
            body {
                background: white;
            }

            .container {
                box-shadow: none;
                padding: 0;
            }

            .section {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .signature-area {
                position: absolute;
                /* or fixed, depending on exact desired behavior for multi-page */
                bottom: 30px;
                /* Adjust as needed */
                width: 100%;
                left: 0;
                right: 0;
                margin: auto;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <table class="header-section">
            <tr>
                <td style="width: 20%; vertical-align: middle;">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" style="height: 120px;">
                </td>
                <td style="width: 80%; vertical-align: middle; text-align: right;">
                    <h1 style="margin: 0; color: var(--dark-color); font-size: 1.4rem;">Prontuário Médico</h1>
                    <h3 style="margin: 3px 0; color: var(--secondary-color); font-size: 1rem;">Paciente:
                        {{ $prontuario->paciente->nome }}</h3>
                    <span style="margin-left: 10px; color: var(--secondary-color);">
                        Data/hora do atendimento:
                        {{ \Carbon\Carbon::parse($prontuario->data_hora_atendimento)->format('d/m/Y H:i') }}
                    </span>
                </td>
            </tr>
        </table>

        <div class="section">
            <h2>Informações do Paciente</h2>
            <table>
                <tr>
                    <td style="width: 33%;">
                        <div class="field-pair">
                            <span class="label">Nome:</span>
                            <span class="value">{{ $prontuario->paciente->nome ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="field-pair">
                            <span class="label">CPF:</span>
                            <span class="value">{{ $prontuario->paciente->cpf ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 33%;">
                        <div class="field-pair">
                            <span class="label">Data de Nascimento:</span>
                            <span class="value">{{ $prontuario->paciente->data_nascimento ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-pair">
                            <span class="label">Médico Responsável:</span>
                            <span class="value">{{ $prontuario->medico->nome ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td colspan="2">
                        <div class="field-pair">
                            <span class="label">CRM:</span>
                            <span class="value">{{ $prontuario->medico->crm ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Avaliação Médica</h2>
            <table>
                <tr>
                    <td colspan="3">
                        <div class="field-pair">
                            <span class="label">Queixa Principal:</span>
                            <span class="value">{{ $prontuario->qp ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div class="field-pair">
                            <span class="label">História da Doença Atual:</span>
                            <span class="value">{{ $prontuario->hdp ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <div class="field-pair">
                            <?php
                            $doencasString = $prontuario->data_inicio_sintomas ?? '';
                            $doencasArray = array_map('trim', preg_split('/\r?\n/', $doencasString, -1, PREG_SPLIT_NO_EMPTY));
                            ?>
                            <span class="label">Doenças Preexistentes:</span>
                            <span class="value">
                                <ul>
                                    <?php foreach ($doencasArray as $doenca): ?>
                                    <li><?php echo htmlspecialchars($doenca); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </span>
                        </div>
                    </td>
                    <td style="width: 50%">
                        <div class="field-pair">
                            <span class="label">Cirurgias/Hospitalizações:</span>
                            <span class="value">{{ $prontuario->cirurgias_hospitalizacoes ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <div class="field-pair">
                            <?php
                                $medicamentosString = $prontuario->medicamento_uso_detalhes ?? '';
                                $medicamentosArray = array_map('trim', preg_split('/\r?\n/', $medicamentosString, -1, PREG_SPLIT_NO_EMPTY));
                            ?>
                            <span class="label">Medicamentos em Uso:</span>
                            <span class="value">
                                <ul>
                                    <?php foreach ($medicamentosArray as $medicamento): ?>
                                    <li><?php echo htmlspecialchars($medicamento); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="field-pair">
                            <span class="label">Alergias:</span>
                            <span class="value">{{ $prontuario->outros_alergias ?? 'Não informado' }}</span>
                        </div>
                    </td>

                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Estilo de Vida</h2>
            <table>
                <tr>
                    <td style="width: 20%;">
                        <div class="field-pair">
                            <span class="label">Tabagismo:</span>
                            <span class="value">
                                {{ $prontuario->tabagismo === 1 ? 'Sim' : ($prontuario->tabagismo === 0 ? 'Não' : 'Não informado') }}
                            </span>

                        </div>
                    </td>
                    <td style="width: 20%;">
                        <div class="field-pair">
                            <span class="label">Alcoolismo:</span>
                            <span
                                class="value">{{ $prontuario->alcoolismo === 1 ? 'Sim' : ($prontuario->alcoolismo === 0 ? 'Não' : 'Não informado') }}</span>
                        </div>
                    </td>
                    <td style="width: 20%;">
                        <div class="field-pair">
                            <span class="label">Drogas:</span>
                            <span
                                class="value">{{ $prontuario->drogas === 1 ? 'Sim' : ($prontuario->drogas === 0 ? 'Não' : 'Não informado') }}</span>
                        </div>
                    </td>
                
                    <td style="width: 20%;">
                        <div class="field-pair">
                            <span class="label">Atividade Física:</span>
                            <span
                                class="value">{{ $prontuario->atividade_fisica === 1 ? 'Sim' : ($prontuario->atividade_fisica === 0 ? 'Não' : 'Não informado') }}</span>
                        </div>
                    </td>
                    <td style="width: 20%;">
                        <div class="field-pair">
                            <span class="label">Dieta:</span>
                            <span
                                class="value">{{ $prontuario->dieta === 1 ? 'Sim' : ($prontuario->dieta === 0 ? 'Não' : 'Não informado') }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="5">
                        <div class="field-pair">
                            <span class="label">Observações:</span>
                            <span class="value">{{ $prontuario->obs_estilo_vida ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>

                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Dados Vitais</h2>
            <table>
                <tr>
                    @if ($prontuario->paciente->genero == '2')
                    <td colspan="400" style="width: 100%;">                   
                        <div class="field-pair">
                            <span class="label">DUM:</span>
                            <span class="value">{{ Carbon\Carbon::parse($prontuario->dum)->format('d/m/Y') ?? 'Não informado' }}</span>
                        </div>                   
                    </td>
                     @endif
                </tr>
                <tr> 
                    <td style="width: 25%;">
                        <div class="field-pair">
                            <span class="label">PA:</span>
                            <span class="value">{{ $prontuario->pa ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="field-pair">
                            <span class="label">Peso:</span>
                            <span class="value">{{ $prontuario->peso ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="field-pair">
                            <span class="label">Altura:</span>
                            <span class="value">{{ $prontuario->altura ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="field-pair">
                            <span class="label">IMC:</span>
                            <span class="value">{{ $prontuario->imc ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field-pair">
                            <span class="label">FC:</span>
                            <span class="value">{{ $prontuario->fc ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="field-pair">
                            <span class="label">FR:</span>
                            <span class="value">{{ $prontuario->fr ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="field-pair">
                            <span class="label">Temperatura:</span>
                            <span class="value">{{ $prontuario->temperatura ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="field-pair">
                            <span class="label">Saturação:</span>
                            <span class="value">{{ $prontuario->saturacao ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Exame Físico</h2>
            <table>
                <tr>
                    <td>
                        <div class="field-pair">
                            <span class="label">Observações:</span>
                            <span class="value">{{ $prontuario->obs_exame_fisico ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Hipótese Diagnóstica</h2>
            <table>
                <tr>
                    <td style="width: 100%;">
                        <div class="field-pair">
                            <span class="label">Diagnósticos:</span>
                            <span
                                class="value">{{ !empty($nomeDiagnosticos) ? implode(', ', $nomeDiagnosticos) : 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 100%;">
                        <div class="field-pair">
                            <span class="label">Detalhes:</span>
                            <span
                                class="value">{{ $prontuario->hipotese_diagnostica_detalhes ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>

                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Conduta e Evolução</h2>
            <table>
                <tr>
                    <td colspan="2">
                        <div class="field-pair">
                            <span class="label">Conduta:</span>
                            <span class="value">{{ $prontuario->conduta ?? 'Não informado' }}</span>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td style="width: 70%">
                        <div class="field-pair">
                            <span class="label">Evolução:</span>
                            <span class="value">{{ $prontuario->evolucao ?? 'Não informado' }}</span>
                        </div>
                    </td>
                    <td style="width: 30%">
                        <div class="field-pair">
                            <span class="label">Data da Evolução:</span>
                            <span class="value">{{ \Carbon\Carbon::parse($prontuario->data_hora_retorno)->format('d/m/Y H:i') }}</span>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Medicamentos Prescritos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Medicamento</th>
                        <th>Quantidade</th>
                        <th>Forma de Uso</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($receituario && $receituario->count() > 0)
                        @foreach ($receituario as $item)
                            <tr>
                                <td>{{ $item->medicamento->nome ?? 'Não informado' }}</td>
                                <td>{{ $item->qtd ?? 'Não informado' }}</td>
                                <td>{{ $item->forma_uso ?? 'Não informado' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="3">Nenhum medicamento prescrito.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="section">
            <h2>Exames Solicitados e Resultados</h2>
            <table>
                <tr>
                    <td style="width: 50%">
                        <div class="field-pair">
                            <?php
                                $examesString = $prontuario->resultado_exames ?? '';
                                $examesArray = array_map('trim', preg_split('/\r?\n/', $examesString, -1, PREG_SPLIT_NO_EMPTY));
                            ?>
                            <span class="label">Exames</span>
                            <span class="value">
                                <ul>
                                    <?php foreach ($examesArray as $exame): ?>
                                    <li><?php echo htmlspecialchars($exame); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </span>
                        </div>
                    </td>
                    <td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>Encaminhamentos</h2>
            <table>
                <thead>
                    <tr>
                        <th>Especialidade</th>
                        <th>Descrição</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($listaEncaminhamentos && $listaEncaminhamentos->count() > 0)
                        @foreach ($listaEncaminhamentos as $encaminhamento)
                            <tr>
                                <td>{{ $encaminhamento->especialidade->nome ?? 'Não informado' }}</td>
                                <td>{{ $encaminhamento->descricao ?? 'Não informado' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2">Nenhum encaminhamento registrado.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="signature-area">
            <div style="text-align: center; font-size: 0.9rem;">
                <span>{{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('DD [de] MMMM [de] YYYY') }}</span>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <span>Dr(a). {{ $prontuario->medico->nome }}</span><br>
                    <span>CRM: {{ $prontuario->medico->crm }}</span>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
