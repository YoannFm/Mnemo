<x-admin-layout>
    <x-slot name="pageTitle">Paramètres - Accueil</x-slot>

    <div class="card shadow mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Message de la page d'accueil</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.settings.home.update') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label" for="home_message">Message affiché sur la page d'accueil des utilisateurs</label>
                    <textarea id="home_message" name="home_message"
                              class="form-control @error('home_message') is-invalid @enderror"
                              rows="6">{{ old('home_message', $home_message) }}</textarea>
                    @error('home_message')<span class="invalid-feedback">{{ $message }}</span>@enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-1"></i> Sauvegarder
                </button>
            </form>
        </div>
    </div>

<script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
<script>
(function () {
    function initTinyMCE() {
        var dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        if (tinymce.get('home_message')) tinymce.remove('#home_message');
        tinymce.init({
            selector: '#home_message',
            base_url: '{{ asset('vendor/tinymce') }}',
            license_key: 'gpl',
            promotion: false,
            height: 350,
            plugins: 'searchreplace autolink code link lists',
            toolbar: 'blocks bold italic underline strikethrough | link | alignleft aligncenter alignright | bullist numlist | removeformat code | undo redo',
            skin: dark ? 'oxide-dark' : 'oxide',
            content_css: dark ? 'dark' : 'default',
        });
    }
    initTinyMCE();
    new MutationObserver(function (m) { m.forEach(function (mm) { if (mm.attributeName === 'data-bs-theme') initTinyMCE(); }); }).observe(document.documentElement, { attributes: true });
})();
</script>
</x-admin-layout>
