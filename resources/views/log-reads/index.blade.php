<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Reads</title>
    <link href="{{url('lib/bootstrap.min.css')}}" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">History</h1>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                @foreach($logReads as $logRead)
                    <tr>
                        <td>{{ $logRead->name }}</td>
                        <td>{{ $logRead->time }} Minutes</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <script src="{{url('lib/bootstrap.bundle.min.js')}}"></script>
</body>
</html>
