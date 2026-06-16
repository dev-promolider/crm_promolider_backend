<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    @include('panels/styles')
</head>
<body>
    {{-- {{$certificate->template}} --}}
    <div class="card">
        <div class="card m-2 border border-success" >
            <div style="position: absolute; top: 20px; left: 20px;" >
                <img
                    src="data:image/png;base64,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" />
            </div>
            <div class="text-center">
                <div class="col-12 mt-3">
                    <h3><strong>Promolíder</strong></h3>
                </div>
                <div class="col-12 mt-2">
                    <h1><strong>Certificado de Finalización</strong></h1>
                </div>
                <div class="col-12 mt-3">
                    <h3>Este certificado se presenta a</h3>
                </div>
                <div class="col-12 mt-2">
                    {{-- <h3><strong>@usuario</strong> --}}
                    <h3><strong>{{$usuario}}</strong>
                    </h3><hr style="background-color:black;height:1px; width: 50%;margin-left: auto;margin-right: auto;"  >
                </div>
                <div class="col-12 mt-5">
                    <div class="d-flex justify-content-center">
                        <div class="col-4">
                            {{-- <p><strong>@firma_administrador</strong></p> --}}
                            <p><strong>{!! $firma_administrador !!}</strong></p>
                            <hr style="background-color:black;height:1px;" >
                            {{-- <p><strong>@administrador</strong></p> --}}
                            <p><strong>{{$administrador}}</strong></p>
                            <p>Administrador de Promolíder</p>
                        </div>
                        <div class="col-4">
                            {{-- <p><strong>@firma_productor</strong></p> --}}
                            <p><strong>{!! $firma_productor !!}</strong></p>
                            <hr style="background-color:black;height:1px;" >
                            {{-- <p><strong>@productor</strong></p> --}}
                            <p><strong>{{$productor}}</strong></p>
                            <p>Productor de Promolíder</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-5 mb-2">
                    <div class="d-flex justify-content-center">
                        <div class="col-8">
                            {{-- <h3>Por completar satisfactoriamente el curso de <strong>@curso</strong></h3> --}}
                            <h3>Por completar satisfactoriamente el curso de <strong>{{$curso}}</strong></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="{{ asset(mix('js/app.js')) }}"></script>
</body>
</html>