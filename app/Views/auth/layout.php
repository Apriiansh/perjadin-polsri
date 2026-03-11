<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <?= $this->renderSection('pageStyles') ?>
</head>

<body class="min-h-screen bg-surface-50 text-slate-800 antialiased">
    <main>
        <?= $this->renderSection('main') ?>
    </main>
    <!-- Lucide Icons Core -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
    </script>
    <?= $this->renderSection('pageScripts') ?>
</body>

</html>