@extends('parent')
@section('main')
    <style>
        h2 {
            color: #5a6268
        }

        h3 {
            color: #38c172;
        }
    </style>
    <div class="jumbotron text-center">
        <div align="right">
            <a href="{{route('welcome.index')}}" class="btn btn-default btn-success">Back</a>
        </div>
        <br/>
        <h2>Title:</h2>
        <h3>{{$item->title}}</h3>
        <br/>
        <div class="border-info" align="center">
            <img src="{{ URL::to('/images/'.$item->image)}}" width="230px" class="img-thumbnail"/>
        </div>
        <h2>Description-<br/></h2>
        <h3>{{$item->description}}</h3>
        <h2>Name-</h2>
        <h3>{{$user->name}}</h3>
    </div>

@endsection
