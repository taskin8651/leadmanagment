<div class="modal fade" id="renewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Renew Subscription</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div data-role="form-view">
                <form method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6"><div class="muted small">Client</div><div class="fw-bold" data-role="modal-client">—</div></div>
                            <div class="col-6"><div class="muted small">Current Plan</div><div class="fw-bold" data-role="modal-plan">—</div></div>
                            <div class="col-6"><div class="muted small">Current Expiry</div><div class="fw-bold" data-role="modal-expiry">—</div></div>
                            <div class="col-6"><div class="muted small">New Expiry</div><div class="fw-bold text-primary-accent" data-role="new-expiry">—</div></div>
                        </div>
                        <label class="form-label">Select Plan</label>
                        <select class="form-select mb-3" name="plan_id">
                            @foreach($plans as $p)<option value="{{ $p->id }}" data-duration="{{ $p->duration_days }}" data-price="{{ $p->price }}">{{ $p->name }} — ₹{{ number_format($p->price,2) }} / {{ $p->duration_days }}d</option>@endforeach
                        </select>
                        <label class="form-label">Amount</label>
                        <div class="input-icon-group mb-3"><i class="bi bi-currency-rupee"></i><input class="form-control" type="number" step="0.01" min="0" name="amount"></div>
                        <label class="form-label">Payment Status</label>
                        <select class="form-select" name="payment_status">
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm Renewal</button>
                    </div>
                </form>
            </div>
            <div data-role="success-view" class="d-none text-center py-5 px-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width:56px;height:56px;border-radius:16px;background:var(--success-soft);color:var(--success);font-size:26px"><i class="bi bi-check-lg"></i></div>
                <h5 class="fw-bold mb-1">Subscription Renewed</h5>
                <p class="muted">The client's plan has been successfully renewed.</p>
            </div>
        </div>
    </div>
</div>
