<x-quiz-layout>
    <x-slot name="pageTitle">Test - {{ $module->title }}</x-slot>

    {{-- En-tête minimal --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
        <span style="font-weight:700;font-size:1.1rem;">{{ $module->title }}</span>
        <a href="{{ route('modules.show', $module) }}" class="btn btn-sm" style="color:var(--text-muted);border:1px solid var(--card-border);">
            <i class="bi bi-x-lg me-1"></i>Quitter
        </a>
    </div>

    {{-- Barre de progression --}}
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:.85rem;">
            <span style="color:var(--text-muted);">{{ $question['current'] }} / {{ $question['total'] }}</span>
            <span id="timer-display" style="font-weight:600;color:var(--accent);">0:00</span>
        </div>
        <div class="progress" style="height:8px;">
            <div class="progress-bar" style="width:{{ ($question['current'] / $question['total']) * 100 }}%;background:var(--accent);"></div>
        </div>
    </div>

    {{-- Question --}}
    <div class="card mb-4">
        <div class="card-body p-4">
            @if ($question['field_question'] === 'photo_path')
                <div style="text-align:center;">
                    <div style="display:inline-block;position:relative;max-width:300px;width:100%;">
                        <img src="{{ $question['question_content'] }}" alt="Question" style="width:100%;aspect-ratio:1/1;border-radius:12px;object-fit:cover;display:block;">
                        <button type="button" onclick="setZoomImage('{{ $question['question_content'] }}')" style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;"><i class="bi bi-zoom-in"></i></button>
                    </div>
                </div>
            @elseif ($question['field_question'] === 'audio_path')
                <div style="text-align:center;">
                    <audio controls autoplay src="{{ $question['question_content'] }}" style="width:100%;max-width:400px;border-radius:8px;"></audio>
                </div>
            @else
                <div class="card" style="background:var(--body-bg);border:1px solid var(--card-border);">
                    <div class="card-body p-3">
                        <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Options --}}
    <form method="POST" action="{{ route('test.submit', $module) }}">
        @csrf
        <div class="mb-4" style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;">
            @foreach ($question['options'] as $index => $option)
                <label for="option_{{ $index }}" class="test-option"
                       style="cursor:pointer;background:var(--card-bg);border:2px solid var(--card-border);border-radius:12px;padding:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;min-height:80px;transition:.2s;position:relative;">
                    <input type="radio" id="option_{{ $index }}" name="answer" value="{{ $index }}" class="form-check-input" style="display:none;" required>
                    @if ($question['field_answer'] === 'photo_path')
                        <img src="{{ $option }}" alt="Option {{ $index + 1 }}" style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                        <span onclick="event.preventDefault();event.stopPropagation();setZoomImage('{{ $option }}')" style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.7);border-radius:6px;padding:4px 8px;cursor:pointer;color:#fff;font-size:.8rem;z-index:10;line-height:1;"><i class="bi bi-zoom-in"></i></span>
                    @elseif ($question['field_answer'] === 'audio_path')
                        <i class="bi bi-music-note-beamed" style="font-size:1.5rem;"></i>
                        <audio src="{{ $option }}" style="width:90%;border-radius:6px;" controls onclick="event.stopPropagation();"></audio>
                    @else
                        <span style="font-size:.9rem;text-align:center;color:var(--text-primary);word-break:break-word;">{{ $option }}</span>
                    @endif
                </label>
            @endforeach
        </div>
        <input type="hidden" name="elapsed_seconds" id="elapsed_seconds" value="0">
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-check-lg me-1"></i>
            {{ $question['current'] < $question['total'] ? 'Question suivante' : 'Terminer le test' }}
        </button>
    </form>

    {{-- Zoom modal --}}
    <div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
        <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;">
        <button onclick="document.getElementById('zoom-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1.1rem;cursor:pointer;">×</button>
    </div>

    <script>
    function setZoomImage(src) { document.getElementById('zoom-img').src = src; document.getElementById('zoom-modal').style.display = 'flex'; }

    let seconds = 0;
    const timerDisplay = document.getElementById('timer-display');
    const elapsedInput = document.getElementById('elapsed_seconds');
    const timer = setInterval(() => {
        seconds++;
        elapsedInput.value = seconds;
        timerDisplay.textContent = Math.floor(seconds/60) + ':' + String(seconds%60).padStart(2,'0');
    }, 1000);
    document.querySelector('form').addEventListener('submit', () => clearInterval(timer));

    document.querySelectorAll('.test-option').forEach(label => {
        label.addEventListener('click', function() {
            document.querySelectorAll('.test-option').forEach(l => l.style.borderColor = 'var(--card-border)');
            this.style.borderColor = 'var(--accent)';
        });
    });

    var keyMap = {'1':0,'&':0,'2':1,'é':1,'"':2,'3':2,"'":3,'4':3};
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        var idx = keyMap[e.key];
        if (idx !== undefined) {
            var labels = document.querySelectorAll('.test-option');
            if (labels[idx]) {
                document.querySelectorAll('.test-option').forEach(l => l.style.borderColor = 'var(--card-border)');
                labels[idx].style.borderColor = 'var(--accent)';
                labels[idx].querySelector('input[type=radio]').checked = true;
            }
        }
    });
    </script>
</x-quiz-layout>
