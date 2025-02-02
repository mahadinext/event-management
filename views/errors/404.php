<!DOCTYPE html>
<html>
<head>
    <title>404 - Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The requested page could not be found.</p>
    <?php if (isset($error)): ?>
        <p>Error details: <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</body>
</html>