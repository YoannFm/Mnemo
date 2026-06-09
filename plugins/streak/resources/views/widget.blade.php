<div class="mt-4">
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;line-height:1;">🔥</div>
                <div style="font-size:2rem;font-weight:800;color:var(--accent);line-height:1.2;">
                    {{ $userStreak->current_streak ?? 0 }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Streak actuel
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card p-4 text-center">
                <div style="font-size:2.5rem;line-height:1;">🏆</div>
                <div style="font-size:2rem;font-weight:800;color:var(--accent);line-height:1.2;">
                    {{ $userStreak->longest_streak ?? 0 }}
                </div>
                <div style="font-size:.7rem;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--text-muted);margin-top:.4rem;">
                    Record
                </div>
            </div>
        </div>
    </div>
</div>
