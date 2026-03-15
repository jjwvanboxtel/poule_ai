<#
.SYNOPSIS
  Start a Playwright MCP server for local agent use.
.DESCRIPTION
  Uses `npx playwright-mcp serve` to run a Playwright MCP server and returns JSON with the started PID and port.
.PARAMETER Port
  TCP port to run the MCP server on (default 9222)
.EXAMPLE
  pwsh -File .\specify\scripts\powershell\start-playwright-mcp.ps1 -Port 9222
#>
param(
    [int]$Port = 9222
)

# Ensure npx is available
if (-not (Get-Command npx -ErrorAction SilentlyContinue)) {
    Write-Error "npx not found. Install Node.js / npm and ensure npx is on PATH."
    exit 1
}

# Start playwright-mcp using npx in a detached process so it survives the PowerShell session
$argList = @("playwright-mcp", "serve", "--port", "$Port")
$startInfo = @{FilePath = 'npx'; ArgumentList = $argList; WindowStyle = 'Hidden'; PassThru = $true}
$proc = Start-Process @startInfo

# Return JSON information for automation
$result = @{ started = $true; port = $Port; pid = $proc.Id }
$result | ConvertTo-Json -Depth 3
