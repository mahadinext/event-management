<!DOCTYPE html>
<html>
<head>
    <title>500 - Internal Server Error</title>
</head>
<body>
    <h1>500 - Internal Server Error</h1>
    <p>An unexpected error occurred. Please try again later.</p>
    <?php if (isset($error)): ?>
        <p>Error details: <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</body>
</html>