<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Submitted - {{ $ticket->ticket_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 30px 0;
        }
        .success-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        .success-header {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: bounce 1s ease;
        }
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .ticket-number {
            background: rgba(255,255,255,0.2);
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 2px;
            margin-top: 20px;
        }
        .success-body {
            padding: 30px;
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
        .badge-priority {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7">
                <div class="success-card">
                    <div class="success-header">
                        <div class="success-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h2>Request Submitted Successfully!</h2>
                        <p class="mb-0">Your maintenance request has been received</p>
                        <div class="ticket-number">
                            {{ $ticket->ticket_number }}
                        </div>
                    </div>

                    <div class="success-body">
                        <div class="alert alert-info">
                            <i class="fas fa-envelope me-2"></i>
                            A confirmation email has been sent to <strong>{{ $ticket->requestor_email }}</strong>
                        </div>

                        <div class="info-box">
                            <h6><i class="fas fa-ticket-alt me-2"></i>Ticket Details</h6>
                            <div class="info-row">
                                <span class="info-label">Ticket Number</span>
                                <span class="info-value">{{ $ticket->ticket_number }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Status</span>
                                <span class="badge {{ $ticket->getStatusBadgeClass() }}">{{ ucfirst(str_replace('_', ' ', $ticket->status)) }}</span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Submitted At</span>
                                <span class="info-value">{{ $ticket->created_at->format('d M Y, H:i') }}</span>
                            </div>
                        </div>

                        <div class="info-box">
                            <h6><i class="fas fa-clipboard-list me-2"></i>Request Information</h6>
                            <div class="info-row">
                                <span class="info-label">Problem Category</span>
                                <span class="badge {{ $ticket->getProblemCategoryBadgeClass() }}">
                                    <i class="fas {{ $ticket->getProblemCategoryIcon() }} me-1"></i>
                                    {{ $ticket->getProblemCategoryLabel() }}
                                </span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Problem Description</span>
                                <span class="info-value">{{ Str::limit($ticket->problem_description, 100) }}</span>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-bookmark me-2"></i>
                            <strong>Save your ticket number:</strong> {{ $ticket->ticket_number }}<br>
                            <small>You'll need this to track your request status.</small>
                        </div>

                        <div class="d-flex gap-2 justify-content-center">
                            <a href="{{ route('maintenance-request.track', ['ticket' => $ticket->ticket_number]) }}" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Track Your Ticket
                            </a>
                            <a href="{{ route('maintenance-request.create') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-plus me-2"></i>Submit Another
                            </a>
                        </div>
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
