{{-- Modal: Générer un lien d'examen partagé --}}
<div class="modal fade" id="shareExamModal" tabindex="-1" aria-labelledby="shareExamModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);">
            <div class="modal-header" style="border-bottom:1px solid var(--card-border);">
                <h5 class="modal-title" id="shareExamModalLabel">
                    <i class="bi bi-share me-2"></i>Générer un lien d'examen partagé
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <form method="POST" action="{{ route('shared-exam.create', $module) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="share_label" class="form-label" style="font-size:.875rem;color:var(--text-muted);">
                            Étiquette <span style="font-size:.8rem;">(optionnel)</span>
                        </label>
                        <input type="text"
                               class="form-control"
                               id="share_label"
                               name="label"
                               placeholder="Ex: Classe de terminale"
                               maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label for="share_mode" class="form-label" style="font-size:.875rem;color:var(--text-muted);">
                            Mode de questions
                        </label>
                        <select class="form-select" id="share_mode" name="mode" required>
                            <option value="random">Aléatoire</option>
                            <option value="photo_to_name_fr">Photo → Nom FR</option>
                            <option value="photo_to_name_alt">Photo → Nom EN</option>
                            <option value="photo_to_function">Photo → Fonction</option>
                            <option value="function_to_photo">Fonction → Photo</option>
                            <option value="function_to_name_fr">Fonction → Nom FR</option>
                            <option value="function_to_name_alt">Fonction → Nom EN</option>
                            <option value="name_fr_to_name_alt">Nom FR → Nom EN</option>
                            <option value="name_fr_to_photo">Nom FR → Photo</option>
                            <option value="name_fr_to_function">Nom FR → Fonction</option>
                            <option value="name_alt_to_photo">Nom EN → Photo</option>
                            <option value="name_alt_to_function">Nom EN → Fonction</option>
                            <option value="name_alt_to_name_fr">Nom EN → Nom FR</option>
                            <option value="audio_to_name_fr">Audio → Nom FR</option>
                            <option value="audio_to_name_alt">Audio → Nom EN</option>
                            <option value="name_fr_to_audio">Nom FR → Audio</option>
                            <option value="name_alt_to_audio">Nom EN → Audio</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="share_expires_at" class="form-label" style="font-size:.875rem;color:var(--text-muted);">
                            Date d'expiration <span style="font-size:.8rem;">(optionnel)</span>
                        </label>
                        @php
                            $defaultDays = \App\Models\Setting::get('exam_default_expires_days');
                            $defaultExpiry = $defaultDays ? now()->addDays((int)$defaultDays)->format('Y-m-d\TH:i') : '';
                        @endphp
                        <input type="datetime-local"
                               class="form-control"
                               id="share_expires_at"
                               name="expires_at"
                               value="{{ $defaultExpiry }}">
                    </div>
                    @includeIf('webhook::field', ['sharedExam' => null])
                </div>
                <div class="modal-footer" style="border-top:1px solid var(--card-border);">
                    <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                            style="color:var(--text-muted);border:1px solid var(--card-border);">
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-link-45deg me-1"></i>Générer le lien
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
