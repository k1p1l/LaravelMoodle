@extends('parent')
@section('main')
    @if ($errors->any())
        <div class="alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{$error}}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div align="right">
        <a href="{{route('welcome.index')}}" class="btn-success">Home</a>
    </div>

    <div class="container table-borderless">
        <form method="post" action="{{route('welcome.store')}}" enctype="multipart/form-data">
            @csrf
            <div class="font-weight-normal">
                <label class="col-md-4 text-left">Title</label>
                <div class="col-md-8">
                    <input type="text" name="title" class="form-control input-group-lg">
                </div>
            </div>
            <br/>
            <br/>
            <div class="font-weight-normal">
                <label class="col-md-4 text-left">Category</label>
                <div class="col-md-8">
                    <select multiple name="category_id">
                        <option selected disabled>SELECT</option>
                        <?php foreach ($dataCategory = \App\Category::all() as $row):?>
                        <option value="{{$row->id}}">{{$row->title}}</option>
                        <?php endforeach;?>
                    </select>
                </div>
            </div>
            <br/>
            <br/>
            <div class="form-group">
                <label class="col-md-4 text-left">Image</label>
                <div class="col-md-8">
                    <input type="file" name="image">
                </div>
            </div>
            <br/>
            <br/>
            <div class="form-group">
                <label class="col-md-4 text-left">Description</label>
                <div class="col-md-8">
                    <input type="text" name="description" class="form-control input-group-lg">
                </div>
            </div>
            <br/>
            <br/>
            <div class="form-group text-center">
                <input type="submit" name="add" class="btn btn-primary " value="Add">
            </div>
        </form>
    </div>
@endsection
