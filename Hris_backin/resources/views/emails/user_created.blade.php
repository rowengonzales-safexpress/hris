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
      .content { padding:24px 16px; }
      .intro { margin-bottom:16px; color:#1f2937; font-size: 16px; }
      .details { margin-top: 20px; margin-bottom: 20px; }
      .detail-item { margin-bottom: 10px; font-size: 14px; }
      .detail-label { font-weight: bold; width: 80px; display: inline-block; }
      .actions { padding:16px; }
      .btn { display:inline-block; text-decoration:none; padding:10px 16px; border-radius:8px; color:#fff !important; font-weight:600; margin-right:8px; border:0; cursor:pointer; background-color: #2563eb; }
      .btn:hover { background-color: #1d4ed8; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="card">
        <div class="brand">
          <div class="brand-title">(FRLS) - Fund Request and Liquidation System</div>
          <div class="brand-app">Account Created</div>
        </div>
        <div class="divider"></div>
        <div class="content">
          <div class="intro">
            Welcome, {{ $user->first_name }} {{ $user->last_name }}!
          </div>
          <div class="intro">
            Your account has been successfully created in the Finance System.
          </div>
          <div class="intro">
            Here are your login details:
          </div>
          <div class="details">
            <div class="detail-item">
              <span class="detail-label">Email:</span> {{ $user->email }}
            </div>
            <div class="detail-item">
              <span class="detail-label">Password:</span> {{ $password }}
            </div>
          </div>
          <div class="intro">
            Thank you.
          </div>
        </div>
        <div class="actions">
            <a href="{{ route('login') }}" target="_blank" class="btn">Visit the Page to login</a>
        </div>
      </div>
    </div>
  </body>
</html>
