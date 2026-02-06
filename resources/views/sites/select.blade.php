<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Site - Warehouse Maintenance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --blibli-blue: #0072ff;
            --blibli-yellow: #ffcd00;
            --blibli-red: #ff4646;
            --blibli-green: #02c82b;
            --blibli-cyan: #0bc4ff;
            --blibli-teal: #00cdc7;
            --blibli-orange: #ff7f00;
            --blibli-pink: #ff31ab;
        }

        body {
            background: url('{{ asset('assets/maxresdefault.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow-x: hidden;
        }

        /* Overlay for background image */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 0;
        }

        .site-selection-container {
            max-width: 1100px;
            width: 100%;
            position: relative;
            z-index: 1;
        }

        .main-title {
            color: white;
            text-align: center;
            margin-bottom: 50px;
        }

        .main-title .logo-wrapper {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .main-title .logo-wrapper img {
            width: 50px;
            height: auto;
        }

        .main-title h1 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 2.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .main-title p {
            opacity: 0.9;
            font-size: 1.1rem;
        }

        .site-card {
            background: white;
            border-radius: 20px;
            padding: 30px 25px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 3px solid transparent;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .site-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--blibli-blue), var(--blibli-cyan));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .site-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 114, 255, 0.3);
            border-color: var(--blibli-blue);
        }

        .site-card:hover::before {
            opacity: 1;
        }

        /* Different colors for each card */
        .site-card:nth-child(1) .site-logo { background: linear-gradient(135deg, var(--blibli-blue) 0%, var(--blibli-cyan) 100%); }
        .site-card:nth-child(2) .site-logo { background: linear-gradient(135deg, var(--blibli-green) 0%, var(--blibli-teal) 100%); }
        .site-card:nth-child(3) .site-logo { background: linear-gradient(135deg, var(--blibli-orange) 0%, var(--blibli-yellow) 100%); }
        .site-card:nth-child(4) .site-logo { background: linear-gradient(135deg, var(--blibli-pink) 0%, var(--blibli-red) 100%); }
        .site-card:nth-child(5) .site-logo { background: linear-gradient(135deg, var(--blibli-cyan) 0%, var(--blibli-teal) 100%); }
        .site-card:nth-child(6) .site-logo { background: linear-gradient(135deg, var(--blibli-yellow) 0%, var(--blibli-orange) 100%); }

        .card-wrapper:nth-child(1) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-blue) 0%, var(--blibli-cyan) 100%); }
        .card-wrapper:nth-child(2) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-green) 0%, var(--blibli-teal) 100%); }
        .card-wrapper:nth-child(3) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-orange) 0%, var(--blibli-yellow) 100%); }
        .card-wrapper:nth-child(4) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-pink) 0%, var(--blibli-red) 100%); }
        .card-wrapper:nth-child(5) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-cyan) 0%, var(--blibli-teal) 100%); }
        .card-wrapper:nth-child(6) .site-card .site-logo { background: linear-gradient(135deg, var(--blibli-yellow) 0%, var(--blibli-orange) 100%); }

        .card-wrapper:nth-child(1) .site-card:hover { border-color: var(--blibli-blue); }
        .card-wrapper:nth-child(2) .site-card:hover { border-color: var(--blibli-green); }
        .card-wrapper:nth-child(3) .site-card:hover { border-color: var(--blibli-orange); }
        .card-wrapper:nth-child(4) .site-card:hover { border-color: var(--blibli-pink); }
        .card-wrapper:nth-child(5) .site-card:hover { border-color: var(--blibli-cyan); }
        .card-wrapper:nth-child(6) .site-card:hover { border-color: var(--blibli-yellow); }

        .site-card .site-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .site-card .site-logo i {
            font-size: 36px;
            color: white;
        }

        .site-card .site-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .site-card h4 {
            color: #1a1a2e;
            margin-bottom: 10px;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .site-card p {
            color: #666;
            font-size: 14px;
            margin-bottom: 0;
            line-height: 1.5;
        }

        .site-card .site-code {
            display: inline-block;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            color: #666;
            margin-top: 15px;
            font-weight: 500;
        }

        .no-sites {
            background: white;
            border-radius: 20px;
            padding: 60px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        .no-sites i {
            font-size: 70px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

        .no-sites h4 {
            color: #1a1a2e;
            font-weight: 600;
        }

        .alert {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .alert-danger {
            background: linear-gradient(135deg, var(--blibli-red) 0%, #ff6b6b 100%);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, var(--blibli-green) 0%, var(--blibli-teal) 100%);
            color: white;
        }

        /* Footer branding */
        .footer-brand {
            text-align: center;
            margin-top: 40px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .footer-brand span {
            color: var(--blibli-yellow);
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="site-selection-container">
        <div class="main-title">
            <div class="logo-wrapper">
                <img src="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.svg') }}" alt="Blibli Logo">
            </div>
            <h1>Warehouse Maintenance</h1>
            <p>Select a site to continue</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($sites->count() > 0)
            <div class="row g-4 justify-content-center">
                @foreach($sites as $index => $site)
                    <div class="col-lg-4 col-md-6 card-wrapper">
                        <form action="{{ route('site.choose') }}" method="POST" class="h-100">
                            @csrf
                            <input type="hidden" name="site_code" value="{{ $site->code }}">
                            <button type="submit" class="site-card w-100 border-0">
                                <div class="site-logo">
                                    @if($site->logo)
                                        <img src="{{ asset('storage/' . $site->logo) }}" alt="{{ $site->name }}">
                                    @else
                                        <i class="fas fa-warehouse"></i>
                                    @endif
                                </div>
                                <h4>{{ $site->name }}</h4>
                                @if($site->description)
                                    <p>{{ $site->description }}</p>
                                @endif
                                <span class="site-code">{{ $site->code }}</span>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="no-sites">
                <i class="fas fa-building"></i>
                <h4>No Sites Available</h4>
                <p class="text-muted">Please contact your administrator to set up sites.</p>
            </div>
        @endif

        <div class="footer-brand">
            Powered by <span>Blibli</span> Warehouse Management
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
