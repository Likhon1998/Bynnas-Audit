@if (! empty($logoDoc))
<!--[if gte vml 1]><v:shape id="audit_logo" o:spid="_x0000_i1025" type="#_x0000_t75"
 style="width:{{ $logoDoc['width_pt'] }}pt;height:{{ $logoDoc['height_pt'] }}pt">
 <v:imagedata src="cid:{{ $logoDoc['cid'] }}" o:title="Logo"/>
</v:shape><![endif]-->
<!--[if !vml]-->
<img src="cid:{{ $logoDoc['cid'] }}"
     width="{{ $logoDoc['width_px'] }}"
     height="{{ $logoDoc['height_px'] }}"
     style="width:{{ $logoDoc['width_pt'] }}pt;height:{{ $logoDoc['height_pt'] }}pt;display:block;border:0;"
     alt="Logo"
     class="logo-large">
<!--[endif]-->
@endif
