@extends('admin.layouts.app')

@section('title', 'Ticket #' . $ticket->reference_id)

@section('content')

    <x-breadcrumb :items="[
        ['label' => 'Support', 'url' => route('admin.support.index')],
        ['label' => 'Ticket #' . $ticket->reference_id],
    ]" />

    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center py-3">
                    <div>
                        <h3 class="card-title mb-1 fs-3">
                            {{ $ticket->subject ?? 'Support Ticket' }} 
                            <span class="text-muted ms-2 fw-normal">#{{ $ticket->reference_id }}</span>
                        </h3>
                        <div class="text-secondary small mt-1">
                            <span class="me-3"><i class="ti ti-calendar me-1"></i> {{ $ticket->created_at ? $ticket->created_at->format('d M Y, h:i A') : 'N/A' }}</span>
                            <span class="me-3"><i class="ti ti-category me-1"></i> {{ $ticket->category ?? 'General' }}</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        @php
                            $statusColors = [1 => 'blue', 2 => 'yellow', 3 => 'green'];
                            $statusColor = $statusColors[$ticket->status] ?? 'secondary';
                            
                            $priorityColors = [1 => 'success', 2 => 'warning', 3 => 'danger'];
                            $priorityColor = $priorityColors[$ticket->priority] ?? 'secondary';
                        @endphp
                        <span class="badge badge-outline text-{{ $statusColor }} border-{{ $statusColor }} fs-9 px-2 py-1">{{ $ticket->status_text }}</span>
                        <span class="badge badge-outline text-{{ $priorityColor }} border-{{ $priorityColor }} fs-9 px-2 py-1">Priority: {{ $ticket->priority_text }}</span>
                    </div>
                </div>

                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" data-bs-toggle="tabs">
                        <li class="nav-item">
                            <a href="#tabs-conversation" class="nav-link active" data-bs-toggle="tab">
                                <i class="ti ti-message-circle me-2"></i> Conversation
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tabs-details" class="nav-link" data-bs-toggle="tab">
                                <i class="ti ti-settings me-2"></i> Settings & Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#tabs-internal-notes" class="nav-link" data-bs-toggle="tab">
                                <i class="ti ti-notes me-2"></i> Internal Notes
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="card-body p-0">
                    <div class="tab-content">
                        
                        {{-- ── TAB: CONVERSATION ─────────────────────────────────────────── --}}
                        <div class="tab-pane active show" id="tabs-conversation">
                            
                            <div class="p-4" style="max-height: 600px; overflow-y: auto;">
                                @forelse($ticket->messages as $msg)
                                    @if($msg->is_admin)
                                        <!-- Outgoing Message (Admin) -->
                                        <div class="d-flex justify-content-end mb-4">
                                            <div class="d-flex flex-column align-items-end" style="max-width: 80%;">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="text-muted small me-2">{{ $msg->created_at ? $msg->created_at->format('d M Y, h:i A') : '' }}</span>
                                                    <span class="small fw-semibold text-body">You (Admin)</span>
                                                </div>
                                                <div class="p-3 bg-primary-lt text-body rounded-3 text-start border border-primary-subtle">
                                                    {!! nl2br(e($msg->message)) !!}
                                                    
                                                    @if(!empty($msg->attachments))
                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                            @foreach($msg->attachments as $att)
                                                                @php
                                                                    $ext = pathinfo($att, PATHINFO_EXTENSION);
                                                                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                                @endphp
                                                                @if($isImage)
                                                                    <a href="{{ Storage::url($att) }}" target="_blank" class="d-block border rounded">
                                                                        <img src="{{ Storage::url($att) }}" class="rounded object-fit-cover" style="width: 80px; height: 80px;" alt="Attachment">
                                                                    </a>
                                                                @else
                                                                    <a href="{{ Storage::url($att) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                        <i class="ti ti-file me-1"></i> Attachment
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="ms-3 mt-4">
                                                <span class="avatar avatar-sm bg-primary text-white">A</span>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Incoming Message (User) -->
                                        <div class="d-flex justify-content-start mb-4">
                                            <div class="me-3 mt-4">
                                                @if(isset($msg->user) && $msg->user->profile_photo)
                                                    <span class="avatar avatar-sm" style="background-image: url('{{ Storage::url($msg->user->profile_photo) }}')"></span>
                                                @else
                                                    <span class="avatar avatar-sm bg-secondary text-white">{{ mb_strtoupper(mb_substr($msg->user->name ?? 'U', 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div class="d-flex flex-column align-items-start" style="max-width: 80%;">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="small fw-semibold text-body me-2">{{ $msg->user->name ?? 'User' }}</span>
                                                    <span class="text-muted small">{{ $msg->created_at ? $msg->created_at->format('d M Y, h:i A') : '' }}</span>
                                                </div>
                                                <div class="p-3 bg-light text-body rounded-3 text-start border">
                                                    {!! nl2br(e($msg->message)) !!}
                                                    
                                                    @if(!empty($msg->attachments))
                                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                                            @foreach($msg->attachments as $att)
                                                                @php
                                                                    $ext = pathinfo($att, PATHINFO_EXTENSION);
                                                                    $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                                                @endphp
                                                                @if($isImage)
                                                                    <a href="{{ Storage::url($att) }}" target="_blank" class="d-block border rounded">
                                                                        <img src="{{ Storage::url($att) }}" class="rounded object-fit-cover" style="width: 80px; height: 80px;" alt="Attachment">
                                                                    </a>
                                                                @else
                                                                    <a href="{{ Storage::url($att) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                                        <i class="ti ti-file me-1"></i> Attachment
                                                                    </a>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                    <div class="empty">
                                        <div class="empty-icon">
                                            <i class="ti ti-message text-muted h1"></i>
                                        </div>
                                        <p class="empty-title">No conversation yet</p>
                                        <p class="empty-subtitle text-secondary">Be the first to reply to this ticket.</p>
                                    </div>
                                @endforelse
                            </div>

                            <div class="card-footer bg-light p-3 border-top">
                                @if(!$ticket->isClosed())
                                    <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <textarea class="form-control" rows="3" placeholder="Type your reply here..." name="message" required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <button type="button" class="btn btn-icon btn-outline-secondary" data-bs-toggle="tooltip" title="Attach Files" onclick="document.getElementById('attachments').click()">
                                                    <i class="ti ti-paperclip"></i>
                                                </button>
                                                <input type="file" id="attachments" name="attachments[]" multiple class="d-none">
                                                <span id="attachment-count" class="small text-muted"></span>
                                            </div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="ti ti-send me-2"></i> Send Reply
                                            </button>
                                        </div>
                                    </form>
                                @else
                                    <div class="alert alert-warning mb-0 d-flex align-items-center">
                                        <i class="ti ti-alert-circle me-3 fs-2"></i>
                                        <div>
                                            <h4 class="alert-title mb-1">Ticket Closed</h4>
                                            <div class="text-secondary">This ticket is closed. Change the status in settings to reopen it before replying.</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        {{-- ── TAB: SETTINGS & PROFILE ───────────────────────────────────── --}}
                        <div class="tab-pane p-4" id="tabs-details">
                            <div class="row row-cards">
                                
                                {{-- Ticket Settings --}}
                                <div class="col-md-6">
                                    <h4 class="mb-3 text-body">Ticket Settings</h4>
                                    
                                    <div class="mb-4">
                                        <label class="form-label text-muted text-uppercase small fw-bold">Update Status</label>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-{{ $statusColor }} dropdown-toggle w-100 d-flex justify-content-between align-items-center" type="button" data-bs-toggle="dropdown">
                                                <span>{{ $ticket->status_text }}</span>
                                            </button>
                                            <ul class="dropdown-menu w-100">
                                                @foreach(\App\Models\SupportTicket::getStatusList() as $val => $label)
                                                    <li>
                                                        <form action="{{ route('admin.support.update-status', $ticket->id) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="status" value="{{ $val }}">
                                                            <button type="submit" class="dropdown-item py-2 {{ $ticket->status == $val ? 'active' : '' }}">
                                                                {{ $label }}
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>

                                    <div class="datagrid">
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Priority</div>
                                            <div class="datagrid-content">
                                                <span class="badge badge-outline text-{{ $priorityColor }} border-{{ $priorityColor }}">{{ $ticket->priority_text }}</span>
                                            </div>
                                        </div>
                                        <div class="datagrid-item">
                                            <div class="datagrid-title">Created At</div>
                                            <div class="datagrid-content">{{ $ticket->created_at ? $ticket->created_at->format('d M Y, h:i A') : 'N/A' }}</div>
                                        </div>
                                        @if($ticket->resolved_at)
                                            <div class="datagrid-item">
                                                <div class="datagrid-title">Resolved At</div>
                                                <div class="datagrid-content">{{ $ticket->resolved_at->format('d M Y, h:i A') }}</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- User Profile --}}
                                <div class="col-md-6">
                                    <h4 class="mb-3 text-body">User Profile</h4>
                                    
                                    @if($ticket->user)
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="me-3">
                                                @if($ticket->user->profile_photo)
                                                    <span class="avatar avatar-lg" style="background-image: url('{{ Storage::url($ticket->user->profile_photo) }}')"></span>
                                                @else
                                                    <span class="avatar avatar-lg bg-secondary text-white">{{ mb_strtoupper(mb_substr($ticket->user->name ?? 'U', 0, 1)) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="fs-4 fw-medium text-body mb-1">{{ $ticket->user->name ?? 'Unknown' }}</div>
                                                <div class="text-secondary small">
                                                    ID: {{ $ticket->user->id }} &middot; 
                                                    @if($ticket->user->role == 1)
                                                        Patient
                                                    @else
                                                        Nurse
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="datagrid mb-4">
                                            <div class="datagrid-item w-100">
                                                <div class="datagrid-title">Email Address</div>
                                                <div class="datagrid-content">{{ $ticket->user->email ?? 'No email provided' }}</div>
                                            </div>
                                            <div class="datagrid-item w-100">
                                                <div class="datagrid-title">Phone Number</div>
                                                <div class="datagrid-content">{{ $ticket->user->phone ?? 'No phone provided' }}</div>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            @if($ticket->user->role == 1)
                                                <a href="{{ route('admin.patients.show', $ticket->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    View Patient Profile
                                                </a>
                                            @else
                                                <a href="{{ route('admin.nurses.show', $ticket->user->id) }}" class="btn btn-outline-primary btn-sm w-100">
                                                    View Nurse Profile
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <div class="empty py-4">
                                            <div class="empty-icon">
                                                <i class="ti ti-user-off text-muted h2"></i>
                                            </div>
                                            <p class="empty-title">User profile is missing.</p>
                                        </div>
                                    @endif
                                </div>
                                
                            </div>
                        </div>

                        {{-- ── TAB: INTERNAL NOTES (COMMENTS) ────────────────────────────── --}}
                        <div class="tab-pane p-4" id="tabs-internal-notes">
                            <x-comments type="{{ \App\Models\SupportTicket::class }}" :model-id="$ticket->id" />
                        </div>
                        
                    </div>
                </div>
            </div>
            
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('attachments').addEventListener('change', function(e) {
        var count = e.target.files.length;
        var el = document.getElementById('attachment-count');
        if(count > 0) {
            el.innerHTML = '<span class="badge bg-secondary-lt text-secondary px-2 py-1"><i class="ti ti-paperclip me-1"></i> ' + count + ' file(s) selected</span>';
        } else {
            el.innerHTML = '';
        }
    });
</script>
@endpush
