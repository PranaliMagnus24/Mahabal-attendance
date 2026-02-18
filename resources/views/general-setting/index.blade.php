@extends('admin.layouts.layout')

@section('title', 'General Setting')
@section('admin')
@section('pagetitle', 'General Setting')

    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> General Setting</h4>
                    <div class="d-flex gap-2">
                        <a href="{{ route('general-setting.index') }}"
                            class="btn btn-secondary d-none d-sm-inline-block">Reset</a>
                    </div>
                </div>
            </div>

            <div class="card-body mt-3">
                <form action="{{ route('general-setting.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="website-name" class="form-label">Website Name</label>
                            <input type="text" class="form-control" name="website_name" value="{{ $getRecord->website_name }}">
                            @error('website_name')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" class="form-control">{{ $getRecord->description }}</textarea>
                            @error('description')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" name="email" value="{{ $getRecord->email }}">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" minlength="10" {{ $getRecord->phone }}>
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" value="{{ $getRecord->address }}">
                            @error('address')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="gstNumber" class="form-label">GST Number</label>
                            <input type="text" class="form-control" name="gst_number" value="{{ $getRecord->gst_number }}">
                            @error('gst_number')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="logo" class="form-label">Logo</label>
                            <input type="file" class="form-control" name="logo">
                             @if(!empty($getRecord->logo))
                        @if(file_exists('upload/general-setting/'.$getRecord->logo))<img src="{{url('upload/general-setting/'.$getRecord->logo)}}" style="height:100px; width:100px;">
                        @endif
                        @endif
                            @error('logo')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
