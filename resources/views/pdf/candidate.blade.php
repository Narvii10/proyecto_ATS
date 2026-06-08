<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; font-family: DejaVu Sans, sans-serif; }
  body { font-size: 11px; color: #1e293b; padding: 30px; }
  h1 { font-size: 18px; color: #4f46e5; border-bottom: 2px solid #e0e7ff; padding-bottom: 8px; margin-bottom: 16px; }
  h2 { font-size: 12px; font-weight: bold; color: #374151; text-transform: uppercase;
       letter-spacing: 0.5px; margin: 16px 0 8px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
  .grid2 { display: table; width: 100%; }
  .col { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
  .kv { margin-bottom: 4px; }
  .kv span { color: #9ca3af; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  th { background: #f8fafc; text-align: left; padding: 5px 8px; font-size: 9px;
       text-transform: uppercase; color: #6b7280; }
  td { padding: 4px 8px; border-bottom: 1px solid #f1f5f9; font-size: 10px; }
  .badge { display: inline-block; padding: 2px 6px; border-radius: 12px; font-size: 9px; font-weight: bold; }
  .badge-error   { background: #fee2e2; color: #dc2626; }
  .badge-warning { background: #fef3c7; color: #d97706; }
  .badge-ok      { background: #dcfce7; color: #16a34a; }
  .score-bar-wrap { background: #e5e7eb; border-radius: 4px; height: 8px; margin-top: 2px; }
  .score-bar      { height: 8px; border-radius: 4px; background: #6366f1; }
  .ai-box { background: #eef2ff; border-left: 3px solid #6366f1; padding: 10px 12px; border-radius: 4px; margin-top: 4px; }
  .page-break { page-break-after: always; }
</style>
</head>
<body>

<h1>Reporte de Candidato</h1>

<div class="grid2">
  <div class="col">
    <h2>Datos Personales</h2>
    <p class="kv"><span>Nombre: </span>{{ $candidate->name ?? '—' }}</p>
    <p class="kv"><span>Email: </span>{{ $candidate->email ?? '—' }}</p>
    <p class="kv"><span>Teléfono: </span>{{ $candidate->phone ?? '—' }}</p>
    <p class="kv"><span>Edad: </span>{{ $candidate->age ?? '—' }}</p>
  </div>
  <div class="col">
    @if($candidate->ai_assessment)
    <div class="ai-box">
      <strong>Evaluación IA:</strong> {{ $candidate->ai_assessment }}<br>
      <strong>Categoría:</strong> {{ $candidate->ai_category }}<br><br>
      {{ $candidate->ai_summary }}
    </div>
    @endif
  </div>
</div>

{{-- Symbol Table --}}
@php $latestCv = $candidate->cvDocuments->first(); @endphp
@if($latestCv && $latestCv->lexicalTokens->count() > 0)
<h2>Tabla de Símbolos ({{ $latestCv->lexicalTokens->count() }} tokens)</h2>
<table>
  <thead><tr><th>Tipo</th><th>Valor</th><th>Línea</th><th>Posición</th></tr></thead>
  <tbody>
  @foreach($latestCv->lexicalTokens->take(60) as $t)
  <tr>
    <td><span class="badge badge-ok">{{ $t->type }}</span></td>
    <td>{{ Str::limit($t->value, 40) }}</td>
    <td>{{ $t->line }}</td>
    <td>{{ $t->position }}</td>
  </tr>
  @endforeach
  </tbody>
</table>
@endif

{{-- Errors --}}
@if($latestCv)
@php
$lexErr = $latestCv->lexicalErrors;
$synErr = $latestCv->syntacticErrors;
$semErr = $latestCv->semanticErrors;
@endphp
@if($lexErr->count()+$synErr->count()+$semErr->count() > 0)
<h2>Errores de Análisis</h2>
<table>
  <thead><tr><th>Fase</th><th>Código</th><th>Mensaje</th><th>Severidad</th></tr></thead>
  <tbody>
  @foreach($lexErr as $e)
  <tr><td>Léxico</td><td>{{ $e->code }}</td><td>{{ $e->message }}</td><td><span class="badge badge-error">error</span></td></tr>
  @endforeach
  @foreach($synErr as $e)
  <tr><td>Sintáctico</td><td>{{ $e->code }}</td><td>{{ $e->message }}</td><td><span class="badge badge-warning">sintaxis</span></td></tr>
  @endforeach
  @foreach($semErr as $e)
  <tr><td>Semántico</td><td>{{ $e->code }}</td>
      <td>{{ $e->message }}</td>
      <td><span class="badge {{ $e->severity === 'error' ? 'badge-error' : 'badge-warning' }}">{{ $e->severity }}</span></td></tr>
  @endforeach
  </tbody>
</table>
@endif
@endif

{{-- Compatibility --}}
@if($candidate->compatibilityResults->count() > 0)
<h2>Compatibilidad por Vacante</h2>
<table>
  <thead><tr><th>Vacante</th><th>Total</th><th>Skills</th><th>Langs</th><th>Exp</th><th>Edu</th><th>Certs</th></tr></thead>
  <tbody>
  @foreach($candidate->compatibilityResults as $r)
  <tr>
    <td>{{ $r->vacancy?->title ?? '—' }}</td>
    <td><strong>{{ number_format($r->total_score,1) }}%</strong></td>
    <td>{{ number_format($r->skills_score,0) }}%</td>
    <td>{{ number_format($r->languages_score,0) }}%</td>
    <td>{{ number_format($r->experience_score,0) }}%</td>
    <td>{{ number_format($r->education_score,0) }}%</td>
    <td>{{ number_format($r->certifications_score,0) }}%</td>
  </tr>
  @endforeach
  </tbody>
</table>
@endif

<p style="margin-top:24px;font-size:9px;color:#9ca3af;">
  Generado el {{ now()->format('d/m/Y H:i') }} — ATS Compiler (Proyecto Final)
</p>
</body>
</html>
