@extends('app')

@section('content')
<div class="card">
    <div class="card-header text-center">
        <h3>One-Time Pad Encryption</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ url('/otp/process') }}">
            @csrf
            <div class="mb-3">
                <label for="message" class="form-label">Message:</label>
                <textarea class="form-control" id="message" name="message" rows="3" placeholder="Enter your message">{{ old('message') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="cipher" class="form-label">Cipher Text (for decryption):</label>
                <textarea class="form-control" id="cipher" name="cipher" rows="3" placeholder="Paste cipher text here">{{ old('cipher') }}</textarea>
            </div>
            <div class="mb-3">
                <label for="key" class="form-label">Key:</label>
                <textarea class="form-control" id="key" name="key" rows="3" placeholder="Paste key here">{{ old('key') }}</textarea>
            </div>
            <div class="text-center">
                <button type="submit" name="action" value="encrypt" class="btn btn-primary me-2">Encrypt</button>
                <button type="submit" name="action" value="decrypt" class="btn btn-success">Decrypt</button>
            </div>
        </form>
    </div>
    @if(isset($result))
        <div class="mt-3">
           
     
            @if (!isset($result['message']))
                <h5>Cipher Text: <span class="text-primary">{{ $result['cipher'] }}</span></h5>
                <h5>Key: <span class="text-success">{{ $result['key'] }}</span></h5>
            @else
                <h5>Message: <span class="text-primary">{{ $result['message'] }}</span></h5>

            @endif
        </div>
    @endif
</div>

@if(isset($chartData))
<div class="card mt-4">
    <div class="card-header text-center">
        <h3>Data from saveOtp</h3>
    </div>
    <div class="card-body">
        <div id="curve_chart" style="width: 900px; height: 700px; margin-left:20%"></div>
    </div>
</div>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
  google.charts.load('current', {'packages':['corechart']});
  google.charts.setOnLoadCallback(drawChart);

  function drawChart() {
    var data = google.visualization.arrayToDataTable([
      ['Time Encode', 'Encode'],
            @foreach($chartData as $data)
                ['{{ $data[0] }}', {{ $data[1] }}],
            @endforeach
    ]);

    var options = {
      title: 'Time Encode',
      curveType: 'function',
      legend: { position: 'bottom' }
    };

    var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));

    chart.draw(data, options);
  }
</script>
@endif

@endsection
