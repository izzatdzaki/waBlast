# waBlast Docker Control Script for Windows PowerShell
# Usage: .\docker-control.ps1 [command]

param(
    [string]$Command = "help",
    [string]$Service = "",
    [Parameter(ValueFromRemainingArguments = $true)]
    [string[]]$Arguments
)

# Colors
$GREEN = "`e[32m"
$BLUE = "`e[34m"
$RED = "`e[31m"
$NC = "`e[0m"

function Show-Help {
    Write-Host @"
$BLUE`waBlast Docker Control Script$NC

Usage: .\docker-control.ps1 [command] [options]

Commands:
    up                  Start all containers
    down                Stop all containers
    restart             Restart all containers
    ps                  Show service status
    
    logs [service]      Show logs (default: all)
    bash [service]      Open bash shell (default: laravel)
    
    artisan [cmd ...]   Run artisan command
    migrate             Run database migrations
    seed                Run database seeders
    cache-clear         Clear all caches
    
    mysql               Connect to MySQL CLI
    
    build [service]     Build Docker image
    clean               Remove containers and volumes (⚠️ deletes data!)
    backup              Backup database
    stats               Show resource usage
    
    help                Show this help message

Examples:
    .\docker-control.ps1 up
    .\docker-control.ps1 logs laravel
    .\docker-control.ps1 artisan migrate:fresh
    .\docker-control.ps1 bash baileys

$GREEN`For more information, see DOCKER_SETUP.md$NC
"@
}

function Invoke-DockerCompose {
    param([string[]]$DockerArgs)
    & docker-compose @DockerArgs
    if ($LASTEXITCODE -ne 0) {
        Write-Host "$RED`Error running command$NC" -ForegroundColor Red
        exit 1
    }
}

switch ($Command) {
    "up" {
        Write-Host "$BLUE`📦 Starting waBlast Docker containers...$NC"
        Invoke-DockerCompose "up", "-d"
        Write-Host "$GREEN`✅ Services started!$NC"
        Write-Host ""
        Write-Host "Access points:"
        Write-Host "  🌐 Frontend:      http://localhost"
        Write-Host "  📧 Mailhog UI:    http://localhost:8025"
        Write-Host "  💬 Baileys API:   http://localhost:3000"
    }
    
    "down" {
        Write-Host "$BLUE`🛑 Stopping waBlast Docker containers...$NC"
        Invoke-DockerCompose "down"
        Write-Host "$GREEN`✅ Services stopped!$NC"
    }
    
    "restart" {
        Write-Host "$BLUE`🔄 Restarting waBlast Docker containers...$NC"
        Invoke-DockerCompose "restart"
        Write-Host "$GREEN`✅ Services restarted!$NC"
    }
    
    "ps" {
        Write-Host "$BLUE`📊 Service Status:$NC"
        Invoke-DockerCompose "ps"
    }
    
    "logs" {
        $svc = if ([string]::IsNullOrWhiteSpace($Service)) { "." } else { $Service }
        Write-Host "$BLUE`📋 Showing logs for $svc$NC"
        Invoke-DockerCompose "logs", "-f", $svc
    }
    
    "bash" {
        $svc = if ([string]::IsNullOrWhiteSpace($Service)) { "laravel" } else { $Service }
        Write-Host "$BLUE`🔧 Opening bash shell in $svc$NC"
        Invoke-DockerCompose "exec", $svc, "bash"
    }
    
    "mysql" {
        Write-Host "$BLUE`🐘 Connecting to MySQL...$NC"
        Invoke-DockerCompose "exec", "mysql", "mysql", "-u", "client", "-p"
    }
    
    "artisan" {
        if ($Arguments.Count -eq 0) {
            Write-Host "$RED`No artisan command provided$NC"
            exit 1
        }
        Write-Host "$BLUE`⚙️  Running artisan command$NC"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", $Arguments
    }
    
    "migrate" {
        Write-Host "$BLUE`📊 Running migrations...$NC"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", "migrate", "--force"
        Write-Host "$GREEN`✅ Migrations complete!$NC"
    }
    
    "seed" {
        Write-Host "$BLUE`🌱 Running seeders...$NC"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", "db:seed", "--force"
        Write-Host "$GREEN`✅ Seeding complete!$NC"
    }
    
    "cache-clear" {
        Write-Host "$BLUE`🧹 Clearing caches...$NC"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", "cache:clear"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", "config:clear"
        Invoke-DockerCompose "exec", "laravel", "php", "artisan", "view:clear"
        Write-Host "$GREEN`✅ Caches cleared!$NC"
    }
    
    "build" {
        $svc = if ([string]::IsNullOrWhiteSpace($Service)) { "." } else { $Service }
        Write-Host "$BLUE`🔨 Building $svc$NC"
        Invoke-DockerCompose "build", $svc
        Write-Host "$GREEN`✅ Build complete!$NC"
    }
    
    "clean" {
        Write-Host "$RED`⚠️  This will remove all Docker volumes and data!$NC"
        $response = Read-Host "Are you sure? (yes/no)"
        if ($response -eq "yes") {
            Invoke-DockerCompose "down", "-v"
            Write-Host "$GREEN`✅ Clean complete!$NC"
        } else {
            Write-Host "Cancelled."
        }
    }
    
    "backup" {
        $timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
        Write-Host "$BLUE`💾 Backing up database...$NC"
        $backupFile = "backup-$timestamp.sql"
        Invoke-DockerCompose "exec", "-T", "mysql", "mysqldump", "-u", "root", "-proot", "--all-databases" | Out-File -FilePath $backupFile
        Write-Host "$GREEN`✅ Backup saved: $backupFile$NC"
    }
    
    "stats" {
        Write-Host "$BLUE`📈 Resource Usage:$NC"
        & docker stats --no-stream
    }
    
    "help" {
        Show-Help
    }
    
    default {
        Write-Host "$RED`❌ Unknown command: $Command$NC"
        Write-Host "Run '.\docker-control.ps1 help' for available commands"
        exit 1
    }
}
