# Clean WhatsApp Sessions Script for PowerShell
# Usage: .\cleanup-sessions.ps1

Write-Host "🧹 Cleaning WhatsApp Sessions..." -ForegroundColor Cyan
Write-Host "Sessions directory: ./sessions" -ForegroundColor Gray

$SESSIONS_DIR = "./sessions"

# Count sessions before cleanup
$BEFORE = @(Get-ChildItem -Path $SESSIONS_DIR -Directory -Exclude ".gitkeep" | Measure-Object).Count

# Remove all session folders
Get-ChildItem -Path $SESSIONS_DIR -Directory | 
    Where-Object { $_.Name -ne ".gitkeep" } | 
    Remove-Item -Recurse -Force -Confirm:$false

# Count sessions after cleanup
$AFTER = @(Get-ChildItem -Path $SESSIONS_DIR -Directory -Exclude ".gitkeep" | Measure-Object).Count

Write-Host "✅ Cleanup complete!" -ForegroundColor Green
Write-Host "   Removed sessions: $BEFORE" -ForegroundColor Yellow
Write-Host "   Sessions remaining: $AFTER" -ForegroundColor Yellow
Write-Host ""
Write-Host "📝 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Restart the backend server: npm start" -ForegroundColor Gray
Write-Host "   2. Refresh the browser: http://127.0.0.1:8000/whatsapp/settings" -ForegroundColor Gray
Write-Host "   3. Generate a fresh QR code" -ForegroundColor Gray
