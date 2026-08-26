$ErrorActionPreference = 'Stop'
$src = 'c:\xampp\htdocs\Bynnas-Audit\storage\app\templates\audit\MF_Audit_Report_Format.doc'
$dst = 'c:\xampp\htdocs\Bynnas-Audit\storage\app\templates\audit\MF_Audit_Report_Format.docx'
$txt = 'c:\xampp\htdocs\Bynnas-Audit\storage\app\templates\audit\MF_Audit_Report_Format.txt'

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
try {
  $doc = $word.Documents.Open($src, $false, $true)
  # 16 = wdFormatDocumentDefault (.docx), 2 = wdFormatText
  $doc.SaveAs([ref]$dst, [ref]16)
  $doc.SaveAs([ref]$txt, [ref]2)
  $doc.Close($false)
  Write-Output 'WORD_CONVERT_OK'
} catch {
  Write-Output ('WORD_ERROR: ' + $_.Exception.Message)
} finally {
  $word.Quit()
  [GC]::Collect()
  [GC]::WaitForPendingFinalizers()
}
