<!-- Fonts -->
<link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">
<!-- Styles -->
<style>
    html, body {
        background-color: #fff;
        color: #636b6f;
        font-family: 'Nunito', sans-serif;
        font-weight: 200;
        height: 100vh;
        margin: 0;
    }

    .full-height {
        height: 100vh;
    }

    .flex-center {
        align-items: center;
        display: flex;
        justify-content: center;
    }

    .position-ref {
        position: relative;
    }

    .top-right {
        position: absolute;
        right: 10px;
        top: 18px;
    }

    .content {
        text-align: center;
    }

    .title {
        font-size: 84px;
    }

    .links > a {
        color: #636b6f;
        padding: 0 25px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: .1rem;
        text-decoration: none;
        text-transform: uppercase;
    }

    .m-b-md {
        margin-bottom: 30px;
    }
</style>

@extends('parent')
@section('main')
    <div align="right">
        <a href="{{url('/add')}}" class="btn btn-success">Add</a>
    </div>

    <form method="get" action="{{url('/welcome')}}">
        @csrf
        <select name="select_value">
            <option selected disabled> select</option>
            <option value="3">3</option>
            <option value="5">5</option>
            <option value="10">10</option>
        </select>
        <div align="left btn-primary">
            <input type="text" name="search">
            <input type="submit" name="add_search" class="btn btn-primary" value="search">
        </div>
    </form>


    @if ($message = Session::get('success'))
        <div class="alert-success">
            <p>{{$message}}</p>
        </div>
    @endif

    @if ($message = Session::get('exception'))
        <div class="alert-danger">
            <p>{{$message}}</p>
        </div>
    @endif

    <table class="table table-bordered table-striped table-info">
        <tr>
            @if (Auth::check() && Auth::user()->is_Admin)
                <th width="15%">Image</th>
                <th width="15%">Title</th>
                <th width="15%">Category</th>
                <th width="35%">Description</th>
                <th width="5%">Tools</th>
            @else
                <th width="15%">Image</th>
                <th width="10%">Title</th>
                <th width="10%">Category</th>
                <th width="20%">Description</th>
                <th width="5%">Tools</th>
            @endif
        </tr>

        @foreach($itemData as $row)
            <tr>
                <th><img src="{{ URL::to('/images/'.$row->image)}}" class="img-thumbnail" width="150px"></th>
                <td>{{$row->title}}</td>
                {{--                    {{dd($row->category_id)}}--}}
                @if($row->category_id == 1)
                    <td>First</td>
                @elseif($row->category_id == 2)
                    <td>Second</td>
                @else
                    <td>Third</td>
                @endif

                <td>{{$row->description}}</td>
                <td>
                    @if (Auth::check() && Auth::user()->is_Admin)
                        <a href="{{route('welcome.show', $row->id)}}" class="btn btn-primary">Show</a><br/>
                        <a href="{{route('welcome.edit', $row->id)}}" class="btn btn-warning">Edit</a>
                        <form action="{{route('welcome.destroy', $row->id)}}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                Delete
                            </button>
                        </form>
                    @else
                        <a href="{{route('welcome.show', $row->id)}}" class="btn btn-primary">Show</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    {!! $itemData->links() !!}
@endsection


