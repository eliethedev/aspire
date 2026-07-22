@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                    <span class="text-indigo-600 dark:text-indigo-400 text-xl font-bold">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">Welcome back, {{ Auth::user()->name }}!</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ now()->format('l, F j, Y') }}</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Administrator
                </span>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    System Online
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 xl:items-start">
        <!-- ===== LEFT MAIN AREA ===== -->
        <div class="xl:col-span-2 space-y-6">

            <!-- System Statistics -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">System Overview</h2>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Users</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20">
                                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Schools</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_schools'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-violet-50">
                                <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Observations</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_observations'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20">
                                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pending COTs</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending_cots'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-rose-50">
                                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Observations</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active_observations'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 p-5 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-4">
                            <div class="p-3 rounded-lg bg-cyan-50">
                                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">All Supervisors</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_users'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Summary -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Performance Summary</h2>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg COT Score</span>
                            <div class="p-1.5 rounded-md bg-blue-100 dark:bg-blue-900/30">
                                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($performance['average_cot_score'], 2) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">out of 5.0</p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Growth Trend</span>
                            <div class="p-1.5 rounded-md @if($performance['teacher_growth_trend'] == 'improving') bg-emerald-100 dark:bg-emerald-900/30 @elseif($performance['teacher_growth_trend'] == 'declining') bg-red-100 dark:bg-red-900/30 @else bg-amber-100 dark:bg-amber-900/30 @endif">
                                <svg class="w-4 h-4 @if($performance['teacher_growth_trend'] == 'improving') text-emerald-600 dark:text-emerald-400 @elseif($performance['teacher_growth_trend'] == 'declining') text-red-600 dark:text-red-400 @else text-amber-600 dark:text-amber-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if($performance['teacher_growth_trend'] == 'improving')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    @elseif($performance['teacher_growth_trend'] == 'declining')
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14"/>
                                    @endif
                                </svg>
                            </div>
                        </div>
                        <p class="text-xl font-bold @if($performance['teacher_growth_trend'] == 'improving') text-emerald-600 dark:text-emerald-400 @elseif($performance['teacher_growth_trend'] == 'declining') text-red-600 dark:text-red-400 @else text-amber-600 dark:text-amber-400 @endif capitalize">
                            {{ $performance['teacher_growth_trend'] }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">vs last month</p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Completed/Month</span>
                            <div class="p-1.5 rounded-md bg-violet-100">
                                <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $performance['completed_observations_this_month'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">observations</p>
                    </div>
                </div>
            </div>

            <!-- Observation Status -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/></svg>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Observation Status Overview</h2>
                    </div>
                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:text-gray-400">{{ $stats['total_observations'] }} total</span>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-700 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-md bg-blue-50 dark:bg-blue-900/20">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Planning</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['pending_cots'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">pre-observation</p>
                    </div>
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-700 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-md bg-amber-50 dark:bg-amber-900/20">
                                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Ongoing</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['active_observations'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">in progress</p>
                    </div>
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-700 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-md bg-emerald-50 dark:bg-emerald-900/20">
                                <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Completed</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $performance['completed_observations_this_month'] }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">this month</p>
                    </div>
                    <div class="p-4 rounded-lg border border-gray-100 dark:border-gray-700 hover:shadow-sm transition-shadow">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 rounded-md bg-rose-50">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Cancelled</span>
                        </div>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">0</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">total</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== RIGHT SIDEBAR ===== -->
        <div class="xl:sticky xl:top-16 xl:self-start xl:max-h-[calc(100vh-4rem)] xl:overflow-y-auto dashboard-sidebar-scroll space-y-6">

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm" x-data="{ quickOpen: true }">
                <button @click="quickOpen = !quickOpen" class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6h16.5M3.75 12h16.5m-16.5 6h16.5"/></svg>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h2>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">{{ $stats['total_users'] + 3 }}</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': quickOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="quickOpen" x-collapse class="p-4 space-y-2">
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="p-2 rounded-md bg-blue-50 dark:bg-blue-900/20 group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 transition-colors">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Manage Users</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $stats['total_users'] }} registered</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-blue-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.schools.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="p-2 rounded-md bg-emerald-50 dark:bg-emerald-900/20 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/30 transition-colors">
                            <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">Manage Schools</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $stats['total_schools'] }} active</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-emerald-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="p-2 rounded-md bg-violet-50 group-hover:bg-violet-100 transition-colors">
                            <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-violet-600 transition-colors">Add User</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Create new account</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-violet-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.schools.create') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="p-2 rounded-md bg-amber-50 dark:bg-amber-900/20 group-hover:bg-amber-100 dark:group-hover:bg-amber-900/30 transition-colors">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Add School</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Create new school</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    <a href="{{ route('admin.supervisors.index') }}" class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group">
                        <div class="p-2 rounded-md bg-cyan-50 group-hover:bg-cyan-100 transition-colors">
                            <svg class="w-4 h-4 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-cyan-600 transition-colors">Supervisors</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Manage roles</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-300 group-hover:text-cyan-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm" x-data="{ activityOpen: true }">
                <button @click="activityOpen = !activityOpen" class="w-full px-6 py-4 flex items-center justify-between border-b border-gray-100 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent Activity</h2>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 dark:text-gray-400">{{ count($recentAuditLogs) }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.audit-logs.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 font-medium">View All</a>
                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-500 transition-transform duration-200" :class="{ 'rotate-180': activityOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </div>
                </button>
                <div x-show="activityOpen" x-collapse class="divide-y divide-gray-50">
                    @forelse($recentAuditLogs as $log)
                    <div class="flex items-start gap-3 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-2 h-2 rounded-full {{ $log->status === 'failed' ? 'bg-red-500' : ($log->action === 'created' ? 'bg-blue-500' : ($log->action === 'deleted' ? 'bg-red-500' : 'bg-amber-500')) }} mt-2 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-700 dark:text-gray-300 truncate">
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="capitalize">{{ str_replace('_', ' ', $log->action) }}</span>
                                @if($log->module)
                                    <span class="text-gray-500 dark:text-gray-400">{{ str_replace('_', ' ', $log->module) }}</span>
                                @endif
                                @if($log->record_id)
                                    <span class="text-gray-400 dark:text-gray-500">#{{ $log->record_id }}</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-6 text-center text-sm text-gray-400 dark:text-gray-500">
                        No recent activity recorded.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- System Status -->
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">System Status</h2>
                </div>
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $systemStatus['database'] === 'Connected' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Database</span>
                        </div>
                        <span class="text-xs font-medium {{ $systemStatus['database'] === 'Connected' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $systemStatus['database'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $systemStatus['api_services'] === 'Operational' ? 'bg-emerald-500' : 'bg-yellow-500' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">API Services</span>
                        </div>
                        <span class="text-xs font-medium {{ $systemStatus['api_services'] === 'Operational' ? 'text-emerald-600 dark:text-emerald-400' : 'text-yellow-600' }}">{{ $systemStatus['api_services'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $systemStatus['email_service'] === 'Operational' ? 'bg-emerald-500' : 'bg-yellow-500' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">Email Service</span>
                        </div>
                        <span class="text-xs font-medium {{ $systemStatus['email_service'] === 'Operational' ? 'text-emerald-600 dark:text-emerald-400' : 'text-yellow-600' }}">{{ $systemStatus['email_service'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $systemStatus['file_storage'] === 'Operational' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">File Storage</span>
                        </div>
                        <span class="text-xs font-medium {{ $systemStatus['file_storage'] === 'Operational' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">{{ $systemStatus['file_storage'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full {{ $systemStatus['ai_processing'] === 'Offline' ? 'bg-gray-300' : 'bg-emerald-500' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-gray-300">AI Processing</span>
                        </div>
                        <span class="text-xs font-medium {{ $systemStatus['ai_processing'] === 'Offline' ? 'text-gray-400 dark:text-gray-500' : 'text-emerald-600 dark:text-emerald-400' }}">{{ $systemStatus['ai_processing'] }}</span>
                    </div>
                </div>
                <div class="px-6 py-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                        <span>Memory: {{ $systemStatus['server_usage']['memory_usage'] ?? 'N/A' }}</span>
                        <span>Peak: {{ $systemStatus['server_usage']['memory_peak'] ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.13.3/dist/cdn.min.js"></script>
@endpush
