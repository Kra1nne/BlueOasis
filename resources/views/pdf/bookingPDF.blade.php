<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Receipt</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      font-size: 12px;
      margin: 0;
      padding: 0;
      background-color: #fff;
      color: #000;
    }
    .dashed {
      border-top: 1px dashed #000;
      margin: 10px 0;
    }
    .line {
      display: flex;
      justify-content: space-between;
      margin: 2px 0;
    }
    .line span {
      display: inline-block;
      vertical-align: middle; /* Ensures proper vertical alignment */
    }
    .line span:last-child {
      text-align: right;
      min-width: 100%;
    }
    .total {
      font-weight: bold;
    }
    h1, h2, h4 {
      text-align: center;
      margin: 5px 0;
    }
    .date {
      text-align: center;
      display: block;
      margin-bottom: 10px;
      font-size: 12px;
    }
    .details .label{
      display: inline-block;
      width: 70px;
      font-weight: bold;
      text-align: left;
    }
  </style>
</head>
<body>

  <div class="receipt">
    <h1>BLUE OASIS</h1>
    <div class="dashed"></div>
    <h2>RECEIPT</h2>
    <span class="date">{{ date('M. d, Y', strtotime($Details['date'])) }}</span>
    <div class="dashed"></div>

    <!-- Items -->
    <div class="details">
      <div><span class="label">Name:</span> {{ $Details['name'] }}</div>
      <div><span class="label">Price:</span> {{ $Details['price'] }}</div>
      <div><span class="label">Check-in:</span> {{ $Details['check_in'] }}</div>
      <div><span class="label">Check-out:</span> {{ $Details['check_out'] }}</div>
      <div><span class="label">Promo:</span> {{ $Details['promo'] }}</div>
      <div><span class="label">Status:</span> {{ $Details['status'] }}</div>
    </div>
    <div class="dashed"></div>
    <div><h4 >Food Items</h4></div>
    @foreach ($Foods as $item)
    <div class="line">
      <span>{{ $item['quantity'] }} x {{ $item['name'] }}</span>
      <span style="margin-top: -14px">{{ number_format($item['quantity'] * $item['price'], 2) }}</span>
    </div>
    @endforeach

    <div class="dashed"></div>

    <div><h4 >Pool</h4></div>
     @if ($Details['category'] === "room")
        <div>
          Free Pool
        </div>
     @else
         <div class="details">
            <div>4–10 yrs old: {{ $Details['children_count'] }} x 70 = {{ $Details['children_count'] * 70 }}</div>
            <div>11 yrs old and above: {{ $Details['adult_count'] }} x 100 = {{ $Details['adult_count'] * 100 }}</div>
          </div>
     @endif
    <div class="dashed"></div>
    <!-- Totals -->
    
    <div class="line total">
      <span>TOTAL AMOUNT</span>
      <span style="margin-top: -14px">{{ number_format($Details['total_amount'], 2) }}</span>
    </div>

    <div class="dashed"></div>
    <h4>THANK YOU</h4>
    <div class="dashed"></div>
  </div>

</body>
</html>
