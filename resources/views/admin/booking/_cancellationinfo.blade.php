                {{-- Cancellation Info --}}
                @if($booking->isCancelled())
                    <div class="card shadow-sm mb-7 bg-light-danger border border-danger border-dashed">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <i class="ti ti-x-circle fs-2 text-danger me-2"></i>
                                <span class="fw-bold text-body ">Cancellation Details</span>
                            </div>
                            <div class="d-flex flex-column gap-3">
                                <div class="d-flex flex-stack">
                                    <span class="text-muted fw-semibold small">Cancelled By</span>
                                    <span class="fw-bold small text-body">
                                        @switch($booking->cancelled_by)
                                            @case(1) <span class="badge badge bg-yellow-lt border border-warning">User</span> @break
                                            @case(2) <span class="badge badge bg-cyan-lt border border-info">Nurse</span> @break
                                            @case(3) <span class="badge badge bg-red-lt border border-danger">Admin</span> @break
                                            @case(4) <span class="badge badge bg-secondary-lt border border-secondary">System</span> @break
                                            @default <span class="text-muted">Unknown</span>
                                        @endswitch
                                    </span>
                                </div>
                                <div class="separator separator-dashed border"></div>
                                <div class="d-flex flex-stack">
                                    <span class="text-muted fw-semibold small">Cancelled At</span>
                                    <span class="fw-bold small text-body">{{ $booking->cancelled_at ? $booking->cancelled_at->format('d M Y, h:i A') : 'N/A' }}</span>
                                </div>
                                @if($booking->cancellation_reason)
                                    <div class="separator separator-dashed border"></div>
                                    <div>
                                        <span class="text-muted fw-semibold small d-block mb-1">Reason</span>
                                        <span class="fw-semibold small text-body">{{ $booking->cancellation_reason }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
