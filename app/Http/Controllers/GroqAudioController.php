<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GroqAudioController extends Controller
{
    /**
     * Monta a URL base removendo qualquer sufixo /openai/v1 que o .env já traga,
     * garantindo que o controller nunca duplique o prefixo.
     *
     * Exemplos aceitos no .env:
     *   GROQ_API_BASE_URI=https://api.groq.com
     *   GROQ_API_BASE_URI=https://api.groq.com/openai/v1
     */
    private function baseUri(): string
    {
        $uri = rtrim(config('services.groq.base_uri', 'https://api.groq.com'), '/');

        // Remove sufixo /openai/v1 caso já esteja no valor do .env
        $uri = preg_replace('#/openai/v1$#', '', $uri);

        return $uri;
    }

    /**
     * Etapa 1 — Transcrição via Groq Whisper.
     */
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => ['required', 'file', 'mimes:webm,ogg,mp4,mp3,wav', 'max:25600'],
        ]);

        $apiKey = config('services.groq.key');
        $url    = $this->baseUri() . '/openai/v1/audio/transcriptions';

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->attach(
                'file',
                file_get_contents($request->file('audio')->getRealPath()),
                'audio.webm'
            )
            ->post($url, [
                'model'                     => 'whisper-large-v3-turbo',
                'language'                  => 'pt',
                'response_format'           => 'verbose_json',
                'timestamp_granularities[]' => 'segment',
            ]);

        if ($response->failed()) {
            Log::error('Groq Whisper error', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json([
                'error' => 'Erro na transcrição: ' . ($response->json('error.message') ?? $response->status()),
            ], $response->status());
        }

        $text = $response->json('text', '');

        if (! trim($text)) {
            return response()->json(['error' => 'Nenhuma fala detectada no áudio. Tente novamente.'], 422);
        }

        return response()->json(['text' => $text]);
    }

    /**
     * Etapa 2 — Diarização + pontuação via Llama 3.3 70B (Groq).
     */
    public function diarize(Request $request)
    {
        $request->validate([
            'text' => ['required', 'string', 'max:20000'],
        ]);

        $apiKey = config('services.groq.key');
        $url    = $this->baseUri() . '/openai/v1/chat/completions';

        $systemPrompt = <<<'PROMPT'
Você é um assistente especializado em transcrição de consultas médicas em português brasileiro.

Receberá um texto bruto, sem pontuação, de uma consulta médica com dois participantes.

Sua tarefa:
1. Adicionar pontuação correta (vírgulas, pontos, interrogações, exclamações, reticências).
2. Identificar e separar os falantes usando os rótulos exatos:
   **Médico:** — perguntas clínicas, orientações, diagnósticos, prescrições.
   **Paciente:** — queixas, sintomas, histórico, respostas.
   Se não for possível distinguir com certeza, use **Falante 1:** e **Falante 2:**.
3. Cada troca de falante em nova linha, com linha em branco entre cada um.
4. Preservar o conteúdo original — não resumir, não corrigir termos médicos, não inventar informações.
5. Retornar APENAS o texto formatado, sem introduções, explicações ou comentários.

Formato de saída esperado:
Médico: Bom dia! Qual é a sua queixa principal hoje?

Paciente: Estou com dor de cabeça há três dias, principalmente de manhã.

Médico: A dor é latejante ou uma pressão constante?
PROMPT;

        $response = Http::withToken($apiKey)
            ->timeout(60)
            ->post($url, [
                'model'       => 'llama-3.3-70b-versatile',
                'temperature' => 0.2,
                'max_tokens'  => 2048,
                'messages'    => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user',   'content' => $request->input('text')],
                ],
            ]);

        if ($response->failed()) {
            Log::error('Groq Llama error', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json([
                'error' => 'Erro na diarização: ' . ($response->json('error.message') ?? $response->status()),
            ], $response->status());
        }

        $formatted = $response->json('choices.0.message.content', '');

        return response()->json(['text' => trim($formatted) ?: $request->input('text')]);
    }
}