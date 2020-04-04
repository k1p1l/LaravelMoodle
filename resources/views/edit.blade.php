@extends('parent')
@section('main')
    <div class="jumbotron text-center">
        <div align="right">
            <a href="{{route('welcome.index')}}" class="btn btn-default">Back</a>
        </div>
        <form method="post" action="{{route('welcome.update', $itemEdit->id)}}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="form-group" align="left">
                <div class="form-group">
                    <label class="col-md-4 text-left">Title</label>
                    <div class="col-md-8">
                        <input type="text" name="title" class="form-control input-group-lg"
                               value="{{$itemEdit->title}}">
                    </div>
                </div>
                <br/>
                <br/>
                <div class="form-group">
                    <label class="col-md-4 text-left">Category</label>
                    <div class="col-md-8">
                        <select name="category_id">
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
                        <input type="file" name="image" value="{{$itemEdit->image}}">
                        <td><img src="{{ URL::to('/images/'.$itemEdit->image)}}" class="img-thumbnail" width="150px"/>
                        </td>
                        <input type="hidden" name="hidden_image" width="150px" value="{{$itemEdit->image}}"/>
                    </div>
                </div>
                <br/>
                <br/>
                <div class="form-group">
                    <label class="col-md-4 text-right">Description</label>
                    <div class="col-md-8">
                        <input type="text" name="description" class="form-control input-group-lg"
                               value="{{$itemEdit->description}}">
                    </div>
                </div>
                <br/>
                <br/>
                <div class="form-group text-center">
                    <input type="submit" name="add" class="btn btn-primary " value="Edit">
                </div>
            </div>
        </form>
    </div>

@endsection
