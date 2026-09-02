<!DOCTYPE html>
<html xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
      xmlns="http://www.w3.org/TR/REC-html40"
      lang="bn">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="ProgId" content="Word.Document">
    <meta name="Generator" content="Bynnas Audit">
    <title>অভ্যন্তরীণ নিরীক্ষা প্রতিবেদন</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
        <o:OfficeDocumentSettings>
            <o:AllowPNG/>
            <o:PixelsPerInch>96</o:PixelsPerInch>
        </o:OfficeDocumentSettings>
    </xml>
    <![endif]-->
    <!--[if gte vml 1]>
    <v:shapetype id="_x0000_t75" coordsize="21600,21600" o:spt="75"
     o:preferrelative="t" path="m@4@5l@4@11@9@11@9@5xe" filled="f" stroked="f">
     <v:stroke joinstyle="miter"/>
     <v:formulas>
      <v:f eqn="if lineDrawn pixelLineWidth 0"/>
      <v:f eqn="sum @0 1 0"/>
      <v:f eqn="sum 0 0 @1"/>
      <v:f eqn="prod @2 1 2"/>
      <v:f eqn="prod @3 21600 pixelWidth"/>
      <v:f eqn="prod @3 21600 pixelHeight"/>
      <v:f eqn="sum @0 0 1"/>
      <v:f eqn="prod @6 1 2"/>
      <v:f eqn="prod @7 21600 pixelWidth"/>
      <v:f eqn="sum @8 21600 0"/>
      <v:f eqn="prod @7 21600 pixelHeight"/>
      <v:f eqn="sum @10 21600 0"/>
     </v:formulas>
     <v:path o:extrusionok="f" gradientshapeok="t" o:connecttype="rect"/>
     <o:lock v:ext="edit" aspectratio="t"/>
    </v:shapetype>
    <![endif]-->
    <style>
        @page {
            size: 21cm 29.7cm;
            margin: 15mm 20mm 15mm 20mm;
            mso-page-orientation: portrait;
        }

        body {
            font-family: 'Nirmala UI', 'Vrinda', 'Kalpurush', 'Segoe UI', Arial, sans-serif;
            font-size: 11pt;
            color: #111;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }

        img {
            border: 0;
            -ms-interpolation-mode: bicubic;
        }

        .logo-large {
            display: block;
            max-width: 62mm;
            max-height: 16mm;
        }

        .doc-cover {
            page-break-after: always;
            page-break-inside: avoid;
            mso-break-type: section-break;
        }

        .doc-flow { page-break-inside: auto; }
        .section-follow { page-break-before: auto; page-break-inside: auto; margin-top: 4mm; }
        .signatures-follow { margin-top: 6mm; page-break-before: auto; }
        .financial-follow { margin-top: 5mm; page-break-before: auto; }
        table { mso-table-overlap: never; border-collapse: collapse; }
        .sign-table { margin-top: 6mm; mso-table-overlap: never; }
        .sign-table p { margin: 0 0 2pt; mso-margin-top-alt: 0; mso-margin-bottom-alt: 2pt; }
        table.toc-table { mso-table-overlap: never; }
        td.rating-cell { padding: 0 !important; vertical-align: middle !important; }
        table.rating-box { mso-table-overlap: never; margin: 0; }
    </style>
    @include('audits.partials.document-styles', ['isPdf' => true, 'forDoc' => true])
</head>
<body lang="bn">
@php $dash = '………………'; @endphp

@include('audits.partials.report-body', ['forDoc' => true, 'logoDoc' => $logoDoc ?? null])

</body>
</html>
