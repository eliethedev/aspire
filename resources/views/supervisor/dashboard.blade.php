@extends('layouts.supervisor')
@section('title','Supervisor Dashboard')
@push('styles')
<style>.kpi-card{transition:all .2s ease}.kpi-card:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(0,0,0,.08)}</style>
@endpush
@section('content')
@php
 $user=Auth::user();
 $completion=$stats['total_observations']>0?round(($stats['completed']/$stats['total_observations'])*100):0;
 $pendingTotal=$stats['in_progress']+$stats['stage_pre_planning']+$stats['stage_pre_conference']+$stats['stage_observation'];
 $todayStr=now()->format('l, F j, Y');
 $greeting=now()->hour<12?'Good morning':(now()->hour<18?'Good afternoon':'Good evening');
@endphp
<div class="max-w-7xl mx-auto space-y-6">
 <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/70 dark:border-gray-800 p-6 lg:p-7 shadow-sm">
  <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
   <div class="flex items-center gap-4">
    <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-lg shrink-0">{{ strtoupper(substr($user->name,0,1)) }}</div>
    <div><p class="text-gray-500 text-xs tracking-widest uppercase font-semibold">{{ $greeting }}</p><h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">Welcome back, {{ explode(' ',$user->name)[0] }}!</h1><p class="text-gray-500 text-sm mt-0.5">{{ $todayStr }} · Supervisor Workspace</p></div>
   </div>
   <div class="flex items-center gap-3 flex-wrap">
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl px-4 py-3 flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-white border flex items-center justify-center text-blue-600"><i class="fas fa-clock"></i></div>
      <div><p class="text-xs text-gray-500">Pending actions</p><p class="text-lg font-bold leading-none text-gray-900 dark:text-white">{{ $pendingTotal }}</p></div>
    </div>
    <div class="bg-gray-50 dark:bg-gray-800 border rounded-xl px-4 py-3 hidden sm:flex items-center gap-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 flex items-center justify-center"><i class="fas fa-check-circle"></i></div>
      <div><p class="text-xs text-gray-500">Completion</p><p class="text-lg font-bold leading-none text-gray-900 dark:text-white">{{ $completion }}%</p></div>
    </div>
   </div>
  </div>
  <div class="mt-5 grid grid-cols-3 gap-3 text-xs">
    <div class="bg-gray-50 dark:bg-gray-800 border rounded-xl px-3 py-2 flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> {{ $stats['total_teachers'] }} Teachers in scope</div>
    <div class="bg-gray-50 dark:bg-gray-800 border rounded-xl px-3 py-2 hidden sm:flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2 h-2 rounded-full bg-blue-600"></span> {{ $stats['total_observations'] }} Total</div>
    <div class="bg-gray-50 dark:bg-gray-800 border rounded-xl px-3 py-2 hidden lg:flex items-center gap-2 text-gray-700 dark:text-gray-300"><span class="w-2 h-2 rounded-full bg-amber-500"></span> Avg {{ number_format($stats['average_score'],1) }}</div>
  </div>
 </div>
 <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
  <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border p-5">
    <div class="flex justify-between items-start"><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Teachers</p><p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['total_teachers'] }}</p><p class="text-xs text-gray-500 mt-1">Under supervision</p></div><div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-users"></i></div></div>
  </div>
  <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border p-5">
    <div class="flex justify-between items-start"><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Total Obs</p><p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['total_observations'] }}</p><p class="text-xs text-gray-500 mt-1">{{ $stats['scheduled'] }} scheduled · {{ $stats['in_progress'] }} active</p></div><div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-clipboard-list"></i></div></div>
  </div>
  <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border p-5">
    <div class="flex justify-between items-start"><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Completed</p><p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $stats['completed'] }}</p></div><div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600"><i class="fas fa-check-circle"></i></div></div>
    <div class="mt-3"><div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-2"><div class="h-2 rounded-full bg-blue-600" style="width: {{ $completion }}%"></div></div><p class="text-[11px] text-gray-500 mt-1.5">{{ $completion }}% · {{ $stats['completed'] }}/{{ $stats['total_observations'] }}</p></div>
  </div>
  <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border p-5">
    <div class="flex justify-between items-start"><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Avg COT Score</p><p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ number_format($stats['average_score'],1) }}</p></div><div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-chart-column"></i></div></div>
    <div class="mt-3 flex items-center gap-1.5 text-xs">@if($trend>0)<span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold">+{{ number_format($trend,1) }}</span><span class="text-gray-500">vs prev</span>@elseif($trend<0)<span class="px-2 py-1 rounded-full bg-red-50 text-red-700 font-semibold">{{ number_format($trend,1) }}</span><span class="text-gray-500">vs prev</span>@else<span class="text-gray-400">No trend yet</span>@endif</div>
  </div>
  <div class="kpi-card bg-white dark:bg-gray-900 rounded-2xl border p-5">
    <div class="flex justify-between items-start"><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Pipeline</p><p class="text-3xl font-extrabold mt-1 text-gray-900 dark:text-white">{{ $pendingTotal }}</p></div><div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600"><i class="fas fa-clock"></i></div></div>
    <p class="text-xs text-gray-500 mt-3">{{ $stats['stage_post_conference'] }} post-conf · {{ $stats['stage_pre_planning'] }} planning</p>
  </div>
 </div>
 <div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <div class="flex items-center justify-between mb-4"><h2 class="text-sm font-bold tracking-widest uppercase text-gray-700 flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-blue-600"></span> COT Score Trend</h2><span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700">{{ count($cotScores) }} points</span></div>
    @if(count($cotScores)>0)<div class="h-56"><canvas id="cotChart"></canvas></div><p class="text-[11px] text-gray-500 mt-3">Last {{ count($cotScores) }} scored · Avg {{ number_format($stats['average_score'],2) }}</p>
    @else<div class="flex flex-col items-center py-14 text-center border-2 border-dashed rounded-xl bg-gray-50/60"><div class="w-14 h-14 rounded-xl bg-white border flex items-center justify-center mb-3"><i class="fas fa-chart-line text-xl text-gray-400"></i></div><p class="text-sm font-medium text-gray-700">No scored observations yet</p><a href="{{ route('supervisor.observations.create') }}" class="mt-2 text-sm font-semibold text-blue-600">Create observation →</a></div>@endif
  </div>
  <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <h2 class="text-sm font-bold tracking-widest uppercase flex items-center gap-2 mb-4"><span class="w-1.5 h-5 rounded-full bg-blue-600"></span> Quick Actions</h2>
    <div class="space-y-3">
      <a href="{{ route('supervisor.observations.create') }}" class="flex items-center gap-3 p-3.5 rounded-xl border hover:shadow-sm hover:border-blue-200 bg-white group">
        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-plus"></i></div>
        <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">New Observation</p><p class="text-xs text-gray-500 truncate">Schedule COT/PPSSH</p></div><i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-500"></i>
      </a>
      <a href="{{ route('supervisor.teachers.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border hover:shadow-sm hover:border-blue-200 bg-white group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i class="fas fa-users"></i></div>
        <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">Teachers</p><p class="text-xs text-gray-500 truncate">{{ $stats['total_teachers'] }} faculty members</p></div><i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-500"></i>
      </a>
      <a href="{{ route('supervisor.reports.index') }}" class="flex items-center gap-3 p-3.5 rounded-xl border hover:shadow-sm hover:border-blue-200 bg-white group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i class="fas fa-chart-column"></i></div>
        <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">Reports</p><p class="text-xs text-gray-500 truncate">Analytics & exports</p></div><i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-500"></i>
      </a>
      <a href="{{ route('supervisor.feedback.center') }}" class="flex items-center gap-3 p-3.5 rounded-xl border hover:shadow-sm hover:border-blue-200 bg-white group">
        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0"><i class="fas fa-comments"></i></div>
        <div class="flex-1 min-w-0"><p class="text-sm font-semibold text-gray-900 group-hover:text-blue-600">Feedback Center</p><p class="text-xs text-gray-500 truncate">Review AI feedback</p></div><i class="fas fa-chevron-right text-xs text-gray-300 group-hover:text-blue-500"></i>
      </a>
    </div>
    <div class="mt-4 p-3 rounded-xl bg-blue-50 border border-blue-100"><p class="text-xs font-semibold text-blue-700">Pro tip</p><p class="text-xs text-blue-600 mt-1">Use PPSSH template for School Heads – Post-Conference is auto-managed.</p></div>
  </div>
 </div>
 <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
  <div class="flex items-center justify-between mb-5"><h2 class="text-sm font-bold tracking-widest uppercase flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-blue-600"></span> Continue Where You Left Off</h2>@if($todoObservations->count()>0)<a href="{{ route('supervisor.observations.index') }}" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-blue-50 text-blue-700 hover:bg-blue-100">View all →</a>@endif</div>
  @if($todoObservations->count()>0)
    <div class="grid md:grid-cols-2 gap-3">
      @foreach($todoObservations as $observation)
        @php $route=match($observation->stage){'pre_observation_planning'=>'supervisor.observations.preObservationPlanning','pre_conference'=>'supervisor.observations.preConference','observation'=>'supervisor.observations.observation','post_conference'=>'supervisor.observations.postConference',default=>null}; $stageLabel=match($observation->stage){'pre_observation_planning'=>'Planning','pre_conference'=>'Pre-Conference','observation'=>'Observation','post_conference'=>'Post-Conference',default=>ucwords(str_replace('_',' ',$observation->stage))}; $pct=match($observation->stage){'pre_observation_planning'=>25,'pre_conference'=>50,'observation'=>75,'post_conference'=>90,default=>10}; @endphp
        <div class="rounded-xl border p-4 flex items-center gap-4 hover:shadow-sm bg-white">
          <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shrink-0">{{ strtoupper(substr($observation->observee->user->name ?? '?',0,1)) }}</div>
          <div class="flex-1 min-w-0"><p class="text-sm font-semibold truncate text-gray-900">{{ $observation->observee->user->name ?? 'Unknown' }}</p><p class="text-xs text-gray-500">{{ $stageLabel }} · {{ $observation->observation_date?->format('M d, Y')??'No date' }}</p><div class="mt-2 h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-1.5 bg-blue-600 rounded-full" style="width: {{ $pct }}%"></div></div></div>
          @if($route)<a href="{{ route($route,$observation) }}" class="shrink-0 px-4 py-2 bg-blue-600 text-white rounded-xl text-xs font-semibold hover:bg-blue-700">Continue <i class="fas fa-arrow-right ml-1 text-xs"></i></a>@endif
        </div>
      @endforeach
    </div>
  @else<div class="flex flex-col items-center py-10 text-center border-2 border-dashed rounded-xl bg-gray-50/50"><p class="text-sm font-medium text-gray-700">All caught up!</p><p class="text-xs text-gray-500 mt-1">No pending evaluations.</p></div>@endif
 </div>
 <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
  <div class="flex items-center justify-between mb-5"><h2 class="text-sm font-bold tracking-widest uppercase flex items-center gap-2"><span class="w-1.5 h-5 rounded-full bg-blue-600"></span> Recent Activity</h2>@if($recentObservations->count()>0)<a href="{{ route('supervisor.observations.index') }}" class="text-xs font-semibold text-blue-600">View all →</a>@endif</div>
  @if($recentObservations->count()>0)<div class="divide-y divide-gray-100">@foreach($recentObservations as $observation)<div class="flex items-center justify-between py-3.5"><div class="flex items-center gap-3"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($observation->observee->user->name ?? '?',0,1)) }}</div><div><p class="text-sm font-medium text-gray-900">{{ $observation->observee->user->name ?? 'Unknown' }}</p><p class="text-xs text-gray-500">{{ $observation->observation_date->format('M d, Y') }} · {{ $observation->subject ?? '—' }}</p></div></div><div class="flex items-center gap-3"><span class="hidden sm:inline-flex px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $observation->status==='completed'?'bg-emerald-50 text-emerald-700 border border-emerald-200':($observation->status==='scheduled'?'bg-blue-50 text-blue-700 border border-blue-200':'bg-amber-50 text-amber-700 border border-amber-200') }}">{{ ucwords(str_replace('_',' ',$observation->status)) }}</span><a href="{{ route('supervisor.observations.show',$observation) }}" class="text-xs font-semibold px-3 py-1.5 rounded-full bg-gray-50 border hover:bg-white">View</a></div></div>@endforeach</div>@else<div class="flex flex-col items-center py-10 text-center border-2 border-dashed rounded-xl"><p class="text-sm text-gray-500">No recent activity.</p><a href="{{ route('supervisor.observations.create') }}" class="mt-2 text-sm font-semibold text-blue-600">Create observation →</a></div>@endif
 </div>
</div>
@if(count($cotScores)>0)<script src="https://cdn.jsdelivr.net/npm/chart.js"></script><script>(function(){const ctx=document.getElementById('cotChart').getContext('2d');new Chart(ctx,{type:'line',data:{labels:{!! json_encode($cotLabels) !!},datasets:[{label:'COT',data:{!! json_encode($cotScores) !!},borderColor:'#2563eb',backgroundColor:'rgba(37,99,235,0.08)',borderWidth:2.2,tension:.38,fill:true,pointRadius:4,pointHoverRadius:6,pointBackgroundColor:'#2563eb'}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{backgroundColor:'#1e3a8a',titleColor:'#fff',bodyColor:'#dbeafe',padding:10,cornerRadius:10}},scales:{y:{min:1,max:7,ticks:{stepSize:1,color:'#6b7280',font:{size:11}},grid:{color:'rgba(0,0,0,.06)'}},x:{ticks:{color:'#6b7280',font:{size:11}},grid:{display:false}}}}});})();</script>@endif
@endsection
