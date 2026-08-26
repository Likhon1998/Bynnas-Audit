$ErrorActionPreference = 'Stop'
$src = 'C:\Users\ASUS\Downloads\MF Audit Report Format.doc'
$htmlOut = 'c:\xampp\htdocs\Bynnas-Audit\storage\app\templates\audit\MF_Audit_Report_Format_word.html'
$filteredOut = 'c:\xampp\htdocs\Bynnas-Audit\storage\app\templates\audit\MF_Audit_Report_Format_filtered.htm'

$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0
try {
  $doc = $word.Documents.Open($src, $false, $true)
  Write-Output ('PAGES=' + $doc.ComputeStatistics(2))
  # 8 = wdFormatHTML, 10 = wdFormatFilteredHTML
  $doc.SaveAs([ref]$htmlOut, [ref]8)
  $doc.SaveAs([ref]$filteredOut, [ref]10)
  $doc.Close($false)
  Write-Output 'HTML_EXPORT_OK'
} catch {
  Write-Output ('ERR=' + $_.Exception.Message)
} finally {
  $word.Quit()
  [GC]::Collect()
  [GC]::WaitForPendingFinalizers()
}
