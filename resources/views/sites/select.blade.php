<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Site - Warehouse Maintenance</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: url('{{ asset('assets/maxresdefault.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Container */
        .main-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 16px;
            position: relative;
            z-index: 1;
        }

        /* Site Card */
        .site-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .hover-hint { display: none; }

        /* Header */
        .site-header {
            background: white;
            padding: 24px 20px 20px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        .site-header .logo {
            margin-bottom: 16px;
        }

        .site-header .logo img {
            height: 40px;
        }

        .site-header h3 {
            margin: 0;
            font-weight: 700;
            font-size: 20px;
            color: #333;
        }

        .site-header p {
            margin: 8px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        /* Body */
        .site-body {
            padding: 20px 16px;
        }

        @media (min-width: 480px) {
            .site-header { padding: 40px 35px 30px; }
            .site-header .logo { margin-bottom: 25px; }
            .site-header .logo img { height: 55px; }
            .site-header h3 { font-size: 28px; }
            .site-header p { font-size: 16px; margin-top: 12px; }
            .site-body { padding: 30px; }
        }

        /* Search Box */
        .search-box {
            position: relative;
            margin-bottom: 20px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--blibli-blue);
            box-shadow: 0 0 0 3px rgba(0, 114, 255, 0.15);
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        /* Site List */
        .site-list {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .site-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 15px;
            border: 1px solid #f0f0f0;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: white;
        }

        .site-item:hover {
            border-color: var(--blibli-blue);
            background: #f8fbff;
            transform: translateX(5px);
        }

        .site-item:last-child {
            margin-bottom: 0;
        }

        .site-item-content {
            flex: 1;
        }

        .site-item-name {
            font-size: 15px;
            color: #333;
            font-weight: 600;
        }

        .site-item-code {
            font-size: 12px;
            color: #888;
            margin-top: 3px;
        }

        .site-item-arrow {
            color: #ccc;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .site-item:hover .site-item-arrow {
            color: var(--blibli-blue);
            transform: translateX(3px);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 30px 20px;
            color: #999;
        }

        .no-results i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #ddd;
        }

        .no-results p {
            margin: 0;
            font-size: 14px;
        }

        /* Alert Messages */
        .alert {
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 13px;
            border: none;
            margin-bottom: 15px;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        /* Scrollbar styling - only show when needed */
        .site-list::-webkit-scrollbar {
            width: 5px;
        }

        .site-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .site-list::-webkit-scrollbar-thumb {
            background: transparent;
            border-radius: 3px;
        }

        .site-list:hover::-webkit-scrollbar-thumb {
            background: #ccc;
        }

        .site-list::-webkit-scrollbar-thumb:hover {
            background: #aaa;
        }

        /* Footer */
        .site-footer {
            text-align: center;
            padding: 15px 25px 20px;
            border-top: 1px solid #f0f0f0;
            color: #999;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="site-card">
            <!-- Header -->
            <div class="site-header">
                <div class="logo">
                    <img src="{{ asset('assets/Blibli_Logo_Horizontal_FC_RGB.svg') }}" alt="Blibli Logo">
                </div>
                <h3>Warehouse Maintenance</h3>
                <p>Select a warehouse to continue</p>
            </div>

            <!-- Body -->
            <div class="site-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                @endif

                <!-- Search Box -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari gudang..." autocomplete="off">
                </div>

                <!-- Site List -->
                <div class="site-list" id="siteList">
                    @if($sites->count() > 0)
                        @foreach($sites as $site)
                            <form action="{{ route('site.choose') }}" method="POST" class="site-form">
                                @csrf
                                <input type="hidden" name="site_code" value="{{ $site->code }}">
                                <div class="site-item" onclick="this.closest('form').submit();" data-name="{{ strtolower($site->name) }}" data-code="{{ strtolower($site->code) }}">
                                    <div class="site-item-content">
                                        <div class="site-item-name">{{ $site->name }}</div>
                                        <div class="site-item-code">
                                            @if($site->description)
                                                {{ $site->description }}
                                            @else
                                                {{ $site->code }}
                                            @endif
                                        </div>
                                    </div>
                                    <i class="fas fa-chevron-right site-item-arrow"></i>
                                </div>
                            </form>
                        @endforeach
                    @else
                        <div class="no-results">
                            <i class="fas fa-warehouse"></i>
                            <p>Tidak ada gudang tersedia</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="site-footer">
                &copy; {{ date('Y') }} Blibli | Warehouse Management System
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const siteItems = document.querySelectorAll('.site-form');
                let hasResults = false;

                siteItems.forEach(function(form) {
                    const item = form.querySelector('.site-item');
                    const name = item.getAttribute('data-name');
                    const code = item.getAttribute('data-code');

                    if (name.includes(searchTerm) || code.includes(searchTerm)) {
                        form.style.display = 'block';
                        hasResults = true;
                    } else {
                        form.style.display = 'none';
                    }
                });

                let noResults = document.querySelector('.no-results-search');
                if (!hasResults && searchTerm !== '') {
                    if (!noResults) {
                        noResults = document.createElement('div');
                        noResults.className = 'no-results no-results-search';
                        noResults.innerHTML = '<i class="fas fa-search"></i><p>No warehouse found</p>';
                        document.getElementById('siteList').appendChild(noResults);
                    }
                    noResults.style.display = 'block';
                } else if (noResults) {
                    noResults.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
