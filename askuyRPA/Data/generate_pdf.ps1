param (
    [string]$JobId = "job_test_001",
    [string]$UserId = "u_001",
    [string]$WeakTopics = "Limit Aljabar, Limit Trigonometri",
    [int]$Tingkat = 2,
    [string]$QuestionsJsonPath = "",
    [string]$OutputPath = ""
)

$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$templatePath = Join-Path $scriptDir "Templates\soal_template.html"

if (-not $OutputPath) {
    $OutputDir = Join-Path $scriptDir "Output"
    if (-not (Test-Path $OutputDir)) { New-Item -ItemType Directory -Path $OutputDir | Out-Null }
    $OutputPath = Join-Path $OutputDir "paket_$JobId.pdf"
}

# 1. Baca soal dari file JSON jika diberikan, atau gunakan sample default
$questions = @()
if ($QuestionsJsonPath -and (Test-Path $QuestionsJsonPath)) {
    $rawJson = Get-Content -Path $QuestionsJsonPath -Raw -Encoding UTF8
    $questions = $rawJson | ConvertFrom-Json
} else {
    $questions = @(
        @{
            id = "MTK-LIM-001"
            sub_bab = "Limit Aljabar"
            soal = "Nilai dari lim x->2 (3x + 1) adalah ..."
            opsi = @{ a = "5"; b = "6"; c = "7"; d = "8"; e = "9" }
            jawaban = "C"
        },
        @{
            id = "MTK-LIM-002"
            sub_bab = "Limit Aljabar"
            soal = "Nilai dari lim x->3 (x^2 - 9)/(x - 3) adalah ..."
            opsi = @{ a = "0"; b = "3"; c = "6"; d = "9"; e = "12" }
            jawaban = "C"
        },
        @{
            id = "MTK-LIM-007"
            sub_bab = "Limit Trigonometri"
            soal = "Nilai dari lim x->0 (sin 3x)/x adalah ..."
            opsi = @{ a = "0"; b = "1"; c = "1/3"; d = "3"; e = "[infinity]" }
            jawaban = "D"
        }
    )
}

# 2. Render Questions HTML
$questionsHtml = ""
$answerKeysHtml = ""
$num = 1

foreach ($q in $questions) {
    $opsiA = if ($q.opsi.a) { $q.opsi.a } else { "" }
    $opsiB = if ($q.opsi.b) { $q.opsi.b } else { "" }
    $opsiC = if ($q.opsi.c) { $q.opsi.c } else { "" }
    $opsiD = if ($q.opsi.d) { $q.opsi.d } else { "" }
    $opsiE = if ($q.opsi.e) { $q.opsi.e } else { "" }

    $questionsHtml += @"
    <div class="question-card">
      <div class="question-header">
        <span class="question-num">SOAL $num</span>
        <span class="question-subtopic">$($q.sub_bab) [$($q.id)]</span>
      </div>
      <div class="question-text">$($q.soal)</div>
      <div class="options-grid">
        <div class="option-item"><span class="option-key">A.</span> $opsiA</div>
        <div class="option-item"><span class="option-key">B.</span> $opsiB</div>
        <div class="option-item"><span class="option-key">C.</span> $opsiC</div>
        <div class="option-item"><span class="option-key">D.</span> $opsiD</div>
        $(if ($opsiE) { "<div class='option-item'><span class='option-key'>E.</span> $opsiE</div>" })
      </div>
    </div>
"@

    $answerKeysHtml += "<div class='key-item'>$num. $($q.jawaban)</div>"
    $num++
}

# 3. Baca dan isi Template HTML
$currentDate = (Get-Date).ToString("dd MMMM yyyy HH:mm")
$htmlContent = Get-Content -Path $templatePath -Raw -Encoding UTF8
$htmlContent = $htmlContent.Replace("{{JOB_ID}}", $JobId)
$htmlContent = $htmlContent.Replace("{{USER_ID}}", $UserId)
$htmlContent = $htmlContent.Replace("{{WEAK_TOPICS}}", $WeakTopics)
$htmlContent = $htmlContent.Replace("{{TINGKAT}}", $Tingkat.ToString())
$htmlContent = $htmlContent.Replace("{{GENERATED_DATE}}", $currentDate)
$htmlContent = $htmlContent.Replace("{{QUESTIONS_HTML}}", $questionsHtml)
$htmlContent = $htmlContent.Replace("{{ANSWER_KEYS_HTML}}", $answerKeysHtml)

$tempHtmlPath = Join-Path $scriptDir "temp_$JobId.html"
[System.IO.File]::WriteAllText($tempHtmlPath, $htmlContent, [System.Text.Encoding]::UTF8)

# 4. Generate PDF menggunakan Microsoft Edge Headless (Bawaan Windows)
$edgePath = "C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"
if (-not (Test-Path $edgePath)) {
    $edgePath = "C:\Program Files\Microsoft\Edge\Application\msedge.exe"
}

if (Test-Path $edgePath) {
    Write-Host "[asKuy RPA] Generating PDF with Edge Headless..."
    Start-Process -FilePath $edgePath -ArgumentList "--headless", "--disable-gpu", "--no-pdf-header-footer", "--print-to-pdf=`"$OutputPath`"", "`"$tempHtmlPath`"" -Wait
    Remove-Item -Path $tempHtmlPath -Force -ErrorAction SilentlyContinue
    Write-Host "[asKuy RPA] PDF berhasil dibuat di: $OutputPath" -ForegroundColor Green
} else {
    Write-Warning "Microsoft Edge tidak ditemukan. File HTML disimpan di: $tempHtmlPath"
}
