                {{-- Booking Timeline --}}
                <div class="card shadow-sm mb-7 border border">
                    <div class="card-header border-0 pt-4 min-h-50px">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold mb-0 text-body">Booking Timeline</span>
                        </h3>
                    </div>
                    <div class="card-body pt-2 pb-5">
                        <div class="timeline">
                            {{-- Created --}}
                            <div class="timeline-item">
                                <div class="timeline-line w-40px"></div>
                                <div class="timeline-icon symbol symbol-circle symbol-40px">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ti ti-plus fs-3 text-primary"></i>
                                    </div>
                                </div>
                                <div class="timeline-content mb-10 mt-n1">
                                    <div class="pe-3 mb-2">
                                        <div class=" fw-bold text-body mb-1">Booking Created</div>
                                        <div class="d-flex align-items-center fw-semibold text-muted">
                                            <i class="ti ti-clock small me-1"></i>
                                            {{ $booking->created_at->format('d M Y, h:i A') }}
                                            <span class="text-muted ms-2">({{ $booking->created_at->diffForHumans() }})</span>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge badge bg-blue-lt border border-primary px-2 py-1">Ref: {{ $booking->reference_id }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Payment --}}
                            @if($booking->payment_status >= 1)
                                <div class="timeline-item">
                                    <div class="timeline-line w-40px"></div>
                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                        <div class="symbol-label bg-light-success">
                                            <i class="ti ti-currency-rupee fs-3 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content mb-10 mt-n1">
                                        <div class="pe-3 mb-2">
                                            <div class=" fw-bold text-body mb-1">Payment Received</div>
                                            <div class="d-flex align-items-center fw-semibold text-muted">
                                                <i class="ti ti-clock small me-1"></i>
                                                <span>₹{{ number_format($booking->total_amount, 2) }}</span>
                                                @if($booking->payment_method)
                                                    <span class="badge badge bg-cyan-lt px-2 py-1 ms-2">{{ $booking->payment_method_text }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            @if($booking->wallet_amount_used > 0)
                                                <span class="badge badge bg-yellow-lt border border-warning px-2 py-1">Wallet: ₹{{ number_format($booking->wallet_amount_used, 2) }}</span>
                                            @endif
                                            @if($booking->gateway_amount > 0)
                                                <span class="badge badge bg-blue-lt border border-primary px-2 py-1">Gateway: ₹{{ number_format($booking->gateway_amount, 2) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Active --}}
                            @if($booking->status >= 2)
                                <div class="timeline-item">
                                    <div class="timeline-line w-40px"></div>
                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                        <div class="symbol-label bg-light-info">
                                            <i class="ti ti-circle fs-3 text-info"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content mb-10 mt-n1">
                                        <div class="pe-3 mb-2">
                                            <div class=" fw-bold text-body mb-1">Booking Activated</div>
                                            <div class="fs-8 fw-semibold text-muted">Sessions started being tracked</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Completed --}}
                            @if($booking->status === \App\Models\Booking::STATUS_COMPLETED)
                                <div class="timeline-item">
                                    <div class="timeline-line w-40px"></div>
                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                        <div class="symbol-label bg-light-success">
                                            <i class="ti ti-check-circle fs-3 text-success"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content mb-10 mt-n1">
                                        <div class="pe-3 mb-2">
                                            <div class=" fw-bold text-body mb-1">Booking Completed</div>
                                            <div class="fs-8 fw-semibold text-muted">All {{ $booking->total_sessions }} sessions completed</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Cancelled --}}
                            @if($booking->status === \App\Models\Booking::STATUS_CANCELLED)
                                <div class="timeline-item">
                                    <div class="timeline-line w-40px"></div>
                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                        <div class="symbol-label bg-light-danger">
                                            <i class="ti ti-x-circle fs-3 text-danger"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content mb-10 mt-n1">
                                        <div class="pe-3 mb-2">
                                            <div class=" fw-bold text-body mb-1">Booking Cancelled</div>
                                            <div class="d-flex align-items-center fw-semibold text-muted">
                                                <i class="ti ti-clock small me-1"></i>
                                                {{ $booking->cancelled_at ? $booking->cancelled_at->format('d M Y, h:i A') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Refund --}}
                            @if($booking->refund_amount > 0)
                                <div class="timeline-item">
                                    <div class="timeline-line w-40px"></div>
                                    <div class="timeline-icon symbol symbol-circle symbol-40px">
                                        <div class="symbol-label bg-light-warning">
                                            <i class="ti ti-circle fs-3 text-warning"></i>
                                        </div>
                                    </div>
                                    <div class="timeline-content mb-10 mt-n1">
                                        <div class="pe-3 mb-2">
                                            <div class=" fw-bold text-body mb-1">Refund Processed</div>
                                            <div class="fs-8 fw-semibold text-muted">₹{{ number_format($booking->refund_amount, 2) }} refunded</div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Updated --}}
                            <div class="timeline-item">
                                <div class="timeline-icon symbol symbol-circle symbol-40px">
                                    <div class="symbol-label bg-light">
                                        <i class="ti ti-clock fs-3 text-muted"></i>
                                    </div>
                                </div>
                                <div class="timeline-content mt-n1">
                                    <div class="pe-3">
                                        <div class="small fw-semibold text-muted">Last Updated: {{ $booking->updated_at->format('d M Y, h:i A') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
