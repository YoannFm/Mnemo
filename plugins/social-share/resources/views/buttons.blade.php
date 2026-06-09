@if(isset($socialShareUrl))
<div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-top:1rem;">
    <span style="font-size:.8rem;color:var(--text-muted);">Partager :</span>
    <a href="https://twitter.com/intent/tweet?text={{ $socialShareText }}&url={{ urlencode($socialShareUrl) }}"
       target="_blank" rel="noopener"
       class="btn btn-sm" style="background:#000;color:#fff;border:none;font-size:.8rem;">
        <i class="bi bi-twitter-x me-1"></i>X
    </a>
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode($socialShareUrl) }}"
       target="_blank" rel="noopener"
       class="btn btn-sm" style="background:#0077b5;color:#fff;border:none;font-size:.8rem;">
        <i class="bi bi-linkedin me-1"></i>LinkedIn
    </a>
    <button type="button" onclick="navigator.clipboard.writeText('{{ $socialShareUrl }}').then(()=>this.innerHTML='<i class=\'bi bi-check-lg\'></i> Copié')"
            class="btn btn-sm" style="border:1px solid var(--card-border);color:var(--text-muted);font-size:.8rem;">
        <i class="bi bi-clipboard me-1"></i>Copier le lien
    </button>
</div>
@endif
