@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 space-y-8">
    <!-- Welcome Section -->
    <div class="glass-card rounded-2xl p-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2 text-gray-900">Welcome back, {{ Auth::user()->name }}!</h1>
                <p class="mt-1 text-gray-600">System administration overview and management</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">{{ now()->format('l, F j, Y') }}</p>
            </div>
        </div>
    </div>

        <!-- System Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Total Users -->
            <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Schools -->
            <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 shadow-lg shadow-emerald-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Schools</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_schools'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Total Observations -->
            <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 shadow-lg shadow-blue-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Observations</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total_observations'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Pending COTs -->
            <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-yellow-500 to-amber-600 shadow-lg shadow-yellow-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Pending COTs</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_cots'] }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Observations -->
            <div class="glass-card rounded-2xl p-6 hover:scale-105 transition-transform duration-300">
                <div class="flex items-center">
                    <div class="p-4 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Observations</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['active_observations'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-xl font-semibold mb-6 text-gray-900">Quick Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.users.index') }}" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-indigo-500/50">
                    <div class="p-3 rounded-xl bg-indigo-500/20 group-hover:bg-indigo-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-indigo-700 transition-colors text-gray-900">Manage Users</p>
                        <p class="text-sm text-gray-600">View and edit all users</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.schools.index') }}" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-emerald-500/50">
                    <div class="p-3 rounded-xl bg-emerald-500/20 group-hover:bg-emerald-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2h-3a2 2 0 00-2-2v14a2 2 0 002 2h3a2 2 0 002 2v3m0 2h4a2 2 0 012 2v10a2 2 0 012 2H7a2 2 0 002-2v-3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-emerald-700 transition-colors text-gray-900">Manage Schools</p>
                        <p class="text-sm text-gray-600">View and edit all schools</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.users.create') }}" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-blue-500/50">
                    <div class="p-3 rounded-xl bg-blue-500/20 group-hover:bg-blue-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-blue-700 transition-colors text-gray-900">Add User</p>
                        <p class="text-sm text-gray-600">Create new user account</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.schools.create') }}" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-pink-500/50">
                    <div class="p-3 rounded-xl bg-pink-500/20 group-hover:bg-pink-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-pink-700 transition-colors text-gray-900">Add School</p>
                        <p class="text-sm text-gray-600">Create new school</p>
                    </div>
                </a>
                
                <a href="{{ route('admin.supervisors.index') }}" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-amber-500/50">
                    <div class="p-3 rounded-xl bg-amber-500/20 group-hover:bg-amber-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-amber-700 transition-colors text-gray-900">Manage Supervisors</p>
                        <p class="text-sm text-gray-600">View and edit supervisors</p>
                    </div>
                </a>
                
                <a href="#" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-purple-500/50">
                    <div class="p-3 rounded-xl bg-purple-500/20 group-hover:bg-purple-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v1a3 3 0 003 3h0a3 3 0 003-3v-1m3-10V4a2 2 0 00-2-2H8a2 2 0 00-2 2v3m3 0h6"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-purple-700 transition-colors text-gray-900">System Reports</p>
                        <p class="text-sm text-gray-600">Generate reports</p>
                    </div>
                </a>
                
                <a href="#" class="group flex items-center p-5 border rounded-xl transition-all duration-300 border-gray-200 hover:bg-gray-50 hover:border-cyan-500/50">
                    <div class="p-3 rounded-xl bg-cyan-500/20 group-hover:bg-cyan-500/30 transition-colors mr-4">
                        <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold group-hover:text-cyan-700 transition-colors text-gray-900">Settings</p>
                        <p class="text-sm text-gray-600">Configure system</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="glass-card rounded-2xl p-8">
            <h2 class="text-xl font-semibold mb-6 text-gray-900">Performance Summary</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Average COT Score -->
                <div class="p-6 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-gray-600">Average COT Score</span>
                        <div class="p-2 rounded-lg bg-indigo-500/20">
                            <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ number_format($performance['average_cot_score'], 2) }}</p>
                    <p class="text-sm text-gray-600 mt-2">out of 5.0</p>
                </div>

                <!-- Teacher Growth Trend -->
                <div class="p-6 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-gray-600">Teacher Growth Trend</span>
                        <div class="p-2 rounded-lg @if($performance['teacher_growth_trend'] == 'improving') bg-emerald-500/20 @elseif($performance['teacher_growth_trend'] == 'declining') bg-red-500/20 @else bg-yellow-500/20 @endif">
                            <svg class="w-5 h-5 @if($performance['teacher_growth_trend'] == 'improving') text-emerald-400 @elseif($performance['teacher_growth_trend'] == 'declining') text-red-400 @else text-yellow-400 @endif" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                    <p class="text-2xl font-bold text-gray-900 @if($performance['teacher_growth_trend'] == 'improving') text-emerald-600 @elseif($performance['teacher_growth_trend'] == 'declining') text-red-600 @else text-yellow-600 @endif">
                        {{ ucfirst($performance['teacher_growth_trend']) }}
                    </p>
                    <p class="text-sm text-gray-600 mt-2">vs last month</p>
                </div>

                <!-- Completed This Month -->
                <div class="p-6 rounded-xl bg-gray-50 border border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-sm font-medium text-gray-600">Completed This Month</span>
                        <div class="p-2 rounded-lg bg-purple-500/20">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <p class="text-4xl font-bold text-gray-900">{{ $performance['completed_observations_this_month'] }}</p>
                    <p class="text-sm text-gray-600 mt-2">observations</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity & System Status -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="glass-card rounded-2xl p-8">
                <h2 class="text-xl font-semibold mb-6 text-gray-900">Recent Activity</h2>
                <div class="space-y-4">
                    <!-- Latest Users -->
                    @foreach($recentActivity['latest_users'] as $user)
                    <div class="flex items-start space-x-4 p-4 rounded-xl transition-colors bg-gray-50 hover:bg-gray-100">
                        <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full mt-2 shadow-lg shadow-indigo-500/50"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">New {{ $user->role }} registered: <span class="text-indigo-600">{{ $user->name }}</span></p>
                            <p class="text-xs mt-1 text-gray-600">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach

                    <!-- Latest Observations -->
                    @foreach($recentActivity['latest_observations'] as $observation)
                    <div class="flex items-start space-x-4 p-4 rounded-xl transition-colors bg-gray-50 hover:bg-gray-100">
                        <div class="w-3 h-3 bg-gradient-to-r from-pink-500 to-rose-500 rounded-full mt-2 shadow-lg shadow-pink-500/50"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Observation created for <span class="text-pink-600">{{ $observation->observee->user->name ?? 'Unknown' }}</span></p>
                            <p class="text-xs mt-1 text-gray-600">{{ $observation->created_at->diffForHumans() }} • {{ $observation->observee->school->name ?? 'Unknown School' }}</p>
                        </div>
                    </div>
                    @endforeach

                    <!-- Latest Schools -->
                    @foreach($recentActivity['latest_schools'] as $school)
                    <div class="flex items-start space-x-4 p-4 rounded-xl transition-colors bg-gray-50 hover:bg-gray-100">
                        <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full mt-2 shadow-lg shadow-emerald-500/50"></div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">New school added: <span class="text-emerald-600">{{ $school->name }}</span></p>
                            <p class="text-xs mt-1 text-gray-600">{{ $school->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- System Status -->
            <div class="glass-card rounded-2xl p-8">
                <h2 class="text-xl font-semibold mb-6 text-gray-900">System Status</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">Database</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">{{ $systemStatus['database'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">API Services</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-500 border border-yellow-500/30">{{ $systemStatus['api_services'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">Email Service</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">{{ $systemStatus['email_service'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">File Storage</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/20 text-emerald-500 border border-emerald-500/30">{{ $systemStatus['file_storage'] }}</span>
                    </div>
                    <div class="flex justify-between items-center p-4 rounded-xl bg-gray-50">
                        <span class="text-sm font-medium text-gray-700">AI Processing</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-500 border border-red-500/30">{{ $systemStatus['ai_processing'] }}</span>
                    </div>
                    
                    <!-- Server Usage -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Server Usage</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">Memory Usage</span>
                                <span class="text-xs font-medium text-gray-900">{{ $systemStatus['server_usage']['memory_usage'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">Memory Peak</span>
                                <span class="text-xs font-medium text-gray-900">{{ $systemStatus['server_usage']['memory_peak'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-xs text-gray-600">Load Average</span>
                                <span class="text-xs font-medium text-gray-900">{{ $systemStatus['server_usage']['load_average'] ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
