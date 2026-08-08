<div class="modal fade" id="globalSearchModal" tabindex="-1" aria-hidden="true" data-endpoint="{{ $searchEndpoint }}">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="d-flex align-items-center gap-2 px-3" style="border-bottom:1px solid var(--border);height:58px">
                    <i class="bi bi-search muted"></i>
                    <input type="search" class="form-control border-0 shadow-none px-2" style="background:transparent" placeholder="Search leads, clients, phone…" aria-label="Global search">
                    <button type="button" class="btn btn-sm btn-ghost" data-bs-dismiss="modal" aria-label="Close search">Esc</button>
                </div>
                <div class="p-3" style="max-height:60vh;overflow-y:auto" data-role="search-results">
                    <div class="text-center py-4 muted small">Type at least 2 characters…</div>
                </div>
            </div>
        </div>
    </div>
</div>
