<?php
    use App\Support\BanglaNumerals;
    $variant = $variant ?? 'default';
    $value = $value ?? '';
?>
<?php echo BanglaNumerals::markup($value, $variant); ?>

<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views\audits\partials\bn-num.blade.php ENDPATH**/ ?>