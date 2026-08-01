# Run Laravel tests using a detected PHP executable.
# Usage: powershell -ExecutionPolicy Bypass -File .\backend\scripts\run-tests.ps1

Param(
    [string]$PhpPath
)

function Try-Run {
    param($php)
    if (-Not (Test-Path $php)) { return $false }
    Write-Host "Using PHP: $php"
    Push-Location "$(Split-Path -Path $PSScriptRoot -Parent)" # project backend folder
    try {
        & "$php" artisan test --verbose
        $rc = $LASTEXITCODE
    } finally {
        Pop-Location
    }
    return $rc -eq 0
}

# If provided explicitly
if ($PhpPath) {
    if (Try-Run $PhpPath) { exit 0 } else { Write-Error "Tests failed or php not found at $PhpPath"; exit 1 }
}

# 1) try php on PATH
$cmd = Get-Command php -ErrorAction SilentlyContinue
if ($cmd) {
    if (Try-Run $cmd.Source) { exit 0 }
}

# 2) common Laragon paths
$laragonCandidates = @(
    "$env:USERPROFILE\\laragon\\bin\\php\\php-8.2.0\\php.exe",
    "$env:USERPROFILE\\laragon\\bin\\php\\php-8.1.0\\php.exe",
    "C:\\laragon\\bin\\php\\php-8.2.10\\php.exe",
    "C:\\laragon\\bin\\php\\php-8.1.18\\php.exe",
    "C:\\laragon\\bin\\php\\php-8.0.0\\php.exe"
)

foreach ($p in $laragonCandidates) {
    if (Try-Run $p) { exit 0 }
}

# 3) try Program Files
$progCandidates = @("C:\\Program Files\\php\\php.exe", "C:\\Program Files (x86)\\php\\php.exe")
foreach ($p in $progCandidates) {
    if (Try-Run $p) { exit 0 }
}

Write-Host "php.exe not found. Please provide the path to php.exe as argument to the script. Example:" -ForegroundColor Yellow
Write-Host "powershell -ExecutionPolicy Bypass -File .\\backend\\scripts\\run-tests.ps1 -PhpPath 'C:\\laragon\\bin\\php\\php-8.2.10\\php.exe'"
exit 2
