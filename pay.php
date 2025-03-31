<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Load Units</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; }
        form { display: inline-block; text-align: left; padding: 20px; border: 1px solid #ddd; border-radius: 10px; }
        input, button { width: 90%; padding: 10px; margin-top: 10px; }
        button { background: green; color: white; border: none; cursor: pointer; margin-left: 10px; }
        button:hover { background: darkgreen; }
    </style>
</head>
<body>
    <h2>Load Energy Units</h2>
    <form action="pay2.php" method="POST">
        <label>Room ID:</label>
        <input type="text" name="room_id" required>
        
        <label>Units:</label>
        <input type="number" name="units" step="0.01" required>

        <button type="submit">Load Units</button>
    </form>
</body>
</html>
