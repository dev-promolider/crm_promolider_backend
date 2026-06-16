@extends('layouts/contentLayoutMaster')

@section('title', 'Edit Class')
@section('content')
    <section id="default-breadcrumb">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('courses.modules.create', $module->course) }}">
                                        Course: {{ $module->course->title }}</a></li>
                                <li class="breadcrumb-item"><a
                                        href="{{ route('courses.modules.create', $module->course) }}">Module:
                                        {{ $module->name }} </a></li>
                                <li class="breadcrumb-item active" aria-current="page">Class: {{ $cla->name }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row match-height">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" aria-controls="home"
                                role="tab" aria-selected="true">Upload Video</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" aria-controls="profile"
                                role="tab" aria-selected="false">Add from Library</a>
                        </li>
                    </ul>


                    <div class="tab-content">
                        @if (isset($errors) && $errors->any())
                            <div role="alert" class="alert alert-danger">
                                <div class="alert-body">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        @if (session('success'))
                            <div role="alert" class="alert alert-success">
                                <div class="alert-body">
                                    <span><strong>Success </strong> {{ session('success') }}!</span>
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                        @endif
                        <div class="tab-pane active" id="home" aria-labelledby="home-tab" role="tabpanel">
                            @if (!empty($cla->video))
                                <video class="col-8" controls>
                                    <source src="{{ asset($cla->video->path) }}" type="video/mp4">
                                    Your browser does not support HTML video.
                                </video>
                                <form action="{{ route('modules.clas.delVideo', [$module, $cla]) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-block">Delete Video</button>
                                </form>
                            @else
                                <form action="{{ route('modules.clas.addVideo', [$module, $cla]) }}" method="post"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="input-group mb-3">

                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="inputGroupFile02"
                                                name="path">
                                            <label class="custom-file-label" for="inputGroupFile02"
                                                aria-describedby="inputGroupFileAddon02">Choose file</label>
                                        </div>
                                        <div class="input-group-append">
                                            <button class="input-group-text" id="inputGroupFileAddon02"
                                                type="submit">Upload</button>
                                        </div>

                                    </div>
                                </form>
                            @endif

                        </div>
                        <div class="tab-pane" id="profile" aria-labelledby="profile-tab" role="tabpanel">
                            <p>
                                Dragée jujubes caramels tootsie roll gummies gummies icing bonbon. Candy jujubes cake cotton
                                candy.
                                Jelly-o lollipop oat cake marshmallow fruitcake candy canes toffee. Jelly oat cake pudding
                                jelly beans
                                brownie lemon drops ice cream halvah muffin. Brownie candy tiramisu macaroon tootsie roll
                                danish.
                            </p>
                            <p>
                                Croissant pie cheesecake sweet roll. Gummi bears cotton candy tart jelly-o caramels apple
                                pie jelly
                                danish marshmallow. Icing caramels lollipop topping. Bear claw powder sesame snaps.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @endsection
