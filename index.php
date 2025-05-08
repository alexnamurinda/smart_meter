<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notice</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: #f4f6f8;
      margin: 0;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .modal {
      background:  transparent;
      padding: 40px;
      border-radius: 15px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      text-align: center;
      max-width: 400px;
    }
    .icon {
      width: 60px;
      margin-bottom: 20px;
      animation: pulse 2s infinite;
    }
    h2 {
      color: #e76f51;
      margin-bottom: 10px;
    }
    p {
      color: #555;
      font-size: 16px;
    }
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.1); }
      100% { transform: scale(1); }
    }
  </style>
</head>
<body>

<div class="modal">
  <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="#e76f51" viewBox="0 0 24 24"><path d="M12 0C5.371 0 0 5.372 0 12c0 6.628 5.371 12 12 12s12-5.372 12-12c0-6.628-5.371-12-12-12zm1 17h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
  <h2>Temporarily Unavailable</h2>
  <p>This site is currently undergoing maintenance or administrative review. Kindly check back later.</p>
</div>

</body>
</html>
