@extends('layouts.admin')
@section('title','Admin Dashboard')
@section('content')
@php $recentAuditLogs=$recentAuditLogs??collect(); @endphp
<div class="space-y-6 max-w-7xl mx-auto">
  <div class="relative overflow-hidden bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/70 dark:border-gray-800 p-6 lg:p-7 shadow-sm">
   <div class="pointer-events-none absolute -top-24 -right-24 w-72 h-72 rounded-full bg-blue-500/10 dark:bg-blue-500/10 blur-3xl" aria-hidden="true"></div>
   <div class="pointer-events-none absolute -bottom-28 -left-16 w-64 h-64 rounded-full bg-indigo-500/10 dark:bg-indigo-500/10 blur-3xl" aria-hidden="true"></div>
   <div class="relative flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
    <div class="flex gap-4 items-center">
     <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-blue-600/25 ring-4 ring-blue-100 dark:ring-blue-500/20">{{ substr(Auth::user()->name,0,1) }}</div>
     <div><p class="text-blue-600 dark:text-blue-400 text-xs tracking-widest uppercase font-semibold">Aspire Control Center</p><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ Auth::user()->name }}</h1><p class="text-gray-500 dark:text-gray-400 text-sm mt-1 flex items-center gap-2 flex-wrap">{{ now()->format('l, F j, Y') }} · System operational
       <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-full capitalize
         @if($performance['teacher_growth_trend'] === 'improving') bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/20
         @elseif($performance['teacher_growth_trend'] === 'declining') bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-500/20
         @else bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-700 @endif">
         <i class="fas @if($performance['teacher_growth_trend'] === 'improving') fa-arrow-trend-up @elseif($performance['teacher_growth_trend'] === 'declining') fa-arrow-trend-down @else fa-minus @endif text-[10px]"></i>
         {{ $performance['teacher_growth_trend'] }}
       </span>
     </p></div>
    </div>
   <div class="flex gap-3 flex-wrap">
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl px-4 py-3 flex items-center gap-3"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><div><p class="text-xs text-gray-500 dark:text-gray-400">System</p><p class="text-sm font-bold text-blue-700 dark:text-blue-300">Online</p></div></div>
    <div class="bg-gray-50 dark:bg-gray-800/60 border dark:border-gray-700 rounded-xl px-4 py-3 hidden sm:flex items-center gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-users text-sm"></i></div>
      <div><p class="text-xs text-gray-500 dark:text-gray-400">Users</p><p class="text-sm font-bold text-gray-900 dark:text-white">{{ $stats['total_users'] }}</p></div>
    </div>
   </div>
  </div>
   <div class="relative mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl px-3 py-2.5 flex justify-between items-center text-blue-700 dark:text-blue-300 transition-all hover:shadow-md hover:-translate-y-0.5"><span class="flex items-center gap-2"><i class="fas fa-school text-blue-500"></i>Schools</span><strong>{{ $stats['total_schools'] }}</strong></div>
    <div class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 rounded-xl px-3 py-2.5 flex justify-between items-center text-blue-700 dark:text-blue-300 transition-all hover:shadow-md hover:-translate-y-0.5"><span class="flex items-center gap-2"><i class="fas fa-clipboard-list text-blue-500"></i>Observations</span><strong>{{ $stats['total_observations'] }}</strong></div>
    <div class="bg-gray-50 dark:bg-gray-800/60 border dark:border-gray-700 rounded-xl px-3 py-2.5 hidden sm:flex justify-between items-center text-gray-700 dark:text-gray-200 transition-all hover:shadow-md hover:-translate-y-0.5"><span class="flex items-center gap-2"><i class="fas fa-star text-amber-500"></i>Avg COT</span><strong>{{ number_format($performance['average_cot_score'],2) }}</strong></div>
    <div class="bg-gray-50 dark:bg-gray-800/60 border dark:border-gray-700 rounded-xl px-3 py-2.5 hidden lg:flex justify-between items-center text-gray-700 dark:text-gray-200 transition-all hover:shadow-md hover:-translate-y-0.5"><span class="flex items-center gap-2"><i class="fas fa-chart-line text-emerald-500"></i>Growth</span><strong class="capitalize">{{ $performance['teacher_growth_trend'] }}</strong></div>
   </div>
 </div>
  <div class="grid xl:grid-cols-3 gap-6">
   <div class="xl:col-span-2 space-y-6">
    <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <div class="flex justify-between mb-5"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-chart-column text-blue-600 dark:text-blue-400"></i> System Overview</h2><span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-700 dark:text-blue-300">{{ $stats['total_observations'] }} total obs</span></div>
     <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-users"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Total Users</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['total_users'] }}</p></div></div></div>
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-school"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Active Schools</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['total_schools'] }}</p></div></div></div>
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-clipboard-list"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Total Observations</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['total_observations'] }}</p></div></div></div>
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-clock"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Pending COTs</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['pending_cots'] }}</p></div></div></div>
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-eye"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">Active Observations</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['active_observations'] }}</p></div></div></div>
      <div class="rounded-2xl border dark:border-gray-800 p-5 bg-white dark:bg-gray-900 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 hover:border-blue-200 dark:hover:border-blue-500/30"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-500/10 flex items-center justify-center text-blue-600 dark:text-blue-300"><i class="fas fa-user-tie"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500 dark:text-gray-400">All Supervisors</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900 dark:text-white">{{ $stats['total_users'] }}</p></div></div></div>
     </div>
    </div>
   <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <div class="flex gap-2 mb-5"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><h2 class="text-xs font-bold tracking-widest uppercase text-gray-900 dark:text-white"><i class="fas fa-chart-line text-blue-600 dark:text-blue-400 mr-1"></i> Performance Summary</h2></div>
     <div class="grid sm:grid-cols-3 gap-4">
      <div class="rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Avg COT Score</p><p class="text-3xl font-extrabold mt-2 text-blue-700 dark:text-blue-300">{{ number_format($performance['average_cot_score'],2) }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">out of 7.0</p></div>
      <div class="rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Growth Trend</p><p class="text-2xl font-bold mt-2 capitalize text-blue-700 dark:text-blue-300">{{ $performance['teacher_growth_trend'] }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">vs last month</p></div>
      <div class="rounded-2xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Completed / Month</p><p class="text-3xl font-extrabold mt-2 text-blue-700 dark:text-blue-300">{{ $performance['completed_observations_this_month'] }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">observations</p></div>
     </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <div class="flex justify-between mb-5"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-list-check text-blue-600 dark:text-blue-400"></i> Observation Status</h2><span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-700 dark:text-blue-300">{{ $stats['total_observations'] }} total</span></div>
     <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-4 bg-blue-50/50 dark:bg-blue-500/10"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Planning</p><p class="text-2xl font-extrabold mt-1 text-blue-700 dark:text-blue-300">{{ $stats['pending_cots'] }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">pre-observation</p></div>
      <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-4 bg-blue-50/50 dark:bg-blue-500/10"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Ongoing</p><p class="text-2xl font-extrabold mt-1 text-blue-700 dark:text-blue-300">{{ $stats['active_observations'] }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">in progress</p></div>
      <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-4 bg-blue-50/50 dark:bg-blue-500/10"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">Completed</p><p class="text-2xl font-extrabold mt-1 text-blue-700 dark:text-blue-300">{{ $performance['completed_observations_this_month'] }}</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">this month</p></div>
      <div class="rounded-2xl border border-blue-100 dark:border-blue-500/20 p-4 bg-blue-50/50 dark:bg-blue-500/10"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700 dark:text-blue-300">System Health</p><p class="text-2xl font-extrabold mt-1 text-blue-700 dark:text-blue-300">● Healthy</p><p class="text-xs text-blue-600 dark:text-blue-400 mt-1">{{ $systemStatus['database'] }} · {{ $systemStatus['file_storage'] }}</p></div>
     </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-5">
     <div class="flex flex-wrap items-center justify-between gap-3 mb-4"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-chart-pie text-blue-600 dark:text-blue-400"></i> Analytics Overview</h2><span class="text-[11px] px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 text-blue-700 dark:text-blue-300">Last 6 months</span></div>
     <div class="grid sm:grid-cols-2 gap-3">
      <div class="rounded-xl border dark:border-gray-800 p-4">
       <div class="flex items-center justify-between mb-2"><h3 class="text-xs font-semibold text-gray-900 dark:text-white">Observations / Month</h3><span class="text-[10px] text-gray-400 dark:text-gray-500">{{ array_sum($charts['monthly']['counts']) }} total</span></div>
       <div class="h-44"><canvas id="adminMonthlyChart"></canvas></div>
      </div>
      <div class="rounded-xl border dark:border-gray-800 p-4">
       <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-2">By Status</h3>
       <div class="h-44"><canvas id="adminStatusChart"></canvas></div>
      </div>
      <div class="rounded-xl border dark:border-gray-800 p-4">
       <div class="flex items-center justify-between mb-2"><h3 class="text-xs font-semibold text-gray-900 dark:text-white">Avg COT Trend</h3><span class="text-[10px] text-gray-400 dark:text-gray-500">/ 7.0</span></div>
       <div class="h-44"><canvas id="adminScoreChart"></canvas></div>
      </div>
      <div class="rounded-xl border dark:border-gray-800 p-4">
       <h3 class="text-xs font-semibold text-gray-900 dark:text-white mb-2">Users by Role</h3>
       <div class="h-44"><canvas id="adminRoleChart"></canvas></div>
      </div>
     </div>
    </div>
   </div>
  <div class="space-y-6">
   <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-bolt text-blue-600 dark:text-blue-400"></i> Quick Actions</h2>
     <div class="space-y-2">
      <a href="{{ route('admin.users.index') }}" class="flex gap-3 p-3 rounded-xl border dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/10"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-users text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900 dark:text-white">Manage Users</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['total_users'] }} registered</p></div><i class="fas fa-chevron-right text-xs text-blue-600 dark:text-blue-400"></i></a>
      <a href="{{ route('admin.schools.index') }}" class="flex gap-3 p-3 rounded-xl border dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/10"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-school text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900 dark:text-white">Manage Schools</p><p class="text-xs text-gray-500 dark:text-gray-400">{{ $stats['total_schools'] }} active</p></div><i class="fas fa-chevron-right text-xs text-blue-600 dark:text-blue-400"></i></a>
      <a href="{{ route('admin.users.create') }}" class="flex gap-3 p-3 rounded-xl border dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/10"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-user-plus text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900 dark:text-white">Add User</p><p class="text-xs text-gray-500 dark:text-gray-400">Create account</p></div><i class="fas fa-chevron-right text-xs text-blue-600 dark:text-blue-400"></i></a>
      <a href="{{ route('admin.schools.create') }}" class="flex gap-3 p-3 rounded-xl border dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/10"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-plus text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900 dark:text-white">Add School</p><p class="text-xs text-gray-500 dark:text-gray-400">Create new</p></div><i class="fas fa-chevron-right text-xs text-blue-600 dark:text-blue-400"></i></a>
      <a href="{{ route('admin.supervisors.index') }}" class="flex gap-3 p-3 rounded-xl border dark:border-gray-800 hover:border-blue-200 dark:hover:border-blue-500/30 hover:bg-blue-50 dark:hover:bg-blue-500/10"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-user-tie text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900 dark:text-white">Supervisors</p><p class="text-xs text-gray-500 dark:text-gray-400">Manage roles</p></div><i class="fas fa-chevron-right text-xs text-blue-600 dark:text-blue-400"></i></a>
     </div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <div class="flex justify-between mb-4"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-clock-rotate-left text-blue-600 dark:text-blue-400"></i> Recent Activity</h2><a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-blue-600 dark:text-blue-400">View all →</a></div>
     <div class="space-y-3 max-h-72 overflow-auto pr-1">@forelse($recentAuditLogs as $log)<div class="flex gap-3"><div class="w-2 h-2 rounded-full mt-2 bg-blue-500 shrink-0"></div><div class="flex-1 min-w-0"><p class="text-sm truncate text-gray-900 dark:text-gray-100"><span class="font-semibold">{{ $log->user?->name ?? 'System' }}</span> {{ str_replace('_',' ',$log->action) }} <span class="text-gray-500 dark:text-gray-400">{{ str_replace('_',' ',$log->module ?? '') }}</span> <span class="text-gray-400 dark:text-gray-500">#{{ $log->record_id }}</span></p><p class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->diffForHumans() }}</p></div></div>@empty<p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8 border-2 border-dashed dark:border-gray-700 rounded-xl">No recent activity</p>@endforelse</div>
    </div>
    <div class="bg-white dark:bg-gray-900 rounded-2xl border dark:border-gray-800 p-6">
     <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2 text-gray-900 dark:text-white"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-gear text-blue-600 dark:text-blue-400"></i> System Status <span id="status-last-checked" class="text-[10px] font-normal text-gray-400 dark:text-gray-500 ml-auto"></span></h2>
    <div id="system-status-list" class="space-y-2">
      @php $ss=[['Database',$systemStatus['database']],['API',$systemStatus['api_services']],['Email',$systemStatus['email_service']],['Storage',$systemStatus['file_storage']],['AI',$systemStatus['ai_processing']]]; @endphp
      @foreach($ss as [$k,$v])
      <div class="flex justify-between p-3 rounded-xl bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20" data-status-key="{{ strtolower($k) }}">
        <span class="text-sm text-blue-700 dark:text-blue-300"><i class="fas fa-circle text-[8px] mr-1"></i>{{ $k }}</span>
        <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border dark:border-gray-700 text-blue-700 dark:text-blue-300">{{ $v }}</span>
      </div>
      @endforeach
     </div>
     <div class="flex justify-between text-xs text-gray-400 dark:text-gray-500 mt-3">
      <span id="status-mem">Mem {{ $systemStatus['server_usage']['memory_usage'] ?? '—' }}</span>
      <span id="status-peak">Peak {{ $systemStatus['server_usage']['memory_peak'] ?? '—' }}</span>
    </div>
   </div>
  </div>
 </div>
</div>
@endsection
@push('scripts')
<script>
(function() {
    const STATUS_URL = '{{ route("admin.dashboard.status") }}';
    const INTERVAL = 30000;
    const statusList = document.getElementById('system-status-list');
    const lastChecked = document.getElementById('status-last-checked');
    const memEl = document.getElementById('status-mem');
    const peakEl = document.getElementById('status-peak');

    const labelMap = {
        database: 'Database',
        api_services: 'API',
        email_service: 'Email',
        file_storage: 'Storage',
        ai_processing: 'AI',
    };

    function getStatusColor(value) {
        const v = value.toLowerCase();
        if (v.includes('connected') || v.includes('operational') || v.includes('online')) return 'text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/20';
        if (v.includes('disabled') || v.includes('not configured')) return 'text-amber-700 dark:text-amber-300 bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/20';
        if (v.includes('error') || v.includes('disconnected') || v.includes('unreachable') || v.includes('degraded')) return 'text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/20';
        return 'text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 border dark:border-gray-700';
    }

    function getDotColor(value) {
        const v = value.toLowerCase();
        if (v.includes('connected') || v.includes('operational') || v.includes('online')) return 'text-emerald-500';
        if (v.includes('disabled') || v.includes('not configured')) return 'text-amber-500';
        return 'text-red-500';
    }

    function updateStatus(data) {
        const keys = ['database', 'api_services', 'email_service', 'file_storage', 'ai_processing'];
        keys.forEach(key => {
            const row = statusList.querySelector(`[data-status-key="${key === 'api_services' ? 'api' : key === 'email_service' ? 'email' : key === 'file_storage' ? 'storage' : key === 'ai_processing' ? 'ai' : key}"]`);
            if (!row) return;
            const val = data[key] ?? 'Unknown';
            const dot = row.querySelector('i.fa-circle');
            const badge = row.querySelector('span:last-child');
            if (dot) dot.className = `fas fa-circle text-[8px] mr-1 ${getDotColor(val)}`;
            if (badge) {
                badge.textContent = val;
                badge.className = `text-xs font-semibold px-2.5 py-1 rounded-full bg-white dark:bg-gray-800 border dark:border-gray-700 ${getStatusColor(val)}`;
            }
        });

        if (data.server_usage) {
            memEl.textContent = 'Mem ' + (data.server_usage.memory_usage || '—');
            peakEl.textContent = 'Peak ' + (data.server_usage.memory_peak || '—');
        }

        if (data.checked_at) {
            const d = new Date(data.checked_at);
            lastChecked.textContent = 'Updated ' + d.toLocaleTimeString();
        }
    }

    function fetchStatus() {
        fetch(STATUS_URL, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(updateStatus)
        .catch(err => {
            if (lastChecked) lastChecked.textContent = 'Status check failed: ' + err.message;
        });
    }

    fetchStatus();
    setInterval(fetchStatus, INTERVAL);
})();
</script>
@endpush
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;
    const isDark = document.documentElement.classList.contains('dark');
    Chart.defaults.color = isDark ? '#9ca3af' : '#6b7280';
    Chart.defaults.borderColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
    Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';

    const tooltipStyle = {
        backgroundColor: isDark ? '#1f2937' : '#1e1b4b',
        titleColor: '#fff',
        bodyColor: isDark ? '#d1d5db' : '#e0e7ff',
        padding: 10,
        cornerRadius: 10
    };
    const legendLabels = { boxWidth: 10, boxHeight: 10, borderRadius: 5, useBorderRadius: true, padding: 14, font: { size: 11 } };

    const monthlyCtx = document.getElementById('adminMonthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: @json($charts['monthly']['labels']),
                datasets: [{
                    label: 'Observations',
                    data: @json($charts['monthly']['counts']),
                    backgroundColor: 'rgba(37, 99, 235, 0.55)',
                    hoverBackgroundColor: 'rgba(37, 99, 235, 0.8)',
                    borderColor: 'rgba(37, 99, 235, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });
    }

    const scoreCtx = document.getElementById('adminScoreChart');
    if (scoreCtx) {
        new Chart(scoreCtx, {
            type: 'line',
            data: {
                labels: @json($charts['monthly']['labels']),
                datasets: [{
                    label: 'Avg COT Score',
                    data: @json($charts['scores']),
                    spanGaps: true,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.10)',
                    fill: true,
                    tension: 0.38,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981',
                    borderWidth: 2.2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: tooltipStyle },
                scales: { y: { min: 0, max: 7, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
            }
        });
    }

    const statusCtx = document.getElementById('adminStatusChart');
    if (statusCtx && @json(count($charts['byStatus']['labels'])) > 0) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: @json($charts['byStatus']['labels']),
                datasets: [{
                    data: @json($charts['byStatus']['counts']),
                    backgroundColor: ['#f59e0b', '#3b82f6', '#6366f1', '#8b5cf6', '#10b981', '#64748b'],
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: legendLabels }, tooltip: tooltipStyle }
            }
        });
    }

    const roleCtx = document.getElementById('adminRoleChart');
    if (roleCtx) {
        new Chart(roleCtx, {
            type: 'doughnut',
            data: {
                labels: @json($charts['usersByRole']['labels']),
                datasets: [{
                    data: @json($charts['usersByRole']['counts']),
                    backgroundColor: ['#8b5cf6', '#3b82f6', '#0ea5e9', '#10b981'],
                    borderWidth: 2,
                    borderColor: isDark ? '#111827' : '#ffffff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom', labels: legendLabels }, tooltip: tooltipStyle }
            }
        });
    }
});
</script>
@endpush
