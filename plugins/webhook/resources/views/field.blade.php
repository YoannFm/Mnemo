<div class="mb-3">
    <label for="webhook_url" style="font-size:.875rem;font-weight:500;color:var(--text-primary);">
        <i class="bi bi-send-fill me-1" style="color:var(--accent);"></i>URL Webhook
    </label>
    <input type="url" name="webhook_url" id="webhook_url"
           class="form-control"
           placeholder="https://hooks.zapier.com/..."
           value="{{ old('webhook_url', $sharedExam->webhook_url ?? '') }}"
           style="background:var(--card-bg);border-color:var(--card-border);color:var(--text-primary);">
    <div style="font-size:.78rem;color:var(--text-muted);margin-top:.25rem;">
        Les résultats seront envoyés en JSON à cette URL à la fin de chaque examen.
    </div>
</div>
