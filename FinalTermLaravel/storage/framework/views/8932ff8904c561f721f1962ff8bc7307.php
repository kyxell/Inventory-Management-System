<?php ($isStockApp = request()->is('stock*')); ?>
<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#212529">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title <?php if (! ($isStockApp)): ?> inertia <?php endif; ?>><?php echo e($isStockApp ? 'Streetfood Stock Management' : config('app.name', 'Laravel')); ?></title>

        <?php if (! ($isStockApp)): ?>
            <link rel="preconnect" href="https://fonts.bunny.net">
            <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <?php else: ?>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
        <?php endif; ?>

        <?php echo app('Tighten\Ziggy\BladeRouteGenerator')->generate(); ?>
        <?php echo app('Illuminate\Foundation\Vite')->reactRefresh(); ?>
        <?php if($isStockApp): ?>
            <?php echo app('Illuminate\Foundation\Vite')('resources/js/app.jsx'); ?>
        <?php else: ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/js/inertia.jsx', "resources/js/Pages/{$page['component']}.jsx"]); ?>
            <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->head; } ?>
        <?php endif; ?>
    </head>
    <body <?php if (! ($isStockApp)): ?> class="font-sans antialiased" <?php endif; ?>>
        <?php if($isStockApp): ?>
            <div id="root"></div>
        <?php else: ?>
            <?php if (!isset($__inertiaSsrDispatched)) { $__inertiaSsrDispatched = true; $__inertiaSsrResponse = app(\Inertia\Ssr\Gateway::class)->dispatch($page); }  if ($__inertiaSsrResponse) { echo $__inertiaSsrResponse->body; } else { ?><div id="app" data-page="<?php echo e(json_encode($page)); ?>"></div><?php } ?>
        <?php endif; ?>
    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Inventory-Management-System\FinalTermLaravel\resources\views/app.blade.php ENDPATH**/ ?>