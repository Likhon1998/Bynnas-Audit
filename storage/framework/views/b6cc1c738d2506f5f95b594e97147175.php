<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($logoDoc)): ?>
<!--[if gte vml 1]><v:shape id="audit_logo" o:spid="_x0000_i1025" type="#_x0000_t75"
 style="width:<?php echo e($logoDoc['width_pt']); ?>pt;height:<?php echo e($logoDoc['height_pt']); ?>pt">
 <v:imagedata src="cid:<?php echo e($logoDoc['cid']); ?>" o:title="Logo"/>
</v:shape><![endif]-->
<!--[if !vml]-->
<img src="cid:<?php echo e($logoDoc['cid']); ?>"
     width="<?php echo e($logoDoc['width_px']); ?>"
     height="<?php echo e($logoDoc['height_px']); ?>"
     style="width:<?php echo e($logoDoc['width_pt']); ?>pt;height:<?php echo e($logoDoc['height_pt']); ?>pt;display:block;border:0;"
     alt="Logo"
     class="logo-large">
<!--[endif]-->
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Bynnas-Audit\resources\views/audits/partials/logo-word.blade.php ENDPATH**/ ?>