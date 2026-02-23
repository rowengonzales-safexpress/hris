<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine }}</title>
  </head>
  <body>
    <h1>{{ $subjectLine }}</h1>
    <div>
      {{ $jobRequest->message ?? '' }}
    </div>
  </body>
</html>
