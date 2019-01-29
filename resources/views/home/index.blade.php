<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>TE305 Table Generate Tools</title>
    <!-- Latest compiled and minified CSS & JS -->
    <link rel="stylesheet" media="screen" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

</head>
<body>


<div class="container">
    <h1>Mapping Main-Sub</h1>
    <hr>
    <form method="post" action="{{ route('main.post') }}">
        {{ csrf_field() }}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if(Session::has('alert-success'))
            <p class="alert alert-success">{{ Session::get('alert-success') }}</p>
        @endif
        @if(Session::has('alert-danger'))
            <p class="alert alert-danger">{{ Session::get('alert-danger') }}</p>
        @endif
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-3">MANDT</div>
                    <div class="col-md-3"><input name="mandt" type="number" class="form-control" value="110"></div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">HVORG</div>
                    <div class="col-md-3"><input name="hvorg" type="number" class="form-control" value="0300"></div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">New TVORG (Even)</div>
                    <div class="col-md-3"><input name="num" type="number" class="form-control"></div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-3">Entries Table from Excel</div>
                    <div class="col-md-9"><textarea id="entries" name="entries" type="text" height="5" class="form-control"></textarea></div>
                </div>
                <br>
                Download :
                <a class="btn btn-sm btn-primary" href="{{ asset('TE305.txt') }}">TE305</a>
                <a class="btn btn-sm btn-success" href="{{ asset('TE305T.txt') }}">TE305T</a>
                <a class="btn btn-sm btn-warning" href="{{ asset('TFKTVO.txt') }}">TFKTVO</a>
                <a class="btn btn-sm btn-danger" href="{{ asset('TFKTVOT.txt') }}">TFKTVOT</a>
                <hr>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </form>
</div>


<script
    src="https://code.jquery.com/jquery-2.2.4.min.js"
    integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
    crossorigin="anonymous"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdn.ckeditor.com/ckeditor5/11.2.0/classic/ckeditor.js"></script>

<script>
    ClassicEditor
        .create( document.querySelector( '#entries' ), {
            toolbar: [],
            heading: {
                options: [
                    { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                    { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                    { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' }
                ]
            }
        } )
        .catch( error => {
            console.log( error );
        } );
</script>
</body>
</html>
