$baseUrl = "http://127.0.0.1:8000/api/showtimes"
$baseUrlSlash = "http://127.0.0.1:8000/api/showtimes/"

Write-Host "1. Testing NO SLASH: $baseUrl"
try {
    $res = Invoke-WebRequest -Uri "$baseUrl?movie_id=1&theater_id=1" -Method GET -UseBasicParsing
    Write-Host "Status: $($res.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "`n2. Testing WITH SLASH: $baseUrlSlash"
try {
    $res = Invoke-WebRequest -Uri "$baseUrlSlash?movie_id=1&theater_id=1" -Method GET -UseBasicParsing
    Write-Host "Status: $($res.StatusCode)" -ForegroundColor Green
} catch {
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
}
