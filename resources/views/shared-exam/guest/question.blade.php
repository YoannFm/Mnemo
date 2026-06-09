<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question {{ $question['current'] }} / {{ $question['total'] }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: #0d1117;
            color: #e6edf3;
            min-height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 2rem 1rem;
        }
        .guest-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 2rem;
            width: 100%;
            max-width: 560px;
        }
        .option-label {
            cursor: pointer;
            background: #0d1117;
            border: 2px solid #30363d;
            border-radius: 12px;
            padding: .5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            min-height: 80px;
            transition: border-color .15s;
            position: relative;
        }
        .option-label:hover {
            border-color: #58a6ff;
        }
        .option-label input {
            display: none;
        }
        .progress-bar-custom {
            background: #58a6ff;
            height: 8px;
            border-radius: 4px;
            transition: width .3s;
        }
        .progress-track {
            background: #30363d;
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }
        .btn-primary {
            background: #238636;
            border-color: #238636;
        }
        .btn-primary:hover {
            background: #2ea043;
            border-color: #2ea043;
        }
        .question-content-box {
            background: #0d1117;
            border: 1px solid #30363d;
            border-radius: 8px;
            padding: .75rem 1rem;
            margin: 1.25rem 0;
        }
    </style>
</head>
<body>
    <div class="guest-card">

        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;">
            <div>
                <span style="font-size:.8rem;color:#8b949e;">{{ $sharedExam->module->title ?? '' }}</span>
            </div>
            <span style="font-size:.85rem;color:#8b949e;font-weight:500;">
                {{ $question['current'] }} / {{ $question['total'] }}
            </span>
        </div>

        {{-- Progress --}}
        <div class="progress-track mb-4">
            <div class="progress-bar-custom" style="width:{{ ($question['current'] / $question['total']) * 100 }}%"></div>
        </div>

        {{-- Question --}}
        <div style="background:#0d1117;border:1px solid #30363d;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;">
            <div style="font-size:.78rem;color:#8b949e;margin-bottom:.75rem;">
                <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
            </div>
            <h6 style="font-size:1rem;margin-bottom:.75rem;">{{ $question['question_text'] }}</h6>

            @if ($question['field_question'] === 'photo_path')
                <div style="text-align:center;">
                    <div style="display:inline-block;position:relative;max-width:260px;width:100%;">
                        <img src="{{ $question['question_content'] }}"
                             alt="Question"
                             style="width:100%;aspect-ratio:1/1;border-radius:10px;object-fit:cover;display:block;">
                        <button type="button" onclick="setZoomImage('{{ $question['question_content'] }}')"
                                style="position:absolute;bottom:8px;right:8px;background:rgba(0,0,0,.65);border:none;border-radius:8px;padding:5px 10px;color:#fff;cursor:pointer;font-size:.85rem;">
                            <i class="bi bi-zoom-in"></i>
                        </button>
                    </div>
                </div>
            @else
                <div class="question-content-box">
                    <p style="margin:0;font-size:.95rem;">{{ $question['question_content'] }}</p>
                </div>
            @endif
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('guest.exam.answer', $sharedExam->uuid) }}" id="answerForm">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
                @foreach ($question['options'] as $index => $option)
                    <label class="option-label" id="label_{{ $index }}"
                           onclick="selectOption({{ $index }}, this)">
                        <input type="radio" name="answer" value="{{ $index }}" required>
                        @if ($question['field_answer'] === 'photo_path')
                            <img src="{{ $option }}"
                                 alt="Option {{ $index + 1 }}"
                                 style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                            <span onclick="event.preventDefault();event.stopPropagation();setZoomImage('{{ $option }}')"
                                  style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.7);border-radius:6px;padding:4px 8px;cursor:pointer;color:#fff;font-size:.8rem;z-index:10;line-height:1;">
                                <i class="bi bi-zoom-in"></i>
                            </span>
                        @else
                            <span style="font-size:.9rem;text-align:center;color:#e6edf3;word-break:break-word;">{{ $option }}</span>
                        @endif
                    </label>
                @endforeach
            </div>

            <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                <i class="bi bi-check-lg me-1"></i>
                @if ($question['current'] < $question['total'])
                    Question suivante
                @else
                    Terminer l'examen
                @endif
            </button>
        </form>

    </div>

    {{-- Zoom modal --}}
    <div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
        <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;">
        <button onclick="document.getElementById('zoom-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1.1rem;cursor:pointer;">×</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectOption(index, el) {
            document.querySelectorAll('.option-label').forEach(l => l.style.borderColor = '#30363d');
            el.style.borderColor = '#58a6ff';
            document.querySelectorAll('input[name=answer]')[index].checked = true;
            document.getElementById('submitBtn').disabled = false;
        }
        function setZoomImage(src) {
            document.getElementById('zoom-img').src = src;
            document.getElementById('zoom-modal').style.display = 'flex';
        }
    </script>
</body>
</html>
