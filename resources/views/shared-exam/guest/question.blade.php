<x-app-layout>
    <x-slot name="pageTitle">Question {{ $question['current'] }} / {{ $question['total'] }}</x-slot>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-6">

            {{-- Header --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span style="font-size:.8rem;color:var(--text-muted);">{{ $sharedExam->module->title }}</span>
                <span style="font-size:.85rem;color:var(--text-muted);font-weight:500;">
                    {{ $question['current'] }} / {{ $question['total'] }}
                </span>
            </div>

            {{-- Progress --}}
            <div style="background:var(--card-border);height:6px;border-radius:3px;overflow:hidden;margin-bottom:1.5rem;">
                <div style="background:var(--accent);height:6px;width:{{ ($question['current'] / $question['total']) * 100 }}%;border-radius:3px;transition:width .3s;"></div>
            </div>

            {{-- Question --}}
            <div class="card p-3 mb-4">
                <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem;">
                    <i class="bi bi-tag me-1"></i>{{ $question['question_type'] }}
                </div>
                <h6 style="font-size:1rem;margin-bottom:.75rem;">{{ $question['question_text'] }}</h6>

                @if ($question['field_question'] === 'photo_path')
                    <div class="text-center">
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
                    <div class="card p-2 mt-1">
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
                               onclick="selectOption({{ $index }}, this)"
                               style="cursor:pointer;background:var(--card-bg);border:2px solid var(--card-border);border-radius:12px;padding:.5rem;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.5rem;min-height:80px;transition:border-color .15s;position:relative;">
                            <input type="radio" name="answer" value="{{ $index }}" required style="display:none;">
                            @if ($question['field_answer'] === 'photo_path')
                                <img src="{{ $option }}" alt="Option {{ $index + 1 }}"
                                     style="width:100%;aspect-ratio:1/1;border-radius:6px;object-fit:cover;display:block;">
                                <span onclick="event.preventDefault();event.stopPropagation();setZoomImage('{{ $option }}')"
                                      style="position:absolute;top:6px;right:6px;background:rgba(0,0,0,.7);border-radius:6px;padding:4px 8px;cursor:pointer;color:#fff;font-size:.8rem;z-index:10;line-height:1;">
                                    <i class="bi bi-zoom-in"></i>
                                </span>
                            @else
                                <span style="font-size:.9rem;text-align:center;color:var(--text-primary);word-break:break-word;">{{ $option }}</span>
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
    </div>

    {{-- Zoom modal --}}
    <div id="zoom-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9999;align-items:center;justify-content:center;" onclick="this.style.display='none'">
        <img id="zoom-img" src="" style="max-width:90vw;max-height:90vh;object-fit:contain;border-radius:10px;">
        <button onclick="document.getElementById('zoom-modal').style.display='none'" style="position:absolute;top:1rem;right:1rem;background:rgba(255,255,255,.15);border:none;border-radius:50%;width:36px;height:36px;color:#fff;font-size:1.1rem;cursor:pointer;">×</button>
    </div>

    <script>
        function selectOption(index, el) {
            document.querySelectorAll('.option-label').forEach(l => l.style.borderColor = 'var(--card-border)');
            el.style.borderColor = 'var(--accent)';
            document.querySelectorAll('input[name=answer]')[index].checked = true;
            document.getElementById('submitBtn').disabled = false;
        }
        function setZoomImage(src) {
            document.getElementById('zoom-img').src = src;
            document.getElementById('zoom-modal').style.display = 'flex';
        }
    </script>
</x-app-layout>
