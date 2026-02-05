<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corrective Maintenance Request - {{ config('app.name', 'Warehouse Maintenance') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/Blibli_Logo_Symbol_FC_RGB.png') }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.5.1/css/all.min.css">
    <style>
        body {
            background: url('{{ asset('assets/maxresdefault.jpg') }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            padding: 30px 0;
            position: relative;
        }
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
        .container {
            position: relative;
            z-index: 1;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .form-header {
            background: #0095DA;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .form-header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }
        .form-header p {
            margin: 10px 0 0 0;
            opacity: 0.9;
        }
        .form-body {
            padding: 30px;
        }
        .section-title {
            color: #2c3e50;
            font-weight: 600;
            border-bottom: 2px solid #0095DA;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .btn-submit {
            background: #0095DA;
            border: none;
            padding: 15px 40px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .btn-submit:hover {
            background: #007AB8;
        }
        .track-link {
            color: white;
            text-decoration: none;
        }
        .track-link:hover {
            color: #f1c40f;
        }
        /* Category Grid */
        .category-grid .col-6 {
            display: flex;
        }
        /* Category Card Styles */
        .category-card {
            border: none;
            border-radius: 16px;
            padding: 20px 15px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: center;
            width: 100%;
            min-height: 130px;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .category-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: #0095DA;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .category-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 149, 218, 0.25);
        }
        .category-card:hover::before {
            opacity: 1;
        }
        .category-card.selected {
            background: #0095DA;
            color: white;
            box-shadow: 0 8px 25px rgba(0, 149, 218, 0.4);
            transform: translateY(-4px);
        }
        .category-card.selected::before {
            opacity: 0;
        }
        .category-card input {
            display: none;
        }
        .category-card .icon-wrapper {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #E6F7FF 0%, #B3E0F7 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            transition: all 0.3s;
        }
        .category-card:hover .icon-wrapper {
            transform: scale(1.1);
        }
        .category-card.selected .icon-wrapper {
            background: rgba(255,255,255,0.25);
        }
        .category-card i {
            font-size: 1.6rem;
            color: #0095DA;
            transition: all 0.3s;
        }
        .category-card.selected i {
            color: white;
        }
        .category-card .category-name {
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.3px;
            margin-top: 8px;
            line-height: 1.3;
        }

        /* Conveyor Totebox - Blue theme */
        .category-card.conveyor-totebox::before {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }
        .category-card.conveyor-totebox .icon-wrapper {
            background: linear-gradient(135deg, #ebf5fb 0%, #d4e6f1 100%);
        }
        .category-card.conveyor-totebox i {
            color: #3498db;
        }
        .category-card.conveyor-totebox.selected {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
        }

        /* Conveyor Paket - Teal theme */
        .category-card.conveyor-paket::before {
            background: linear-gradient(135deg, #1abc9c, #16a085);
        }
        .category-card.conveyor-paket .icon-wrapper {
            background: linear-gradient(135deg, #e8f8f5 0%, #d1f2eb 100%);
        }
        .category-card.conveyor-paket i {
            color: #1abc9c;
        }
        .category-card.conveyor-paket.selected {
            background: linear-gradient(135deg, #1abc9c 0%, #16a085 100%);
            box-shadow: 0 8px 25px rgba(26, 188, 156, 0.4);
        }

        /* Lift Merah - Red theme */
        .category-card.lift-merah::before {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }
        .category-card.lift-merah .icon-wrapper {
            background: linear-gradient(135deg, #fdedec 0%, #fadbd8 100%);
        }
        .category-card.lift-merah i {
            color: #e74c3c;
        }
        .category-card.lift-merah:hover {
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.25);
        }
        .category-card.lift-merah.selected {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }

        /* Lift Kuning - Yellow/Orange theme */
        .category-card.lift-kuning::before {
            background: linear-gradient(135deg, #f39c12, #d68910);
        }
        .category-card.lift-kuning .icon-wrapper {
            background: linear-gradient(135deg, #fef9e7 0%, #fcf3cf 100%);
        }
        .category-card.lift-kuning i {
            color: #f39c12;
        }
        .category-card.lift-kuning:hover {
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.25);
        }
        .category-card.lift-kuning.selected {
            background: linear-gradient(135deg, #f39c12 0%, #d68910 100%);
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.4);
        }

        /* Chute - Purple theme */
        .category-card.chute::before {
            background: linear-gradient(135deg, #9b59b6, #8e44ad);
        }
        .category-card.chute .icon-wrapper {
            background: linear-gradient(135deg, #f5eef8 0%, #ebdef0 100%);
        }
        .category-card.chute i {
            color: #9b59b6;
        }
        .category-card.chute:hover {
            box-shadow: 0 8px 25px rgba(155, 89, 182, 0.25);
        }
        .category-card.chute.selected {
            background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);
            box-shadow: 0 8px 25px rgba(155, 89, 182, 0.4);
        }

        /* Others - Gray theme */
        .category-card.others::before {
            background: linear-gradient(135deg, #7f8c8d, #5d6d7e);
        }
        .category-card.others .icon-wrapper {
            background: linear-gradient(135deg, #f4f6f6 0%, #e5e8e8 100%);
        }
        .category-card.others i {
            color: #7f8c8d;
        }
        .category-card.others:hover {
            box-shadow: 0 8px 25px rgba(127, 140, 141, 0.25);
        }
        .category-card.others.selected {
            background: linear-gradient(135deg, #7f8c8d 0%, #5d6d7e 100%);
            box-shadow: 0 8px 25px rgba(127, 140, 141, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="form-header">
                        <h1><i class="fas fa-tools me-2"></i>Corrective Maintenance Request</h1>
                        <p>Submit a maintenance request for equipment issues</p>
                        <div class="mt-3">
                            <a href="{{ route('maintenance-request.track') }}" class="track-link">
                                <i class="fas fa-search me-1"></i> Track Your Ticket
                            </a>
                        </div>
                    </div>

                    <div class="form-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('maintenance-request.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            {{-- Requestor Information --}}
                            <h5 class="section-title"><i class="fas fa-user me-2"></i>Requestor Information</h5>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="requestor_name" class="form-control" value="{{ old('requestor_name') }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="requestor_email" class="form-control" value="{{ old('requestor_email') }}" required>
                                    <small class="text-muted">Ticket updates will be sent to this email</small>
                                </div>
                            </div>

                            {{-- Problem Category --}}
                            <h5 class="section-title"><i class="fas fa-tags me-2"></i>Problem Category <span class="text-danger">*</span></h5>
                            <div class="row mb-4 g-3 category-grid">
                                {{-- Conveyor Totebox --}}
                                <div class="col-6">
                                    <label class="category-card conveyor-totebox {{ old('problem_category') == 'conveyor_totebox' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="conveyor_totebox" {{ old('problem_category') == 'conveyor_totebox' ? 'checked' : '' }} required>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-box"></i>
                                        </div>
                                        <span class="category-name">Conveyor Totebox</span>
                                    </label>
                                </div>
                                {{-- Conveyor Paket --}}
                                <div class="col-6">
                                    <label class="category-card conveyor-paket {{ old('problem_category') == 'conveyor_paket' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="conveyor_paket" {{ old('problem_category') == 'conveyor_paket' ? 'checked' : '' }}>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-boxes-stacked"></i>
                                        </div>
                                        <span class="category-name">Conveyor Paket</span>
                                    </label>
                                </div>
                                {{-- Lift Merah --}}
                                <div class="col-6">
                                    <label class="category-card lift-merah {{ old('problem_category') == 'lift_merah' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="lift_merah" {{ old('problem_category') == 'lift_merah' ? 'checked' : '' }}>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-elevator"></i>
                                        </div>
                                        <span class="category-name">Lift Merah</span>
                                    </label>
                                </div>
                                {{-- Lift Kuning --}}
                                <div class="col-6">
                                    <label class="category-card lift-kuning {{ old('problem_category') == 'lift_kuning' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="lift_kuning" {{ old('problem_category') == 'lift_kuning' ? 'checked' : '' }}>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-elevator"></i>
                                        </div>
                                        <span class="category-name">Lift Kuning</span>
                                    </label>
                                </div>
                                {{-- Chute --}}
                                <div class="col-6">
                                    <label class="category-card chute {{ old('problem_category') == 'chute' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="chute" {{ old('problem_category') == 'chute' ? 'checked' : '' }}>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-arrow-down-wide-short"></i>
                                        </div>
                                        <span class="category-name">Chute</span>
                                    </label>
                                </div>
                                {{-- Others --}}
                                <div class="col-6">
                                    <label class="category-card others {{ old('problem_category') == 'others' ? 'selected' : '' }}" onclick="selectCategory(this)">
                                        <input type="radio" name="problem_category" value="others" {{ old('problem_category') == 'others' ? 'checked' : '' }}>
                                        <div class="icon-wrapper">
                                            <i class="fas fa-ellipsis-h"></i>
                                        </div>
                                        <span class="category-name">Others</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Problem Description --}}
                            <h5 class="section-title"><i class="fas fa-clipboard-list me-2"></i>Problem Details</h5>
                            <div class="mb-3">
                                <label class="form-label">Problem Description <span class="text-danger">*</span></label>
                                <textarea name="problem_description" class="form-control" rows="4" required placeholder="Please describe the issue in detail...">{{ old('problem_description') }}</textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Attachment (Photo/Document)</label>
                                <input type="file" name="attachment" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                                <small class="text-muted">Accepted formats: JPG, PNG, PDF. Max 5MB</small>
                            </div>

                            {{-- Submit --}}
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="text-center mt-4 text-white">
                    <small>&copy; {{ date('Y') }} {{ config('app.name', 'Warehouse Maintenance') }}</small>
                </div>
            </div>
        </div>
    </div>

    <script>
        function selectCategory(element) {
            // Remove selected from all cards
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });
            // Add selected to clicked card
            element.classList.add('selected');
            // Check the radio input
            element.querySelector('input').checked = true;
        }
    </script>
</body>
</html>
