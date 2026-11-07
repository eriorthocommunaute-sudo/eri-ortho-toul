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
    $sheet.Range('B3').Value2 = $title
    $sheet.Range('B4').Value2 = $docLine
    $sheet.Range('B5').Value2 = $subtitle

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
    $sheet.Range("F$openingBalanceRow").Value2 = 'Solde d''ouverture / መእተዊ ተረፍ'
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
        $sheet.Range("I$openingBalanceRow").Value2 = $openingBalance
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

        $sheet.Range("B$row").Value2 = $i + 1
        $sheet.Range("C$row").Value2 = [string]$entry.line_reference
        $sheet.Range("D$row").Value2 = [string]$entry.entry_date
        $sheet.Range("E$row").Value2 = [string]$entry.source_reference
        $sheet.Range("F$row").Value2 = [string]$entry.category_label
        if ($income -gt 0) { $sheet.Range("G$row").Value2 = $income }
        if ($expense -gt 0) { $sheet.Range("H$row").Value2 = $expense }
        $sheet.Range("I$row").Value2 = $runningBalance
        $notes = [string]$entry.payment_method
        if ([string]$entry.notes -ne '') { $notes += ' | ' + [string]$entry.notes }
        $sheet.Range("J$row").Value2 = $notes
    }

    if ($blankData) {
        $sheet.Range('G227:I227').ClearContents()
    } else {
        $sheet.Range('G227').Value2 = $totalIncome
        $sheet.Range('H227').Value2 = $totalExpense
        $sheet.Range('I227').Value2 = $closingBalance
    }

    # Category totals in the source-reference guide (income: G, expenses: H).
    $sourceRows = @{ 'COT' = 218; 'OFF' = 219; 'DON' = 220; 'EQP' = 221; 'TRP' = 222; 'PRE' = 223; 'EVT' = 224; 'EXC' = 225 }
    foreach ($code in $sourceRows.Keys) {
        $sheet.Range("C$($sourceRows[$code])").Value2 = $code + '-' + $registerYear + '-'
        $sourceRow = $sourceRows[$code]
        $totals = $sourceTotals.$code
        if ($blankData) {
            $sheet.Range("G$sourceRow:H$sourceRow").ClearContents()
        } elseif ($null -ne $totals) {
            if ([double]$totals.income -gt 0) { $sheet.Range("G$sourceRow").Value2 = [double]$totals.income }
            if ([double]$totals.expense -gt 0) { $sheet.Range("H$sourceRow").Value2 = [double]$totals.expense }
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
            $sheet.Range("B$row").Value2 = $i
            if ($i -gt $rowCount) {
                $sheet.Range("C$row").Value2 = $refPrefix + '-' + $i.ToString('00')
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
