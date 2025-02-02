<!DOCTYPE html>
<html>
<head>
    <title>403 - Forbidden</title>
</head>
<body>
    <h1>403 - Forbidden</h1>
    <p>You are not authorized to access this page.</p>
    <?php if (isset($error)): ?>
        <p>Error details: <?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
</body>
</html>