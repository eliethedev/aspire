@extends('layouts.admin')
@section('title','Admin Dashboard')
@section('content')
@php $recentAuditLogs=$recentAuditLogs??collect(); @endphp
<div class="space-y-6 max-w-7xl mx-auto">
 <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200/70 dark:border-gray-800 p-6 lg:p-7 shadow-sm">
  <div class="flex flex-col lg:flex-row gap-6 justify-between items-start lg:items-center">
   <div class="flex gap-4 items-center">
    <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-bold text-xl">{{ substr(Auth::user()->name,0,1) }}</div>
    <div><p class="text-blue-600 text-xs tracking-widest uppercase font-semibold">Aspire Control Center</p><h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome back, {{ Auth::user()->name }}</h1><p class="text-gray-500 text-sm mt-1">{{ now()->format('l, F j, Y') }} · System operational</p></div>
   </div>
   <div class="flex gap-3 flex-wrap">
    <div class="bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 flex items-center gap-3"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span><div><p class="text-xs text-gray-500">System</p><p class="text-sm font-bold text-blue-700">Online</p></div></div>
    <div class="bg-gray-50 border rounded-xl px-4 py-3 hidden sm:flex items-center gap-3">
      <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-users text-sm"></i></div>
      <div><p class="text-xs text-gray-500">Users</p><p class="text-sm font-bold text-gray-900">{{ $stats['total_users'] }}</p></div>
    </div>
   </div>
  </div>
  <div class="mt-5 grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
    <div class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2.5 flex justify-between text-blue-700"><span>Schools</span><strong>{{ $stats['total_schools'] }}</strong></div>
    <div class="bg-blue-50 border border-blue-100 rounded-xl px-3 py-2.5 flex justify-between text-blue-700"><span>Observations</span><strong>{{ $stats['total_observations'] }}</strong></div>
    <div class="bg-gray-50 border rounded-xl px-3 py-2.5 hidden sm:flex justify-between"><span>Avg COT</span><strong>{{ number_format($performance['average_cot_score'],2) }}</strong></div>
    <div class="bg-gray-50 border rounded-xl px-3 py-2.5 hidden lg:flex justify-between"><span>Growth</span><strong class="capitalize">{{ $performance['teacher_growth_trend'] }}</strong></div>
  </div>
 </div>
 <div class="grid xl:grid-cols-3 gap-6">
  <div class="xl:col-span-2 space-y-6">
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <div class="flex justify-between mb-5"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-chart-column text-blue-600"></i> System Overview</h2><span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700">{{ $stats['total_observations'] }} total obs</span></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-users"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Total Users</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['total_users'] }}</p></div></div></div>
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-school"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Active Schools</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['total_schools'] }}</p></div></div></div>
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-clipboard-list"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Total Observations</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['total_observations'] }}</p></div></div></div>
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-clock"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Pending COTs</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['pending_cots'] }}</p></div></div></div>
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-eye"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">Active Observations</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['active_observations'] }}</p></div></div></div>
      <div class="rounded-2xl border p-5 bg-white hover:shadow-sm"><div class="flex gap-3"><div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600"><i class="fas fa-user-tie"></i></div><div><p class="text-[11px] tracking-widest uppercase font-semibold text-gray-500">All Supervisors</p><p class="text-2xl font-extrabold leading-none mt-1 text-gray-900">{{ $stats['total_users'] }}</p></div></div></div>
    </div>
   </div>
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <div class="flex gap-2 mb-5"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><h2 class="text-xs font-bold tracking-widest uppercase"><i class="fas fa-chart-line text-blue-600 mr-1"></i> Performance Summary</h2></div>
    <div class="grid sm:grid-cols-3 gap-4">
      <div class="rounded-2xl bg-blue-50 border border-blue-100 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Avg COT Score</p><p class="text-3xl font-extrabold mt-2 text-blue-700">{{ number_format($performance['average_cot_score'],2) }}</p><p class="text-xs text-blue-600 mt-1">out of 7.0</p></div>
      <div class="rounded-2xl bg-blue-50 border border-blue-100 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Growth Trend</p><p class="text-2xl font-bold mt-2 capitalize text-blue-700">{{ $performance['teacher_growth_trend'] }}</p><p class="text-xs text-blue-600 mt-1">vs last month</p></div>
      <div class="rounded-2xl bg-blue-50 border border-blue-100 p-5"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Completed / Month</p><p class="text-3xl font-extrabold mt-2 text-blue-700">{{ $performance['completed_observations_this_month'] }}</p><p class="text-xs text-blue-600 mt-1">observations</p></div>
    </div>
   </div>
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <div class="flex justify-between mb-5"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-list-check text-blue-600"></i> Observation Status</h2><span class="text-xs px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-700">{{ $stats['total_observations'] }} total</span></div>
    <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <div class="rounded-2xl border p-4 bg-blue-50/50"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Planning</p><p class="text-2xl font-extrabold mt-1 text-blue-700">{{ $stats['pending_cots'] }}</p><p class="text-xs text-blue-600 mt-1">pre-observation</p></div>
      <div class="rounded-2xl border p-4 bg-blue-50/50"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Ongoing</p><p class="text-2xl font-extrabold mt-1 text-blue-700">{{ $stats['active_observations'] }}</p><p class="text-xs text-blue-600 mt-1">in progress</p></div>
      <div class="rounded-2xl border p-4 bg-blue-50/50"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">Completed</p><p class="text-2xl font-extrabold mt-1 text-blue-700">{{ $performance['completed_observations_this_month'] }}</p><p class="text-xs text-blue-600 mt-1">this month</p></div>
      <div class="rounded-2xl border p-4 bg-blue-50/50"><p class="text-[11px] tracking-widest uppercase font-semibold text-blue-700">System Health</p><p class="text-2xl font-extrabold mt-1 text-blue-700">● Healthy</p><p class="text-xs text-blue-600 mt-1">{{ $systemStatus['database'] }} · {{ $systemStatus['file_storage'] }}</p></div>
    </div>
   </div>
  </div>
  <div class="space-y-6">
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-bolt text-blue-600"></i> Quick Actions</h2>
    <div class="space-y-2">
      <a href="{{ route('admin.users.index') }}" class="flex gap-3 p-3 rounded-xl border hover:border-blue-200 hover:bg-blue-50"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-users text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900">Manage Users</p><p class="text-xs text-gray-500">{{ $stats['total_users'] }} registered</p></div><i class="fas fa-chevron-right text-xs text-blue-600"></i></a>
      <a href="{{ route('admin.schools.index') }}" class="flex gap-3 p-3 rounded-xl border hover:border-blue-200 hover:bg-blue-50"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-school text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900">Manage Schools</p><p class="text-xs text-gray-500">{{ $stats['total_schools'] }} active</p></div><i class="fas fa-chevron-right text-xs text-blue-600"></i></a>
      <a href="{{ route('admin.users.create') }}" class="flex gap-3 p-3 rounded-xl border hover:border-blue-200 hover:bg-blue-50"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-user-plus text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900">Add User</p><p class="text-xs text-gray-500">Create account</p></div><i class="fas fa-chevron-right text-xs text-blue-600"></i></a>
      <a href="{{ route('admin.schools.create') }}" class="flex gap-3 p-3 rounded-xl border hover:border-blue-200 hover:bg-blue-50"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-plus text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900">Add School</p><p class="text-xs text-gray-500">Create new</p></div><i class="fas fa-chevron-right text-xs text-blue-600"></i></a>
      <a href="{{ route('admin.supervisors.index') }}" class="flex gap-3 p-3 rounded-xl border hover:border-blue-200 hover:bg-blue-50"><div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center"><i class="fas fa-user-tie text-sm"></i></div><div class="flex-1"><p class="text-sm font-semibold text-gray-900">Supervisors</p><p class="text-xs text-gray-500">Manage roles</p></div><i class="fas fa-chevron-right text-xs text-blue-600"></i></a>
    </div>
   </div>
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <div class="flex justify-between mb-4"><h2 class="text-xs font-bold tracking-widest uppercase flex items-center gap-2"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-clock-rotate-left text-blue-600"></i> Recent Activity</h2><a href="{{ route('admin.audit-logs.index') }}" class="text-xs font-semibold text-blue-600">View all →</a></div>
    <div class="space-y-3 max-h-72 overflow-auto pr-1">@forelse($recentAuditLogs as $log)<div class="flex gap-3"><div class="w-2 h-2 rounded-full mt-2 bg-blue-500 shrink-0"></div><div class="flex-1 min-w-0"><p class="text-sm truncate"><span class="font-semibold">{{ $log->user?->name ?? 'System' }}</span> {{ str_replace('_',' ',$log->action) }} <span class="text-gray-500">{{ str_replace('_',' ',$log->module ?? '') }}</span> <span class="text-gray-400">#{{ $log->record_id }}</span></p><p class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</p></div></div>@empty<p class="text-sm text-gray-400 text-center py-8 border-2 border-dashed rounded-xl">No recent activity</p>@endforelse</div>
   </div>
   <div class="bg-white dark:bg-gray-900 rounded-2xl border p-6">
    <h2 class="text-xs font-bold tracking-widest uppercase mb-4 flex items-center gap-2"><span class="w-1.5 h-5 bg-blue-600 rounded-full"></span><i class="fas fa-gear text-blue-600"></i> System Status</h2>
    <div class="space-y-2">@php $ss=[['Database',$systemStatus['database']],['API',$systemStatus['api_services']],['Email',$systemStatus['email_service']],['Storage',$systemStatus['file_storage']],['AI',$systemStatus['ai_processing']]]; @endphp @foreach($ss as [$k,$v])<div class="flex justify-between p-3 rounded-xl bg-blue-50 border border-blue-100"><span class="text-sm text-blue-700"><i class="fas fa-circle text-[8px] mr-1"></i>{{ $k }}</span><span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white border text-blue-700">{{ $v }}</span></div>@endforeach</div>
    <div class="flex justify-between text-xs text-gray-400 mt-3"><span>Mem {{ $systemStatus['server_usage']['memory_usage'] ?? '—' }}</span><span>Peak {{ $systemStatus['server_usage']['memory_peak'] ?? '—' }}</span></div>
   </div>
  </div>
 </div>
</div>
@endsection
