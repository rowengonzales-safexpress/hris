<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>(FRLS) - Fund Request and Liquidation System</title>
    <style>
      body { font-family: Arial, sans-serif; color: #111; background:#f5f7fb; }
      .container { max-width: 640px; margin: 24px auto; padding: 0; }
      .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; box-shadow:0 2px 6px rgba(0,0,0,0.06); overflow:hidden; }
      .brand { text-align:center; padding:24px 16px 12px; }
      .brand-title { font-size:16px; color:#6b7280; }
      .brand-app { font-size:20px; font-weight:700; color:#111827; margin-top:6px; }
      .divider { border-top:1px solid #e5e7eb; margin:0 16px; }
      .content { padding:16px; }
      .title { font-size:18px; font-weight:600; margin-bottom:8px; }
      .intro { margin-bottom:16px; color:#1f2937; }
      table { width: 100%; border-collapse: collapse; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
      th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
      th { width: 40%; font-size:12px; color:#6b7280; text-transform:uppercase; letter-spacing:0.02em; }
      td { font-size:14px; color:#111827; }
      .list-table { margin-top:12px; }
      .list-table th { width:auto; }
      .list-table thead th { background:#f3f4f6; color:#374151; }
      .summary { text-align:right; padding:12px; font-weight:700; }
      .actions { padding:16px; }
      .btn { display:inline-block; text-decoration:none; padding:10px 16px; border-radius:8px; color:#fff !important; font-weight:600; margin-right:8px; border:0; cursor:pointer; }
      .btn-review { background:#2563eb; }
      .btn-approve { background:#16a34a; }
      .btn-reject { background:#dc2626; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="card">
        <div class="brand">
          @if (!empty($emailRequest->logo_url))
            <img src="{{ $emailRequest->logo_url }}" alt="Logo" style="height:48px;margin-bottom:8px;" />
          @endif
          <div class="brand-title">(FRLS) - Fund Request and Liquidation System</div>
          <div class="brand-app">{{ $emailRequest->title ?? 'Fund Request' }}</div>
        </div>
        <div class="divider"></div>
        <div class="content">
          @if (!empty($emailRequest->recipient_name))
            <div class="intro">Hi {{ strtolower($emailRequest->recipient_name) }}!</div>
          @endif
          <div class="intro">{{ $emailRequest->intro ?? '' }}</div>
          <table>
            @if (!empty($emailRequest->frm_no))
              <tr><th>Request No</th><td>{{ $emailRequest->frm_no }}</td></tr>
            @endif
            @if (!empty($emailRequest->purpose))
              <tr><th>Purpose</th><td>{{ $emailRequest->purpose }}</td></tr>
            @endif
            @if (!empty($emailRequest->request_date))
              <tr><th>Request Date</th><td>{{ $emailRequest->request_date }}</td></tr>
            @endif
            @if (!empty($emailRequest->branch_name))
              <tr><th>Site</th><td>{{ $emailRequest->branch_name }}</td></tr>
            @endif
            @if (!empty($emailRequest->expectedliquidation_date))
              <tr><th>Expected Liquidation</th><td>{{ $emailRequest->expectedliquidation_date }}</td></tr>
            @endif
            @if (!empty($emailRequest->status_request))
              @php
            $statusMap = [
              'FA' => 'For Approved',
              'FD' => 'For Disbursement',
              'FL' => 'For Liquidation',
              'A'  => 'Approved',
              'C'  => 'Closed',
              'X'  => 'Canceled',
            ];
            $statusLabel = $statusMap[$emailRequest->status_request] ?? $emailRequest->status_request;
          @endphp
          <tr><th>Status</th><td>{{ $statusLabel }}</td></tr>
        @endif
        @if (!empty($emailRequest->amount))
          <tr><th>Amount</th><td>{{ number_format($emailRequest->amount, 2) }}</td></tr>
        @endif
        @if (!empty($emailRequest->filename))
          <tr><th>Filename</th><td>{{ $emailRequest->filename }}</td></tr>
        @endif
        @if (!empty($emailRequest->deleted_by))
          <tr><th>Deleted By</th><td>{{ $emailRequest->deleted_by }}</td></tr>
        @endif
          </table>
          @if (!empty($emailRequest->items) && is_array($emailRequest->items))
            <table class="list-table">
              <thead>
                <tr>
                  <th>Account Code</th>
                  <th>Frequency</th>
                  <th>Description</th>
                  <th>Qty</th>
                  <th>Unit Price</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($emailRequest->items as $it)
                  <tr>
                    <td>{{ $it['account_code_title'] ?? '' }}</td>
                    <td>{{ $it['frequency'] ?? '' }}</td>
                    <td>{{ $it['description'] ?? '' }}</td>
                    <td>{{ $it['qty'] ?? '' }}</td>
                    <td>{{ isset($it['unit_price']) ? number_format($it['unit_price'], 2) : '' }}</td>
                    <td>{{ isset($it['amount']) ? number_format($it['amount'], 2) : '' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            @if (!empty($emailRequest->total_amount))
              <div class="summary">Total Amount: ₱{{ number_format($emailRequest->total_amount, 2) }}</div>
            @endif
          @endif
        </div>
        @if (!empty($emailRequest->action_url) || !empty($emailRequest->approval_url) || !empty($emailRequest->rejection_url))
          <div class="actions">
            @if (!empty($emailRequest->approval_url))
              <a href="{{ url($emailRequest->approval_url) }}" target="_blank" class="btn btn-approve text-white">Approve</a>
            @endif
            @if (!empty($emailRequest->rejection_url))
              <a href="{{ url($emailRequest->rejection_url) }}" target="_blank" class="btn btn-reject text-white">Reject</a>
            @endif
            @if (!empty($emailRequest->action_url))
              <a href="{{ url($emailRequest->action_url) }}" target="_blank" class="btn btn-review text-white">Review Request</a>
            @endif
          </div>
        @endif
      </div>
    </div>
  </body>
</html>
