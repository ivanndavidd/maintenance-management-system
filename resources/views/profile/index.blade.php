@extends(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super-admin') ? 'layouts.admin' : 'layouts.user')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="mb-4">
        <h2><i class="fas fa-user"></i> My Profile</h2>
        <p class="text-muted">View and manage your profile information</p>
    </div>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <!-- User Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-id-card"></i> Profile Information
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">
                                    Employee ID
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       value="{{ $user->employee_id ?? '-' }}" 
                                       disabled>
                                <small class="text-muted">Cannot be changed</small>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Phone Number</label>
                                <input type="text" 
                                       name="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}" 
                                       placeholder="+62xxx">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" 
                                      rows="3" 
                                      class="form-control @error('address') is-invalid @enderror" 
                                      placeholder="Your address...">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Change Password Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-key"></i> Change Password
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.change-password') }}">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label">
                                Current Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   name="current_password" 
                                   class="form-control @error('current_password') is-invalid @enderror" 
                                   required>
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                Confirm New Password <span class="text-danger">*</span>
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   class="form-control" 
                                   required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Password Requirements:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Minimum 8 characters</li>
                                <li>Use a strong, unique password</li>
                                <li>Don't share your password with anyone</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-key"></i> Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- User Avatar Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="avatar-large mx-auto mb-3" 
                         style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 48px; font-weight: bold; color: white;">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    </div>
                    <h4 class="mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    @if($user->employee_id)
                        <span class="badge bg-primary">{{ $user->employee_id }}</span>
                    @endif
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle"></i> Active
                    </span>
                </div>
            </div>
            
            <!-- Statistics Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar"></i> My Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Total Tasks</strong>
                            <span class="badge bg-primary">{{ $stats['total_tasks'] }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Completed Tasks</strong>
                            <span class="badge bg-success">{{ $stats['completed_tasks'] }}</span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Completion Rate</strong>
                            <span class="badge bg-info">{{ $stats['completion_rate'] }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" 
                                 role="progressbar" 
                                 style="width: {{ $stats['completion_rate'] }}%"
                                 aria-valuenow="{{ $stats['completion_rate'] }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Total Reports</strong>
                            <span class="badge bg-secondary">{{ $stats['total_reports'] }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <strong>Approved Reports</strong>
                            <span class="badge bg-success">{{ $stats['approved_reports'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Info Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle"></i> Account Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Role:</strong>
                        <span class="float-end">
                            @foreach($user->roles as $role)
                                <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                            @endforeach
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Member Since:</strong>
                        <span class="float-end">{{ $stats['member_since'] }}</span>
                    </div>
                    <div class="mb-2">
                        <strong>Joined:</strong>
                        <span class="float-end">{{ $user->created_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <strong>Last Updated:</strong>
                        <span class="float-end">{{ $user->updated_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection