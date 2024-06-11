@extends('master')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h3>DANH SÁCH NHÂN VIÊN</h3>
</div>
@if(session('Error'))
    <div class="alert alert-danger d-flex align-items-center" role="alert">
        <div> 
              {{session('Error')}}
        </div>
    </div>
@endif
@if(session('Success'))
    <div class="alert alert-success d-flex align-items-center" role="alert">
        <div> 
              {{session('Success')}}
        </div>
    </div>
@endif
@if(isset($listAdmin) && $listAdmin->isNotEmpty($listAdmin))
<div class="table-responsive">
    <table class="table">
        <thead>
            <tr> 
                <th>Ảnh đại diện</td>
                <th>Họ tên</th>
                <th>Email</td>
                <th>Tên tài khoản</th>
                <th>Số điện thoại</td>
                <th>Địa chỉ</th>
                <th>Giới tính</th>
                <th>Quyền</th>
                <th>Trạng thái</th>
                <th>Tác vụ</th>
            </tr>
        </thead>
        @foreach($listAdmin as $Admin)
        <tr>
            <td><img src="{{asset($Admin->avatar_url)}}" class="avatar1" alt="avatar"/></td>
            <td>{{ $Admin->name }}</td>
            <td>{{ $Admin->email }}</td>
            <td>{{ $Admin->username }}</td>
            <td>{{ $Admin->phone }}</td>
            <td>{{ $Admin->address }}</td>
            <td>{{ $Admin->gender == 1 ? 'Nam' : 'Nữ'}}</td>
            <td>{{ $Admin->roles == 1 ? 'Quản lý' : ($Admin->roles == 2 ? 'Nhân viên' : ($Admin->roles == 3 ? 'Quản lý kho' : 'Không xác định')) }}</td>
            <td>{{ $Admin->status === 1 ? 'Hoạt động' : 'Không hoạt động' }}</td>
            <td>
                <a href="{{ route('admin.update', ['id' => $Admin->id]) }}">Sửa</a> | <a href="{{ route('admin.delete', ['id' => $Admin->id]) }}">Xóa</a>
            </td>
        <tr>
        @endforeach
    </table>
    {{ $listAdmin->links('vendor.pagination.default') }}
</div>
@else
<span class="error">Không có nhân viên nào!</span>
@endif
@endsection