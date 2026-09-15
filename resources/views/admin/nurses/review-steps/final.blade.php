<div class="card">
    <div class="card-header">
        <div>
            <h3 class="card-title">Final Decision</h3>
            <div class="text-muted small mt-1">Approve or reject the entire application</div>
        </div>
    </div>

    <div class="card-body">
        <div class="alert alert-info d-flex align-items-center mb-4" role="alert">
            <i class="ti ti-shield-check fs-2 me-3"></i>
            <div>
                <h4 class="alert-title mb-1">Ensure all sections are reviewed</h4>
                <div class="text-secondary small">
                    Before making a final decision, ensure all sections on the left have been appropriately approved or rejected.
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
            <button type="button" class="btn btn-success btn-lg" onclick="finalizeReview({{ \App\Models\NurseProfile::STATUS_APPROVED }})">
                <i class="ti ti-circle-check fs-2 me-2"></i> Officially Approve Application
            </button>
            <button type="button" class="btn btn-outline-danger btn-lg" onclick="finalizeReview({{ \App\Models\NurseProfile::STATUS_REJECTED }})">
                <i class="ti ti-circle-x fs-2 me-2"></i> Reject Entire Application
            </button>
        </div>
    </div>
</div>
