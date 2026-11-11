<#
    Exports the populated bilingual "Registre financier" workbook to PDF with Excel COM.

    The script works on a temporary copy of the association's official workbook.
    It updates the period header, fills the real register entries, computes the
    running balance, writes source-category subtotals and sets the final totals.
    The source workbook remains unchanged.

    Text and financial rows come from a UTF-8 JSON payload (-ValuesPath), which
    preserves Tigrigna text and decimal amounts.
#>
param(
    [Parameter(Mandatory = $true)][string]$TemplatePath,
    [Parameter(Mandatory = $true)][string]$OutputPath,
    [Parameter(Mandatory = $true)][string]$ValuesPath,
    [string]$SheetName = 'Registre financier',
    [int]$VisibleRows = 0,
    [int]$DataStartRow = 8,
    [int]$DataEndRow = 215,
    [int]$DataRowCount = 200,
    [string]$RefPrefix = ''
)

# Excel's ExportAsFixedFormat rejects forward-slash paths (Laravel's
# storage_path() emits them on Windows); normalize for all file operations.
$TemplatePath = $TemplatePath.Replace('/', '\')
$OutputPath = $OutputPath.Replace('/', '\')
$ValuesPath = $ValuesPath.Replace('/', '\')

# PowerShell's COM adapter caches Value2's setter type after the first
# variable-sourced assignment, breaking later numeric writes. InvokeMember
# performs a plain IDispatch put and accepts any value type.
function Set-CellValue([object]$Target, [object]$Value) {
    $Target.GetType().InvokeMember('Value2', 'SetProperty', $null, $Target, @($Value)) | Out-Null
}

$ErrorActionPreference = 'Stop'
$excel = $null
$book  = $null

try {
    if (-not (Test-Path -LiteralPath $TemplatePath)) { throw "Template introuvable: $TemplatePath" }
    if (-not (Test-Path -LiteralPath $ValuesPath))   { throw "Valeurs introuvables: $ValuesPath" }

    $values = Get-Content -LiteralPath $ValuesPath -Raw -Encoding UTF8 | ConvertFrom-Json
    $title    = [string]$values.Title
    $subtitle = [string]$values.Subtitle
    $docLine  = [string]$values.DocLine
    $refPrefix = [string]$values.RefPrefix

    # Work on a throw-away copy so the shipped template is never modified.
    $workDir = Join-Path ([System.IO.Path]::GetTempPath()) ('regf_' + [guid]::NewGuid().ToString('N'))
    New-Item -ItemType Directory -Path $workDir -Force | Out-Null
    $workFile = Join-Path $workDir 'registre.xlsx'
    Copy-Item -LiteralPath $TemplatePath -Destination $workFile -Force

    $excel = New-Object -ComObject Excel.Application
    $excel.Visible = $false
    $excel.DisplayAlerts = $false
    $excel.ScreenUpdating = $false
    $excel.AskToUpdateLinks = $false

    $book = $excel.Workbooks.Open($workFile, 0, $false)
    $sheet = $book.Worksheets.Item($SheetName)

    # --- Header updates -------------------------------------------------------
    # The template already carries the bilingual association name on rows 1-2
    # (French then Tigrigna). Only the period-aware lines are refreshed:
    #   Row 3 : document title
    #   Row 4 : document identification line (carries the RF reference)
    #   Row 5 : description line (carries the period)
    Set-CellValue $sheet.Range('B3') $title
    Set-CellValue $sheet.Range('B4') $docLine
    Set-CellValue $sheet.Range('B5') $subtitle

    # --- Populate the register rows ----------------------------------------
    $entries = @($values.Entries)
    $openingBalance = [double]$values.OpeningBalance
    $totalIncome = [double]$values.TotalIncome
    $totalExpense = [double]$values.TotalExpense
    $closingBalance = [double]$values.ClosingBalance
    $sourceTotals = $values.SourceTotals
    $registerYear = [int]$values.Year
    $blankData = [bool]$values.BlankData

    # Clear the old template values while preserving its formatting.
    $sheet.Range("D$DataStartRow`:J$DataEndRow").ClearContents()
    $openingBalanceRow = $DataStartRow - 2
    $sheet.Range("F$openingBalanceRow`:H$openingBalanceRow").UnMerge()
    $sheet.Range("F$openingBalanceRow`:H$openingBalanceRow").Merge()
    Set-CellValue $sheet.Range("F$openingBalanceRow") 'Solde d''ouverture / መእተዊ ተረፍ'
    $sheet.Range("F$openingBalanceRow").HorizontalAlignment = -4152
    $sheet.Range("F$openingBalanceRow:I$openingBalanceRow").Font.Bold = $true
    $sheet.Range("I$openingBalanceRow").NumberFormat = $sheet.Range("I$DataStartRow").NumberFormat
    $sheet.Range("I$openingBalanceRow").HorizontalAlignment = -4108
    $noteLine = $sheet.Range("J$openingBalanceRow").Borders.Item(9)
    $noteLine.LineStyle = 1
    $noteLine.Weight = 2
    $noteLine.Color = 0x00C0D0D8
    if ($blankData) {
        $sheet.Range("I$openingBalanceRow").ClearContents()
    } else {
        Set-CellValue $sheet.Range("I$openingBalanceRow") $openingBalance
    }

    $runningBalance = $openingBalance
    $rowCount = [Math]::Min($entries.Count, $DataRowCount)
    for ($i = 0; $i -lt $rowCount; $i++) {
        $entry = $entries[$i]
        $row = $DataStartRow + $i
        $income = 0.0
        $expense = 0.0
        if ([string]$entry.type -eq 'income') { $income = [double]$entry.amount }
        else { $expense = [double]$entry.amount }
        $runningBalance += ($income - $expense)

        Set-CellValue $sheet.Range("B$row") ($i + 1)
        Set-CellValue $sheet.Range("C$row") ([string]$entry.line_reference)
        Set-CellValue $sheet.Range("D$row") ([string]$entry.entry_date)
        Set-CellValue $sheet.Range("E$row") ([string]$entry.source_reference)
        Set-CellValue $sheet.Range("F$row") ([string]$entry.category_label)
        if ($income -gt 0) { Set-CellValue $sheet.Range("G$row") $income }
        if ($expense -gt 0) { Set-CellValue $sheet.Range("H$row") $expense }
        Set-CellValue $sheet.Range("I$row") $runningBalance
        $notes = [string]$entry.payment_method
        if ([string]$entry.notes -ne '') { $notes += ' | ' + [string]$entry.notes }
        Set-CellValue $sheet.Range("J$row") $notes
    }

    if ($blankData) {
        $sheet.Range('G227:I227').ClearContents()
    } else {
        Set-CellValue $sheet.Range('G227') $totalIncome
        Set-CellValue $sheet.Range('H227') $totalExpense
        Set-CellValue $sheet.Range('I227') $closingBalance
    }

    # Category totals in the source-reference guide (income: G, expenses: H).
    $sourceRows = @{ 'COT' = 218; 'OFF' = 219; 'DON' = 220; 'EQP' = 221; 'TRP' = 222; 'PRE' = 223; 'EVT' = 224; 'EXC' = 225 }
    foreach ($code in $sourceRows.Keys) {
        Set-CellValue $sheet.Range("C$($sourceRows[$code])") ($code + '-' + $registerYear + '-')
        $sourceRow = $sourceRows[$code]
        $totals = $sourceTotals.$code
        if ($blankData) {
            $sheet.Range("G$sourceRow:H$sourceRow").ClearContents()
        } elseif ($null -ne $totals) {
            if ([double]$totals.income -gt 0) { Set-CellValue $sheet.Range("G$sourceRow") ([double]$totals.income) }
            if ([double]$totals.expense -gt 0) { Set-CellValue $sheet.Range("H$sourceRow") ([double]$totals.expense) }
        }
    }

    # --- Per-period RF references on the entry lines ------------------------
    # The "Référence RF" column is numbered inside the register itself:
    #   RF-2026-10-01, RF-2026-10-02, ...        (monthly)
    #   RF-2026-10-11-12-01, RF-2026-10-11-12-02, ...  (quarterly)
    if ($refPrefix -ne '') {
        $rfCount = if ($VisibleRows -gt 0) { $VisibleRows } else { $DataRowCount }
        for ($i = 1; $i -le $rfCount; $i++) {
            $row = $DataStartRow + $i - 1
            Set-CellValue $sheet.Range("B$row") $i
            if ($i -gt $rowCount) {
                Set-CellValue $sheet.Range("C$row") ($refPrefix + '-' + $i.ToString('00'))
            }
        }
    }

    # --- Keep only the requested number of entry lines ----------------------
    # Rows are hidden rather than deleted, so the template styling and source guide stay intact.
    # The trailing spacer rows (207-214) are collapsed as well, otherwise the
    # totals row would be pushed onto a second, almost empty page.
    if ($VisibleRows -gt 0) {
        $hideStart = $DataStartRow + $VisibleRows
        if ($hideStart -le $DataEndRow) {
            $sheet.Rows("$hideStart`:$DataEndRow").Hidden = $true
        }
    }

    $outDir = Split-Path -Parent $OutputPath
    if (-not (Test-Path -LiteralPath $outDir)) {
        New-Item -ItemType Directory -Path $outDir -Force | Out-Null
    }
    if (Test-Path -LiteralPath $OutputPath) {
        Remove-Item -LiteralPath $OutputPath -Force
    }

    # xlTypePDF = 0, xlQualityStandard = 0
    $sheet.ExportAsFixedFormat(0, $OutputPath, 0, $true, $false)

    $book.Close($false)
    $book = $null
    $excel.Quit()
    $excel = $null

    Remove-Item -Recurse -Force $workDir -ErrorAction SilentlyContinue

    if (-not (Test-Path -LiteralPath $OutputPath)) { throw 'Export PDF echoue' }

    Write-Output 'OK'
    exit 0
}
catch {
    Write-Output ('ERREUR: ' + $_.Exception.Message)
    exit 1
}
finally {
    if ($book  -ne $null) { try { $book.Close($false) } catch { } }
    if ($excel -ne $null) { try { $excel.Quit() } catch { } }
    [GC]::Collect()
    [GC]::WaitForPendingFinalizers()
}
