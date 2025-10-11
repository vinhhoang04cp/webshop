@extends('layouts.app')

@section('title', 'Chỉnh sửa quyền User')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa quyền: {{ $user->name }}</h1>
        <a href="{{ route('dashboard.users.show', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Form chỉnh sửa quyền -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cập nhật Roles</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-group">
                            <label><strong>Chọn Roles cho User:</strong></label>
                            <div class="row">
                                @foreach($roles as $role)
                                <div class="col-md-6 mb-2">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" 
                                               class="custom-control-input" 
                                               id="role_{{ $role->role_id }}" 
                                               name="roles[]" 
                                               value="{{ $role->role_id }}"
                                               {{ $user->roles->contains('role_id', $role->role_id) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="role_{{ $role->role_id }}">
                                            <strong>{{ $role->role_display_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $role->role_name }}</small>
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật quyền
                            </button>
                            <a href="{{ route('dashboard.users.show', $user->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy bỏ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Thông tin hiện tại -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin User</h6>
                </div>
                <div class="card-body">
                    <p><strong>Tên:</strong> {{ $user->name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                    <p><strong>Ngày tạo:</strong> {{ $user->created_at->format('d/m/Y') }}</p>
                    
                    <hr>
                    
                    <h6><strong>Roles hiện tại:</strong></h6>
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                            <span class="badge badge-primary badge-pill mr-1 mb-1">
                                {{ $role->role_display_name }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted">Chưa có role nào</p>
                    @endif

                    <hr>

                    <h6><strong>Quyền hiện tại:</strong></h6>
                    @php
                        $permissions = $user->getAllPermissions();
                    @endphp
                    
                    @if(in_array('*', $permissions))
                        <span class="badge badge-danger">Tất cả quyền (Admin)</span>
                    @elseif(count($permissions) > 0)
                        @foreach($permissions as $permission)
                            <span class="badge badge-success badge-pill mr-1 mb-1" style="font-size: 10px;">
                                {{ $permission }}
                            </span>
                        @endforeach
                    @else
                        <p class="text-muted">Chưa có quyền nào</p>
                    @endif
                </div>
            </div>

            <!-- Gán role nhanh -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gán Role nhanh</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.users.assign-role', $user->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <select name="role_id" class="form-control">
                                <option value="">-- Chọn Role --</option>
                                @foreach($roles as $role)
                                    @if(!$user->roles->contains('role_id', $role->role_id))
                                    <option value="{{ $role->role_id }}">{{ $role->role_display_name }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm btn-block">
                            <i class="fas fa-plus"></i> Gán Role
                        </button>
                    </form>

                    @if($user->roles->count() > 0)
                    <hr>
                    <h6><strong>Gỡ Role:</strong></h6>
                    @foreach($user->roles as $role)
                        <form action="{{ route('dashboard.users.remove-role', [$user->id, $role->role_id]) }}" 
                              method="POST" 
                              style="display: inline-block;"
                              onsubmit="return confirm('Bạn có chắc chắn muốn gỡ role {{ $role->role_display_name }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm mb-1">
                                <i class="fas fa-times"></i> {{ $role->role_display_name }}
                            </button>
                        </form>
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection