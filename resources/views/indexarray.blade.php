@extends('layout.app')

@section('content')
    <header>
        <div class="container-fluid py-3" style="background: #fafafa">
            <div class="container">
                <div class="row">
                    <h1>Image Title Bulk Converter</h1>
                </div>
            </div>
        </div>
    </header>

    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-6">
                    <div class="inputs_row py-3 px-4 rounded border">
                        <form action="{{route('zipArray')}}" enctype="multipart/form-data" method="POST">
                            @csrf
                            <div class="form-row pb-2">
                                <div class="label-position" style="margin-top: -28px;">
                                    <label for="images[]" style="background: white; padding: 0px 12px; font-weight: 600;">Subir imágenes & Keywords</label> 
                                </div>
                                <input type="file" name="images[]" class="form-control mt-3" multiple>
                            </div>

                            <div class="form-row pb-2">
                                <label for="track-keyword">Keyword a cambiar</label>
                                <input type="text" name="track-keyword" id="track-keyword" class="form-control">
                            </div>

                            <div class="form-row pb-2">
                                <label for="new-keyword">Nuevas keywords</label>
                                {{-- <input type="text" name="new-keyword" id="new-keyword" class="form-control"> --}}
                                <textarea style="height: 140px;" name="new-keyword" id="new-keyword" cols="30" rows="10" placeholder="Escribe una keyword por línea" class="form-control"></textarea>
                                <small style="opacity: 0.7;">Nota: máx 15 keywords | ejemplo keyword compuesta: "varias-palabras"</small>
                            </div>
                            
                            <button type="submit" class="submit btn btn-primary">Comenzar Bulk</button>
                        </form>
                    </div>
                </div>

                <div class="col-sm-12 col-md-6">   

                </div>
            </div>
        </div>
    </main>
@endsection