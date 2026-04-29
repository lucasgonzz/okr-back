$ErrorActionPreference = 'Stop'
$base = 'http://127.0.0.1:8001/api/v1'

function PostJson($url, $headers, $obj) {
    Invoke-RestMethod -Uri $url -Method POST -Headers $headers -ContentType 'application/json' -Body ($obj | ConvertTo-Json -Depth 8)
}
function PutJson($url, $headers, $obj) {
    Invoke-RestMethod -Uri $url -Method PUT -Headers $headers -ContentType 'application/json' -Body ($obj | ConvertTo-Json -Depth 8)
}
function GetJson($url, $headers) {
    Invoke-RestMethod -Uri $url -Method GET -Headers $headers
}

Write-Host "=== 1. Login super_admin ==="
$saHeaders = @{ Accept = 'application/json' }
$r = PostJson "$base/auth/login" $saHeaders @{ email='superadmin@platform.com'; password='123456' }
$saToken = $r.token
Write-Host "super_admin token ok, role=$($r.user.role)"

$saAuth = @{ Accept = 'application/json'; Authorization = "Bearer $saToken" }

Write-Host "`n=== 2. List companies (super_admin) ==="
$companies = GetJson "$base/admin/companies" $saAuth
Write-Host "companies in platform: $($companies.data.Count)"
$companies.data | ForEach-Object { Write-Host " - $($_.name) ($($_.slug))" }

Write-Host "`n=== 3. Create new company + admin (super_admin) ==="
$new = PostJson "$base/admin/companies" $saAuth @{
    name='Acme Inc'; slug='acme'; admin_name='Jane Admin';
    admin_email='jane@acme.com'; admin_password='secret123'
}
Write-Host "new company id=$($new.company.id) name=$($new.company.name)"
Write-Host "new admin id=$($new.admin.id) email=$($new.admin.email)"

Write-Host "`n=== 4. Login as Demo admin ==="
$r = PostJson "$base/auth/login" $saHeaders @{ email='admin@demo.com'; password='123456' }
$adminToken = $r.token
Write-Host "admin token ok, company=$($r.company.name)"
$adminAuth = @{ Accept = 'application/json'; Authorization = "Bearer $adminToken" }

Write-Host "`n=== 5. List objectives (demo company) ==="
$obj = GetJson "$base/objectives" $adminAuth
Write-Host "objectives count: $($obj.data.Count)"
$obj.data | ForEach-Object { Write-Host " - [$($_.quarter)] $($_.title) progress=$($_.progress)% status=$($_.status) krs=$($_.keyResults.Count)" }

Write-Host "`n=== 6. Dashboard stats ==="
GetJson "$base/dashboard/stats?quarter=Q4-2024" $adminAuth | ConvertTo-Json -Depth 5

Write-Host "`n=== 7. Create new objective + 2 KRs ==="
$depts = GetJson "$base/departments" $adminAuth
$deptId = $depts.data[0].id
$users = GetJson "$base/users" $adminAuth
$ownerId = $users.data[0].id

$newObj = PostJson "$base/objectives" $adminAuth @{
    title='Objetivo smoke test'; description='Creado desde smoke test';
    department_id=[int]$deptId; owner_id=[int]$ownerId;
    quarter='Q1-2025'; calculation_mode='automatic'
}
$objId = $newObj.objective.id
Write-Host "new objective id=$objId"

$newKr = PostJson "$base/objectives/$objId/key-results" $adminAuth @{
    title='KR inicial'; target=100; current_value=20;
    start_value=0; unit='%'; sums_to_objective=$true
}
$krId = $newKr.keyResult.id
Write-Host "new KR id=$krId progress=$($newKr.keyResult.progress)%"

Write-Host "`n=== 8. Update KR current_value to 100 (should auto-complete + log) ==="
$upd = PutJson "$base/objectives/$objId/key-results/$krId" $adminAuth @{
    current_value=100; target=100
}
Write-Host "KR after update: status=$($upd.keyResult.status) progress=$($upd.keyResult.progress)%"

Write-Host "`n=== 9. Fetch logs for that KR ==="
$logs = GetJson "$base/key-results/$krId/logs" $adminAuth
Write-Host "logs count: $($logs.data.Count)"
$logs.data | ForEach-Object {
    Write-Host " - [$($_.createdAt)] by $($_.user.name) -> changes:"
    $_.changes | ForEach-Object { Write-Host "    * $($_.field): $($_.old) -> $($_.new)" }
}

Write-Host "`n=== 10. Verify parent objective was recalculated ==="
$objDetail = GetJson "$base/objectives/$objId" $adminAuth
Write-Host "objective progress=$($objDetail.objective.progress)% status='$($objDetail.objective.status)'"

Write-Host "`n=== 11. Login as standard user (carlos, Comercial) ==="
$r = PostJson "$base/auth/login" $saHeaders @{ email='carlos@demo.com'; password='123456' }
$userToken = $r.token
Write-Host "user role=$($r.user.role) deptId=$($r.user.departmentId)"
$userAuth = @{ Accept = 'application/json'; Authorization = "Bearer $userToken" }

Write-Host "`n=== 12. Standard user should be FORBIDDEN to edit an IT objective ==="
$itObj = $obj.data | Where-Object { $_.department.slug -eq 'it' } | Select-Object -First 1
if ($itObj) {
    try {
        PutJson "$base/objectives/$($itObj.id)" $userAuth @{ title='Hackeado' } | Out-Null
        Write-Host "UNEXPECTED: edit was allowed"
    } catch {
        Write-Host ("blocked as expected: " + $_.Exception.Message)
    }
} else {
    Write-Host "no IT objective found to test"
}

Write-Host "`n=== 13. Logout admin ==="
Invoke-RestMethod -Uri "$base/auth/logout" -Method POST -Headers $adminAuth | ConvertTo-Json

Write-Host "`nSMOKE TEST OK"
