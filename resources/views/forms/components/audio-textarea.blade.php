<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            isRecording: false,
            isProcessing: false,
            hasSupport: false,
            errorMsg: '',
            mediaRecorder: null,
            audioChunks: [],
            recordingSeconds: 0,
            timerInterval: null,

            init() {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.errorMsg = 'Este navegador não suporta gravação de áudio. Use Chrome ou Edge.';
                    return;
                }
                this.hasSupport = true;

                /**
                 * Expõe uma função global que o CreateAtendimentoClinicoNew.php
                 * pode chamar via Livewire dispatch antes de salvar o formulário.
                 *
                 * Retorna uma Promise que resolve quando:
                 *   - não há gravação/processamento em curso  → resolve imediatamente
                 *   - está gravando                           → para, processa e resolve
                 *   - está processando                        → aguarda o fim e resolve
                 */
                /**
                 * Etapa 1 — apenas informa o PHP se há gravação/processamento em curso.
                 * O PHP decide se exibe o modal de confirmação ou salva direto.
                 */
                Livewire.on('check-audio-state', ({ another }) => {
                    const needsWait = this.isRecording || this.isProcessing;
                    Livewire.dispatch('audio-check-result', { another: another ?? false, needsWait });
                });

                /**
                 * Etapa 2 — chamado após o usuário confirmar o modal.
                 * Para a gravação, aguarda o processamento e avisa o PHP para salvar.
                 */
                Livewire.on('check-audio-before-save', async ({ another }) => {
                    await window.audioTextareaStopAndProcess();
                    Livewire.dispatch('audio-ready', { another: another ?? false });
                });

                window.audioTextareaStopAndProcess = () => {
                    return new Promise((resolve) => {

                        // Nada em curso — pode salvar já
                        if (!this.isRecording && !this.isProcessing) {
                            resolve();
                            return;
                        }

                        // Aguarda o fim do processamento se só estiver processando
                        if (!this.isRecording && this.isProcessing) {
                            const wait = setInterval(() => {
                                if (!this.isProcessing) {
                                    clearInterval(wait);
                                    resolve();
                                }
                            }, 300);
                            return;
                        }

                        // Está gravando: para e aguarda processamento concluir
                        if (this.isRecording) {
                            // Após onstop + processAudio terminar, isProcessing volta a false
                            const waitProcessing = setInterval(() => {
                                if (!this.isRecording && !this.isProcessing) {
                                    clearInterval(waitProcessing);
                                    resolve();
                                }
                            }, 300);

                            // Para a gravação (dispara onstop → processAudio)
                            this.mediaRecorder.stop();
                            this.isRecording = false;
                            clearInterval(this.timerInterval);
                            this.recordingSeconds = 0;
                        }
                    });
                };
            },

            formatTime(seconds) {
                const m = String(Math.floor(seconds / 60)).padStart(2, '0');
                const s = String(seconds % 60).padStart(2, '0');
                return m + ':' + s;
            },

            async toggleRecording() {
                this.errorMsg = '';

                if (this.isRecording) {
                    this.mediaRecorder.stop();
                    this.isRecording = false;
                    clearInterval(this.timerInterval);
                    this.recordingSeconds = 0;
                } else {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                        this.audioChunks = [];

                        const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                            ? 'audio/webm;codecs=opus'
                            : 'audio/webm';

                        this.mediaRecorder = new MediaRecorder(stream, { mimeType });

                        this.mediaRecorder.ondataavailable = (e) => {
                            if (e.data.size > 0) this.audioChunks.push(e.data);
                        };

                        this.mediaRecorder.onstop = async () => {
                            stream.getTracks().forEach(t => t.stop());
                            const audioBlob = new Blob(this.audioChunks, { type: mimeType });
                            await this.processAudio(audioBlob);
                        };

                        this.mediaRecorder.start(250);
                        this.isRecording = true;
                        this.recordingSeconds = 0;
                        this.timerInterval = setInterval(() => { this.recordingSeconds++; }, 1000);

                    } catch (e) {
                        if (e.name === 'NotAllowedError') {
                            this.errorMsg = 'Permissão de microfone negada. Verifique as configurações do navegador.';
                        } else {
                            this.errorMsg = 'Erro ao acessar microfone: ' + e.message;
                        }
                    }
                }
            },

            async processAudio(audioBlob) {
                this.isProcessing = true;
                this.setTextarea('⏳ Transcrevendo áudio...');

                try {
                    // ── ETAPA 1: Transcrição via backend (Groq Whisper) ──
                    const formData = new FormData();
                    formData.append('audio', audioBlob, 'audio.webm');

                    const transcribeRes = await fetch('{{ route('groq.transcribe') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        },
                        body: formData,
                    });

                    const transcribeData = await transcribeRes.json();

                    if (!transcribeRes.ok) {
                        this.errorMsg = transcribeData.error || 'Erro na transcrição.';
                        this.setTextarea('');
                        return;
                    }

                    const rawText = transcribeData.text || '';

                    // ── ETAPA 2: Diarização via backend (Llama 3.3 70B) ──
                    this.setTextarea('🤖 Identificando falantes e adicionando pontuação...');

                    const diarizeRes = await fetch('{{ route('groq.diarize') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ text: rawText }),
                    });

                    const diarizeData = await diarizeRes.json();

                    if (!diarizeRes.ok) {
                        this.setTextarea(rawText);
                        this.errorMsg = diarizeData.error || 'Diarização indisponível. Texto bruto mantido.';
                        return;
                    }

                    this.setTextarea(diarizeData.text || rawText);

                } catch (e) {
                    this.errorMsg = 'Erro de conexão: ' + e.message;
                    this.setTextarea('');
                } finally {
                    this.isProcessing = false;
                }
            },

            setTextarea(value) {
                const el = document.getElementById('{{ $getId() }}');
                if (el) {
                    el.value = value;
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }
        }"
    >
        {{-- ── Textarea principal ── --}}
        <textarea
            id="{{ $getId() }}"
            wire:model="{{ $getStatePath() }}"
            {!! $isDisabled() ? 'disabled' : '' !!}
            rows="8"
            placeholder="Clique em 'Gravar Áudio', fale normalmente e ao parar a IA irá identificar Médico e Paciente automaticamente..."
            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm font-mono leading-relaxed"
        ></textarea>

        {{-- ── Mensagem de erro ── --}}
        <p
            x-show="errorMsg"
            x-text="errorMsg"
            class="text-sm text-red-600 dark:text-red-400 mt-2 p-2 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800"
        ></p>

        {{-- ── Aviso: gravação ou processamento em curso ── --}}
        <div
            x-show="isRecording || isProcessing"
            class="mt-3 flex items-center gap-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-300 dark:bg-amber-900/20 dark:border-amber-600"
        >
            <span class="text-amber-600 dark:text-amber-400 text-sm font-medium">
                ⚠️
                <span x-text="isRecording ? 'Gravação em andamento. Ao salvar, a gravação será interrompida e transcrita automaticamente.' : 'Aguarde — transcrevendo o áudio antes de salvar...'"></span>
            </span>
        </div>

        {{-- ── Barra de controles ── --}}
        <div class="flex items-center gap-3 mt-3 flex-wrap">

            {{-- Botão principal --}}
            <button
                type="button"
                x-show="hasSupport"
                x-bind:disabled="isProcessing"
                @click="toggleRecording()"
                :style="isRecording
                    ? 'background-color:#dc2626;color:#fff;'
                    : isProcessing
                        ? 'background-color:#6b7280;color:#fff;opacity:0.6;cursor:not-allowed;'
                        : 'background-color:#16a34a;color:#fff;'"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg shadow-sm focus:outline-none transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
                <span x-show="isRecording" class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-white"></span>
                </span>
                <span x-show="!isRecording && !isProcessing">🎙️</span>
                <span x-show="isProcessing" class="animate-spin">⚙️</span>

                <span x-text="
                    isProcessing ? 'Processando...' :
                    isRecording   ? 'Parar (' + formatTime(recordingSeconds) + ')' :
                                    'Gravar Áudio'
                "></span>
            </button>

            <span x-show="isRecording" class="text-xs text-red-500 dark:text-red-400 animate-pulse font-medium">
                🎤 Gravando... fale normalmente.
            </span>
            <span x-show="isProcessing" class="text-xs text-blue-600 dark:text-blue-400 animate-pulse font-medium">
                🤖 Identificando falantes...
            </span>
        </div>
        
    </div>
</x-dynamic-component>