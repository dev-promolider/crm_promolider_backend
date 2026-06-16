@extends('layouts.contentLayoutMaster') {{-- Tu layout base --}}

@section('title', 'Editor de páginas')

@section('content')
    <div class="card">
        <div class="card-body">
            <template-gallery :user='@json(auth()->user())'></template-gallery>
        </div>
    </div>
@endsection