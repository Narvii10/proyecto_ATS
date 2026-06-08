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
  .req-grid { display: table; width: 100%; margin-bottom: 12px; }
  .req-item { display: table-cell; padding-right: 20px; font-size: 10px; }
  .req-item strong { display: block; font-size: 9px; color: #6b7280; text-transform: uppercase; margin-bottom: 2px; }
  .tags { }
  .tag { display: inline-block; background: #e0e7ff; color: #4338ca; padding: 1px 6px; border-radius: 10px;
         font-size: 9px; margin: 1px; }
  table { width: 100%; border-collapse: collapse; margin-top: 6px; }
  thead tr { background: #f8fafc; }
  th { text-align: left; padding: 6px 8px; font-size: 9px; text-transform: uppercase; color: #6b7280; }
  td { padding: 5px 8px; border-bottom: 1px solid #f1f5f9; }
  .rank-1 { background: #fffbeb; }
  .score-high { color: #16a34a; font-weight: bold; }
  .score-mid  { color: #d97706; font-weight: bold; }
  .score-low  { color: #dc2626; }
  .top-badge  { background: #fef3c7; color: #b45309; padding: 2px 6px; border-radius: 10px; font-size: 9px; font-weight: bold; }
</style>
</head>
<body>

<h1>Ranking de Candidatos</h1>
<p style="color:#6b7280;margin-bottom:12px;">Vacante: <strong>{{ $vacancy->title }}</strong></p>

<h2>Requisitos de la Vacante</h2>
<div class="req-grid">
  <div class="req-item"><strong>Experiencia</strong>{{ $vacancy->required_years_experience }} año(s)</div>
  <div class="req-item"><strong>Educación</strong>{{ ucfirst($vacancy->required_education_level) }}</div>
  <div class="req-item">
    <strong>Habilidades</strong>
    <div class="tags">
      @foreach($vacancy->required_skills ?? [] as $s)<span class="tag">{{ $s }}</span>@endforeach
    </div>
  </div>
  <div class="req-item">
    <strong>Lenguajes</strong>
    <div class="tags">
      @foreach($vacancy->required_languages ?? [] as $l)<span class="tag">{{ $l }}</span>@endforeach
    </div>
  </div>
</div>

<h2>Tabla de Ranking ({{ $results->count() }} candidatos)</h2>

@if($results->isEmpty())
  <p style="color:#9ca3af;padding:12px 0;">Sin candidatos evaluados para esta vacante.</p>
@else
<table>
  <thead>
    <tr>
      <th style="width:30px">#</th>
      <th>Candidato</th>
      <th>Email</th>
      <th style="text-align:center">Total</th>
      <th style="text-align:center">Skills</th>
      <th style="text-align:center">Langs</th>
      <th style="text-align:center">Exp</th>
      <th style="text-align:center">Edu</th>
      <th style="text-align:center">Certs</th>
    </tr>
  </thead>
  <tbody>
  @foreach($results as $i => $r)
  <tr class="{{ $i === 0 ? 'rank-1' : '' }}">
    <td>
      {{ $i + 1 }}
      @if($i === 0)<span class="top-badge">TOP</span>@endif
    </td>
    <td>{{ $r->candidate?->name ?? '—' }}</td>
    <td style="color:#6b7280;font-size:9px;">{{ $r->candidate?->email ?? '—' }}</td>
    <td style="text-align:center">
      <span class="{{ $r->total_score >= 70 ? 'score-high' : ($r->total_score >= 40 ? 'score-mid' : 'score-low') }}">
        {{ number_format($r->total_score,1) }}%
      </span>
    </td>
    <td style="text-align:center">{{ number_format($r->skills_score,0) }}%</td>
    <td style="text-align:center">{{ number_format($r->languages_score,0) }}%</td>
    <td style="text-align:center">{{ number_format($r->experience_score,0) }}%</td>
    <td style="text-align:center">{{ number_format($r->education_score,0) }}%</td>
    <td style="text-align:center">{{ number_format($r->certifications_score,0) }}%</td>
  </tr>
  @endforeach
  </tbody>
</table>

@if($results->first())
<div style="margin-top:16px;background:#ecfdf5;border:1px solid #a7f3d0;padding:10px 14px;border-radius:6px;">
  <strong>Candidato destacado:</strong> {{ $results->first()->candidate?->name ?? '—' }} —
  Puntuación total: {{ number_format($results->first()->total_score, 1) }}%
</div>
@endif
@endif

<p style="margin-top:24px;font-size:9px;color:#9ca3af;">
  Generado el {{ now()->format('d/m/Y H:i') }} — ATS Compiler (Proyecto Final)
</p>
</body>
</html>
