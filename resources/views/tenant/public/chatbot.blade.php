@extends('layouts.public')

@section('content')

{{-- Full-screen chat card --}}
<div class="flex flex-col bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden"
     style="height: calc(100vh - 200px); min-height: 480px;">

    {{-- ── Chat header ── --}}
    <div class="flex items-center gap-3 px-4 py-3 border-b border-slate-100 bg-white flex-shrink-0">
        {{-- Bot avatar --}}
        <div class="relative flex-shrink-0">
            <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center shadow-sm">
                <svg width="26" height="26" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- Graduation cap top -->
                    <polygon points="20,6 36,14 20,22 4,14" fill="white" opacity="0.95"/>
                    <!-- Cap brim shadow -->
                    <polygon points="20,22 36,14 36,16 20,24 4,16 4,14" fill="white" opacity="0.5"/>
                    <!-- Tassel string -->
                    <line x1="36" y1="14" x2="36" y2="22" stroke="white" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="36" cy="23" r="1.5" fill="white" opacity="0.9"/>
                    <!-- Robot face circle -->
                    <circle cx="20" cy="29" r="8" fill="white" opacity="0.15"/>
                    <!-- Eyes -->
                    <circle cx="17" cy="28" r="1.8" fill="white"/>
                    <circle cx="23" cy="28" r="1.8" fill="white"/>
                    <!-- Smile -->
                    <path d="M16.5 31.5 Q20 34 23.5 31.5" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </svg>
            </div>
            {{-- Online dot --}}
            <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
        </div>

        <div class="flex-1 min-w-0">
            <div class="font-semibold text-slate-800 text-sm leading-tight">School Assistant</div>
            <div class="text-xs text-green-500 font-medium">Online · Ask me anything</div>
        </div>

        {{-- Powered by badge --}}
        <div class="hidden sm:flex items-center gap-1 text-xs text-slate-400 bg-slate-50 border border-slate-100 rounded-full px-2.5 py-1">
            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            AI-powered
        </div>
    </div>

    {{-- ── Messages area ── --}}
    <div id="chat-window"
         class="flex-1 overflow-y-auto px-4 py-5 space-y-4 scroll-smooth"
         style="overscroll-behavior: contain;">

        {{-- Welcome bubble --}}
        <div class="flex gap-2.5 items-end">
            <div class="shrink-0 w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center shadow-sm">
                <svg width="16" height="16" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="20,6 36,14 20,22 4,14" fill="white" opacity="0.95"/>
                    <circle cx="17" cy="28" r="2" fill="white"/>
                    <circle cx="23" cy="28" r="2" fill="white"/>
                    <path d="M16.5 31.5 Q20 34 23.5 31.5" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="bg-slate-100 rounded-2xl rounded-bl-sm px-4 py-3 max-w-sm shadow-sm">
                <p class="text-sm text-slate-700 leading-relaxed">{{ __('public.chatbot_intro') }}</p>
                <span class="text-xs text-slate-400 mt-1 block">{{ now()->format('g:i A') }}</span>
            </div>
        </div>

{{-- Suggested questions --}}
        <div class="flex gap-2 flex-wrap ps-9">
            <button type="button" onclick="askSuggestion(this)"
                    class="suggestion-btn text-xs bg-white border border-slate-200 text-slate-600 rounded-full px-3 py-1.5 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-sm">
                📅 School timings?
            </button>
            <button type="button" onclick="askSuggestion(this)"
                    class="suggestion-btn text-xs bg-white border border-slate-200 text-slate-600 rounded-full px-3 py-1.5 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-sm">
                💰 Fee structure?
            </button>
            <button type="button" onclick="askSuggestion(this)"
                    class="suggestion-btn text-xs bg-white border border-slate-200 text-slate-600 rounded-full px-3 py-1.5 hover:border-blue-400 hover:text-blue-600 transition-colors shadow-sm">
                📋 Admission process?
            </button>
        </div>
    </div>

    {{-- ── Downloads panel ── --}}
    @if(isset($downloads) && $downloads->isNotEmpty())
    <div class="flex-shrink-0 border-t border-slate-100" x-data="{ open: false }">
        {{-- Toggle bar --}}
        <button type="button"
                @click="open = !open"
                class="w-full flex items-center justify-between px-4 py-2.5 bg-slate-50 hover:bg-slate-100 transition-colors text-left">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span class="text-xs font-semibold text-slate-700">Available Downloads</span>
                <span class="text-xs bg-blue-100 text-blue-600 font-semibold rounded-full px-1.5 py-0.5">{{ $downloads->count() }}</span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        {{-- File list --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="px-3 pb-3 pt-1 grid grid-cols-1 gap-1.5 max-h-48 overflow-y-auto bg-slate-50">
            @foreach($downloads as $doc)
            <a href="{{ $doc->signed_url }}" target="_blank" rel="noopener"
               class="flex items-center gap-2.5 bg-white border border-slate-200 rounded-xl px-3 py-2 hover:border-blue-400 hover:shadow-sm transition-all group">
                <div class="shrink-0 w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 truncate">{{ $doc->title }}</div>
                    <div class="text-xs text-slate-400">
                        {{ $doc->ext }}
                        @if($doc->size)
                            @php
                                $sz = $doc->size >= 1048576
                                    ? round($doc->size/1048576, 1).' MB'
                                    : ($doc->size >= 1024 ? round($doc->size/1024).' KB' : $doc->size.' B');
                            @endphp
                            · {{ $sz }}
                        @endif
                    </div>
                </div>
                <svg class="w-3 h-3 text-slate-400 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Input bar ── --}}
    <div class="flex-shrink-0 border-t border-slate-100 bg-white px-4 py-3">
        <form id="ask-form" class="flex items-end gap-2">
            @csrf
            <div class="flex-1 relative">
                <textarea
                    id="question"
                    name="question"
                    rows="1"
                    maxlength="1000"
                    placeholder="{{ __('public.question_placeholder') }}"
                    autocomplete="off"
                    class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:bg-white transition-colors leading-relaxed"
                    style="max-height: 120px; overflow-y: auto;"
                ></textarea>
            </div>
            <button
                type="submit"
                id="ask-btn"
                class="shrink-0 w-10 h-10 rounded-xl bg-blue-600 hover:bg-blue-700 flex items-center justify-center transition-colors shadow-sm disabled:opacity-40 disabled:cursor-not-allowed"
            >
                <svg class="w-4 h-4 text-white rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19V5m0 0l-7 7m7-7l7 7"/>
                </svg>
            </button>
        </form>
        <p class="text-xs text-slate-400 text-center mt-2">AI can make mistakes. Verify important information with the school.</p>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form      = document.getElementById('ask-form');
    const input     = document.getElementById('question');
    const btn       = document.getElementById('ask-btn');
    const chatWin   = document.getElementById('chat-window');

    const thinkingText = @json(__('public.thinking'));
    const errorGeneric = @json(__('public.error_generic'));
    const errorConn    = @json(__('public.error_connection'));

    const botAvatarHtml = `
        <div class="shrink-0 w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center shadow-sm self-end">
            <svg width="16" height="16" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                <polygon points="20,6 36,14 20,22 4,14" fill="white" opacity="0.95"/>
                <circle cx="17" cy="28" r="2" fill="white"/>
                <circle cx="23" cy="28" r="2" fill="white"/>
                <path d="M16.5 31.5 Q20 34 23.5 31.5" stroke="white" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            </svg>
        </div>`;

    function now() {
        return new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    }

    function scrollToBottom() {
        chatWin.scrollTo({ top: chatWin.scrollHeight, behavior: 'smooth' });
    }

    function addUserBubble(text) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex justify-end items-end gap-2';
        wrapper.innerHTML = `
            <div class="bg-blue-600 text-white rounded-2xl rounded-br-sm px-4 py-3 max-w-sm shadow-sm">
                <p class="text-sm leading-relaxed">${escapeHtml(text)}</p>
                <span class="text-xs text-blue-200 mt-1 block text-right">${now()}</span>
            </div>`;
        chatWin.appendChild(wrapper);
        scrollToBottom();
    }

    function addBotBubble(html) {
        const wrapper = document.createElement('div');
        wrapper.className = 'flex gap-2.5 items-end';
        wrapper.innerHTML = botAvatarHtml + `
            <div class="bg-slate-100 rounded-2xl rounded-bl-sm px-4 py-3 max-w-sm shadow-sm">
                <p class="text-sm text-slate-700 leading-relaxed">${html}</p>
                <span class="text-xs text-slate-400 mt-1 block">${now()}</span>
            </div>`;
        chatWin.appendChild(wrapper);
        scrollToBottom();
    }

    function addTyping() {
        const el = document.createElement('div');
        el.id = 'typing-indicator';
        el.className = 'flex gap-2.5 items-end';
        el.innerHTML = botAvatarHtml + `
            <div class="bg-slate-100 rounded-2xl rounded-bl-sm px-4 py-3 shadow-sm flex items-center gap-1.5" style="min-width:60px">
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full inline-block"></span>
                <span class="typing-dot w-2 h-2 bg-slate-400 rounded-full inline-block"></span>
            </div>`;
        chatWin.appendChild(el);
        scrollToBottom();
    }

    function removeTyping() {
        document.getElementById('typing-indicator')?.remove();
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\n/g, '<br>');
    }

    function formatBytes(bytes) {
        if (!bytes) return '';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024)    return (bytes / 1024).toFixed(0) + ' KB';
        return bytes + ' B';
    }

    function renderAttachments(attachments) {
        if (!attachments || !attachments.length) return '';
        const cards = attachments.map(a => `
            <a href="${a.url}" target="_blank" rel="noopener"
               class="flex items-center gap-2.5 bg-white border border-slate-200 rounded-xl px-3 py-2.5 hover:border-blue-400 hover:shadow-sm transition-all group mt-2 no-underline">
                <div class="shrink-0 w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h4a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-slate-700 group-hover:text-blue-600 truncate">${escapeHtml(a.title)}</div>
                    <div class="text-xs text-slate-400">${a.ext || 'File'}${a.size ? ' · ' + formatBytes(a.size) : ''}</div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
            </a>`).join('');
        return `<div class="mt-2 border-t border-slate-200 pt-2">${cards}</div>`;
    }

    // Auto-resize textarea
    input.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    // Submit on Enter (Shift+Enter = newline)
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    });

    async function sendQuestion(question) {
        addUserBubble(question);
        addTyping();

        // Hide suggestion chips after first message
        document.querySelectorAll('.suggestion-btn').forEach(b => b.closest('div')?.remove());

        btn.disabled = true;
        input.disabled = true;

        try {
            const res = await fetch('{{ route("tenant.public.chatbot.ask") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                                    || document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ question }),
            });

            removeTyping();
            const data = await res.json();

            if (data.success) {
                const attachHtml = renderAttachments(data.attachments);
                addBotBubble(escapeHtml(data.answer) + attachHtml);
            } else {
                addBotBubble(escapeHtml(data.message || errorGeneric));
            }
        } catch (err) {
            removeTyping();
            addBotBubble(escapeHtml(errorConn));
        } finally {
            btn.disabled = false;
            input.disabled = false;
            input.style.height = 'auto';
            input.focus();
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const question = input.value.trim();
        if (!question) return;
        input.value = '';
        sendQuestion(question);
    });

    // Suggestion chips
    window.askSuggestion = function (btn) {
        const text = btn.innerText.replace(/^[^\w]*/, '').trim(); // strip emoji prefix
        input.value = text;
        form.dispatchEvent(new Event('submit'));
    };
})();
</script>

<style>
/* Typing dots bounce animation */
.typing-dot {
    animation: typingBounce 1.2s infinite ease-in-out;
}
.typing-dot:nth-child(2) { animation-delay: 0.2s; }
.typing-dot:nth-child(3) { animation-delay: 0.4s; }

@keyframes typingBounce {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
    30%            { transform: translateY(-5px); opacity: 1; }
}

/* Thin scrollbar for chat window */
#chat-window::-webkit-scrollbar { width: 4px; }
#chat-window::-webkit-scrollbar-track { background: transparent; }
#chat-window::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 99px; }
#chat-window::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
@endpush

@endsection
