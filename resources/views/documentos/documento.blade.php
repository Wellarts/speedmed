<!DOCTYPE html>
<html>

<head>
    <title>{{ $documento->titulo }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            color: #666666;
            margin: 0;
            padding: 0;
        }

        .container {
            position: relative;
            text-align: center;
            margin: 0 auto;
            width: 100%;
            height: 100vh;
            /* Make container full height */
        }

        .background-image {
            width: 120%;
            height: 110%;
            display: block;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: -1;
            /* Send to back */
          /*  opacity: 0.5; */
        }

        .header-title {
            position: absolute;
            top: 25%;
            /* Adjusted position */
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;

        }

        .patient-info {
            position: absolute;
            top: 30%;
            left: 10%;
            font-size: 16px;
            text-align: left;
        }

        .content-area {
            position: absolute;
            top: 45%;
            /* Adjusted position */
            left: 10%;
            right: 10%;
            transform: translateY(-50%);
            font-size: 16px;
            text-align: left;
            line-height: 1.6;
        }

        .date-footer {
            position: absolute;
            bottom: 15%;
            /* Adjusted position */
            width: 100%;
            text-align: center;
            font-size: 16px;

        }

        .signature-footer {
            position: absolute;
            bottom: 5%;
            /* Adjusted position */
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #666666;
            padding-top: 5px;
            width: 250px;
            margin: 0 auto;
        }
    </style>
</head>

<body>
    <div class="container">
        {{-- Nota: Usando o fundo do receituário. Você pode querer um mais genérico. --}}
        <img src="{{ public_path('img/receituario_comum.jpg') }}" class="background-image" alt="Fundo do Documento" />

        <div class="header-title">
            <h4>
                {{ match ($documento->tipo) {
                    '1' => 'Declaração de Comparecimento',
                    '2' => 'Atestado',
                    '3' => 'Receituário',
                    '4' => 'Encaminhamento',
                    '5' => 'Exames',
                    '6' => 'Orientações',
                    '7' => 'Outros',
                    '8' => 'Laudo',
                    default => 'Documento',
                } }}
            </h4>
        </div>

        <div class="patient-info">
            <p><b>Paciente:</b> {{ $documento->paciente->nome }}</p>
            <p><b>CPF:</b> {{ $documento->paciente->cpf }}</p>
        </div>

        <div class="content-area" style="text-align: justify; text-justify: inter-word;">
            <p>{!! nl2br(e($documento->descricao)) !!}</p>
        </div>

        <div class="date-footer" style="bottom: 25%;">
            {{ \Carbon\Carbon::now()->locale('pt_BR')->isoFormat('D [de] MMMM [de] YYYY') }}
        </div>

        <div class="signature-footer" style="bottom: 15%;">
            <div class="signature-line">
                Dr(a). {{ $documento->medico->nome }}<br>
                CRM: {{ $documento->medico->crm }}
            </div>
        </div>
    </div>
</body>

</html>
