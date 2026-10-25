@props(['status'])
@php($badge = match(strtolower((string) $status)) {'aktif' => ['Aktif','check','active'], 'pending','menunggu moderasi','menunggu' => ['Menunggu moderasi','clock','pending'], 'ditolak' => ['Ditolak','x','rejected'], 'nonaktif' => ['Nonaktif','minus','muted'], default => [ucfirst((string) $status),'info','muted']})
<span {{ $attributes->class(['ui-status-badge', 'ui-status-badge--'.$badge[2]]) }}><span aria-hidden="true">{{ ['check' => '✓', 'clock' => '◷', 'x' => '×', 'minus' => '−', 'info' => '•'][$badge[1]] }}</span>{{ $badge[0] }}</span>
