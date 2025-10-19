<?php
require_once __DIR__ . '/../includes/auth.php';
requireLogin();

$title = $title ?? 'Account Action';
$actionHeading = $actionHeading ?? $title;
$subtitle = $subtitle ?? '';
$sections = $sections ?? [];
$flash = $flash ?? null;

require __DIR__ . '/../partials/header.php';
?>
<!-- layout:content:start -->
<section class="account-action">
    <header class="page-header">
        <h1><?= e($actionHeading) ?></h1>
        <?php if ($subtitle !== ''): ?>
            <p class="subtitle"><?= e($subtitle) ?></p>
        <?php endif; ?>
        <p class="back-link"><a href="<?= e(siteUrl('dashboard.php')) ?>">&larr; Back to Account Dashboard</a></p>
    </header>
    <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
    <div class="action-panels">
        <?php foreach ($sections as $section): ?>
            <?php
            $sectionTitle = $section['title'] ?? '';
            $sectionSubtitle = $section['subtitle'] ?? '';
            $sectionBody = $section['body'] ?? '';
            $sectionItems = $section['items'] ?? [];
            $content = $section['content'] ?? '';
            $cta = $section['cta'] ?? null;
            ?>
            <article class="panel">
                <?php if ($sectionTitle || $sectionSubtitle || $cta): ?>
                    <header>
                        <div>
                            <?php if ($sectionTitle): ?>
                                <h2><?= e($sectionTitle) ?></h2>
                            <?php endif; ?>
                            <?php if ($sectionSubtitle): ?>
                                <p class="meta"><?= e($sectionSubtitle) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($cta): ?>
                            <a class="btn <?= e($cta['variant'] ?? 'secondary') ?>" href="<?= e($cta['href']) ?>"><?= e($cta['label'] ?? 'Learn more') ?></a>
                        <?php endif; ?>
                    </header>
                <?php endif; ?>
                <?php if ($sectionBody): ?>
                    <p><?= e($sectionBody) ?></p>
                <?php endif; ?>
                <?php if ($sectionItems): ?>
                    <ul class="checklist">
                        <?php foreach ($sectionItems as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <?php if ($content): ?>
                    <?= $content ?>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<!-- layout:content:end -->
<?php require __DIR__ . '/../partials/footer.php'; ?>
