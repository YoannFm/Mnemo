@if (!empty($showOnboarding))
<div class="modal fade" id="onboardingModal" tabindex="-1" aria-labelledby="onboardingModalLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:1rem;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h4 class="modal-title fw-bold" id="onboardingModalLabel" style="color:var(--text-primary);">
                    Bienvenue sur Mnemo 👋
                </h4>
            </div>
            <div class="modal-body px-4 py-3">
                <p style="color:var(--text-muted);font-size:.95rem;line-height:1.6;">
                    Mnemo est une application de mémorisation par répétition espacée. Tu peux créer des modules thématiques, y ajouter des items question/réponse, puis t'entraîner en mode Anki ou Test pour maximiser ta mémorisation. Suis ta progression et regarde tes connaissances évoluer au fil du temps.
                </p>

                <div class="row g-3 my-3">
                    <div class="col-12 col-md-4">
                        <div class="p-3 text-center h-100" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:.75rem;">
                            <i class="bi bi-plus-circle" style="font-size:2rem;color:var(--accent);"></i>
                            <div class="fw-semibold mt-2" style="color:var(--text-primary);">1. Créer un module</div>
                            <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem;">
                                Organise tes connaissances par thème ou matière.
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 text-center h-100" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:.75rem;">
                            <i class="bi bi-list-ul" style="font-size:2rem;color:var(--accent);"></i>
                            <div class="fw-semibold mt-2" style="color:var(--text-primary);">2. Ajouter des items</div>
                            <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem;">
                                Saisis tes questions et réponses à mémoriser.
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 text-center h-100" style="background:var(--card-bg);border:1px solid var(--card-border);border-radius:.75rem;">
                            <i class="bi bi-lightning-charge" style="font-size:2rem;color:var(--accent);"></i>
                            <div class="fw-semibold mt-2" style="color:var(--text-primary);">3. S'entraîner</div>
                            <div style="font-size:.82rem;color:var(--text-muted);margin-top:.25rem;">
                                Lance une session Anki ou Test et suis ta progression.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 pt-2 d-flex gap-2 flex-wrap">
                <a href="@php try { echo route('modules.create'); } catch (\Exception $e) { echo '/modules/create'; } @endphp"
                   class="btn btn-primary"
                   id="onboardingCreateBtn">
                    <i class="bi bi-plus-lg me-1"></i> Créer mon premier module
                </a>
                <a href="@php try { echo route('library.index'); } catch (\Exception $e) { echo '/library'; } @endphp"
                   class="btn btn-outline-secondary"
                   id="onboardingLibraryBtn">
                    Explorer la bibliothèque
                </a>
                <button type="button" class="btn btn-link ms-auto" style="color:var(--text-muted);font-size:.85rem;"
                        id="onboardingDismissBtn">
                    Passer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    function dismissOnboarding() {
        const token = document.querySelector('meta[name="csrf-token"]');
        fetch('/onboarding/dismiss', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
            },
        }).catch(function () {});
    }

    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('onboardingModal');
        if (!modalEl) return;

        var modal = new bootstrap.Modal(modalEl);
        modal.show();

        // Dismiss on modal hide (close button, backdrop, etc.)
        modalEl.addEventListener('hide.bs.modal', function () {
            dismissOnboarding();
        });

        // Primary CTA: dismiss before navigating
        var createBtn = document.getElementById('onboardingCreateBtn');
        if (createBtn) {
            createBtn.addEventListener('click', function () {
                dismissOnboarding();
            });
        }

        // Library link: dismiss before navigating
        var libraryBtn = document.getElementById('onboardingLibraryBtn');
        if (libraryBtn) {
            libraryBtn.addEventListener('click', function () {
                dismissOnboarding();
            });
        }

        // "Passer" button
        var dismissBtn = document.getElementById('onboardingDismissBtn');
        if (dismissBtn) {
            dismissBtn.addEventListener('click', function () {
                modal.hide();
            });
        }
    });
})();
</script>
@endif
