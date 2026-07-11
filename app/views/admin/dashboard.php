<?php
declare(strict_types=1);

/** @var list<array{label:string,n:int,href:string,urgent?:bool}> $cards */
?>
<h1>Tableau de bord</h1>
<ul class="cards">
    <?php foreach ($cards as $c) : ?>
        <li class="card stat<?= !empty($c['urgent']) && $c['n'] > 0 ? ' stat-urgent' : '' ?>">
            <a href="<?= e($c['href']) ?>">
                <span class="stat-n"><?= (int) $c['n'] ?></span>
                <span class="stat-label"><?= e($c['label']) ?></span>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
<?php if (!$cards) : ?><p>Utilisez le menu pour accéder à vos contenus.</p><?php endif; ?>
