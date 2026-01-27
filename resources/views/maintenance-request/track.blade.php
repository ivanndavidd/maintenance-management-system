<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Ticket - {{ $ticket->ticket_number ?? 'Search' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .track-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .track-header {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .search-box {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .search-box input {
            border: none;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 1.1rem;
        }
        .search-box button {
            padding: 15px 30px;
            border-radius: 8px;
        }
        .track-body {
            padding: 30px;
        }
        .timeline {
            position: relative;
            padding-left: 40px;
        }
        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        .timeline-item {
            position: relative;
            padding-bottom: 25px;
        }
        .timeline-item:last-child {
            padding-bottom: 0;
        }
        .timeline-dot {
            position: absolute;
            left: -32px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e9ecef;
            border: 3px solid #fff;
            box-shadow: 0 0 0 2px #e9ecef;
        }
        .timeline-dot.active {
            background: #27ae60;
            box-shadow: 0 0 0 2px #27ae60;
        }
        .timeline-dot.current {
            background: #3498db;
            box-shadow: 0 0 0 2px #3498db;
            animation: pulse 1.5s infinite;
        }
        .timeline-dot.failed {
            background: #e74c3c;
            box-shadow: 0 0 0 2px #e74c3c;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h6 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            color: #7f8c8d;
        }
        .info-value {
            font-weight: 500;
            color: #2c3e50;
        }
        .status-badge {
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-pending { background: #f39c12; color: white; }
        .status-received { background: #3498db; color: white; }
        .status-in_progress { background: #9b59b6; color: white; }
        .status-completed { background: #27ae60; color: white; }
        .status-failed { background: #e74c3c; color: white; }
        .status-cancelled { background: #95a5a6; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="track-card">
                    <div class="track-header">
                        <h2><i class="fas fa-search me-2"></i>Track Your Ticket</h2>
                        <p class="mb-0">Enter your ticket number to see the current status</p>

                        <div class="search-box">
                            <form action="{{ route('maintenance-request.track.search') }}" method="GET" class="d-flex gap-2">
                                <input type="text" name="ticket" class="form-control"
                                       placeholder="Enter ticket number (e.g., CMR-20250119-0001)"
                                       value="{{ request('ticket') ?? ($ticket->ticket_number ?? '') }}">
                                <button type="submit" class="btn btn-light">
                                    <i class="fas fa-search"></i> Track
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="track-body">
                        @if(session('error'))
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                            </div>
                        @endif

                        @if(isset($ticket))
                            <div class="text-center mb-4">
                                <span class="status-badge status-{{ $ticket->status }}">
                                    {{ ucfirst(str_replace('_', ' ', $ticket->status)) }}
                                </span>
                            </div>

                            <div class="info-box">
                                <h6><i class="fas fa-ticket-alt me-2"></i>Ticket Information</h6>
                                <div class="info-row">
                                    <span class="info-label">Ticket Number</span>
                                    <span class="info-value">{{ $ticket->ticket_number }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Requestor</span>
                                    <span class="info-value">{{ $ticket->requestor_name }}</span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Submitted</span>
                                    <span class="info-value">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                                </div>
                            </div>

                            <div class="info-box">
                                <h6><i class="fas fa-clipboard-list me-2"></i>Request Details</h6>
                                <div class="info-row">
                                    <span class="info-label">Problem Category</span>
                                    <span class="badge {{ $ticket->getProblemCategoryBadgeClass() }}">
                                        <i class="fas {{ $ticket->getProblemCategoryIcon() }} me-1"></i>
                                        {{ $ticket->getProblemCategoryLabel() }}
                                    </span>
                                </div>
                                <div class="info-row">
                                    <span class="info-label">Problem Description</span>
                                    <span class="info-value">{{ $ticket->problem_description }}</span>
                                </div>
                            </div>

                            <div class="info-box">
                                <h6><i class="fas fa-clock me-2"></i>Progress Timeline</h6>
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-dot {{ $ticket->created_at ? 'active' : '' }}"></div>
                                        <strong>Submitted</strong>
                                        <p class="text-muted mb-0 small">{{ $ticket->created_at->format('d M Y, H:i') }}</p>
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-dot {{ $ticket->received_at ? 'active' : ($ticket->status == 'pending' ? 'current' : '') }}"></div>
                                        <strong>Received</strong>
                                        @if($ticket->received_at)
                                            <p class="text-muted mb-0 small">{{ $ticket->received_at->format('d M Y, H:i') }}</p>
                                        @else
                                            <p class="text-muted mb-0 small">Waiting for admin to acknowledge</p>
                                        @endif
                                    </div>
                                    <div class="timeline-item">
                                        <div class="timeline-dot {{ $ticket->in_progress_at ? 'active' : ($ticket->status == 'received' ? 'current' : '') }}"></div>
                                        <strong>In Progress</strong>
                                        @if($ticket->in_progress_at)
                                            <p class="text-muted mb-0 small">{{ $ticket->in_progress_at->format('d M Y, H:i') }}</p>
                                            @if($ticket->assignedUser)
                                                <p class="text-muted mb-0 small">Assigned to: {{ $ticket->assignedUser->name }}</p>
                                            @endif
                                        @else
                                            <p class="text-muted mb-0 small">Waiting for technician assignment</p>
                                        @endif
                                    </div>
                                    <div class="timeline-item">
                                        @if($ticket->status == 'failed')
                                            <div class="timeline-dot failed"></div>
                                            <strong class="text-danger">Failed</strong>
                                        @elseif($ticket->status == 'cancelled')
                                            <div class="timeline-dot failed"></div>
                                            <strong class="text-secondary">Cancelled</strong>
                                        @else
                                            <div class="timeline-dot {{ $ticket->completed_at ? 'active' : ($ticket->status == 'in_progress' ? 'current' : '') }}"></div>
                                            <strong>Completed</strong>
                                        @endif
                                        @if($ticket->completed_at)
                                            <p class="text-muted mb-0 small">{{ $ticket->completed_at->format('d M Y, H:i') }}</p>
                                        @elseif($ticket->status == 'cancelled')
                                            <p class="text-muted mb-0 small">This ticket has been cancelled</p>
                                        @else
                                            <p class="text-muted mb-0 small">Waiting for completion</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if($ticket->resolution)
                            <div class="info-box">
                                <h6><i class="fas fa-clipboard-check me-2"></i>Resolution</h6>
                                <p class="mb-0">{{ $ticket->resolution }}</p>
                            </div>
                            @endif

                            <div class="text-center mt-4">
                                <a href="{{ route('maintenance-request.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Submit New Request
                                </a>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-ticket-alt fa-4x text-muted mb-3"></i>
                                <h4>Enter Your Ticket Number</h4>
                                <p class="text-muted">Use the search box above to find your maintenance request ticket.</p>
                                <a href="{{ route('maintenance-request.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Submit New Request
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="text-center mt-4 text-white">
                    <small>&copy; {{ date('Y') }} Warehouse Maintenance System</small>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
