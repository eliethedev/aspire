import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { ChevronLeft, ChevronRight, X, Clock, User, BookOpen } from 'lucide-react';

export default function CalendarIndex({ events, initialDate }) {
    const [currentDate, setCurrentDate] = useState(new Date(initialDate));
    const [selectedDate, setSelectedDate] = useState(null);
    const [selectedObservations, setSelectedObservations] = useState([]);
    const [showModal, setShowModal] = useState(false);

    // Get days in month
    const getDaysInMonth = (date) => {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
    };

    // Get first day of month
    const getFirstDayOfMonth = (date) => {
        return new Date(date.getFullYear(), date.getMonth(), 1).getDay();
    };

    // Get observations for a specific date
    const getObservationsForDate = (date) => {
        const dateStr = date.toISOString().split('T')[0];
        return events.filter(event => event.start.split('T')[0] === dateStr);
    };

    // Handle date click
    const handleDateClick = (day) => {
        const clickedDate = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        setSelectedDate(clickedDate);
        const observations = getObservationsForDate(clickedDate);
        setSelectedObservations(observations);
        setShowModal(true);
    };

    // Navigate to previous month
    const previousMonth = () => {
        setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() - 1));
    };

    // Navigate to next month
    const nextMonth = () => {
        setCurrentDate(new Date(currentDate.getFullYear(), currentDate.getMonth() + 1));
    };

    // Get stage badge color
    const getStageColor = (stage) => {
        switch (stage) {
            case 'pre_observation_planning':
                return 'bg-blue-100 text-blue-800 border-blue-300';
            case 'pre_conference':
                return 'bg-purple-100 text-purple-800 border-purple-300';
            case 'observation':
                return 'bg-indigo-100 text-indigo-800 border-indigo-300';
            case 'post_conference':
                return 'bg-green-100 text-green-800 border-green-300';
            default:
                return 'bg-gray-100 text-gray-800 border-gray-300';
        }
    };

    // Get status badge color
    const getStatusColor = (status) => {
        return status === 'completed'
            ? 'bg-green-100 text-green-800'
            : 'bg-yellow-100 text-yellow-800';
    };

    // Get stage label
    const getStageLabel = (stage) => {
        switch (stage) {
            case 'pre_observation_planning':
                return 'Pre-Observation';
            case 'pre_conference':
                return 'Pre-Conference';
            case 'observation':
                return 'Observation';
            case 'post_conference':
                return 'Post-Conference';
            default:
                return 'Unknown';
        }
    };

    const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    const daysInMonth = getDaysInMonth(currentDate);
    const firstDay = getFirstDayOfMonth(currentDate);
    const days = [];

    // Add empty cells for days before month starts
    for (let i = 0; i < firstDay; i++) {
        days.push(null);
    }

    // Add days of month
    for (let i = 1; i <= daysInMonth; i++) {
        days.push(i);
    }

    return (
        <AppLayout>
            <Head title="Calendar - Observations" />

            <div className="max-w-7xl mx-auto px-6 py-8">
                {/* Header */}
                <div className="mb-8">
                    <h1 className="text-3xl font-bold text-white mb-2">Observation Calendar</h1>
                    <p className="text-white/60">View and manage all observations for your school</p>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {/* Calendar */}
                    <div className="lg:col-span-3">
                        <div className="bg-white rounded-xl shadow-sm glass-card p-6">
                            {/* Calendar Header */}
                            <div className="flex items-center justify-between mb-6">
                                <h2 className="text-xl font-bold text-white">
                                    {monthNames[currentDate.getMonth()]} {currentDate.getFullYear()}
                                </h2>
                                <div className="flex gap-2">
                                    <button
                                        onClick={previousMonth}
                                        className="p-2 hover:bg-white/10 rounded-lg transition-colors"
                                    >
                                        <ChevronLeft className="w-5 h-5 text-white" />
                                    </button>
                                    <button
                                        onClick={nextMonth}
                                        className="p-2 hover:bg-white/10 rounded-lg transition-colors"
                                    >
                                        <ChevronRight className="w-5 h-5 text-white" />
                                    </button>
                                </div>
                            </div>

                            {/* Day names */}
                            <div className="grid grid-cols-7 gap-2 mb-2">
                                {dayNames.map(day => (
                                    <div
                                        key={day}
                                        className="text-center text-sm font-semibold text-white/60 py-2"
                                    >
                                        {day}
                                    </div>
                                ))}
                            </div>

                            {/* Calendar days */}
                            <div className="grid grid-cols-7 gap-2">
                                {days.map((day, index) => {
                                    if (day === null) {
                                        return (
                                            <div
                                                key={`empty-${index}`}
                                                className="aspect-square bg-white/5 rounded-lg"
                                            />
                                        );
                                    }

                                    const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                                    const dayObservations = getObservationsForDate(date);
                                    const isToday = new Date().toDateString() === date.toDateString();

                                    return (
                                        <button
                                            key={day}
                                            onClick={() => handleDateClick(day)}
                                            className={`aspect-square rounded-lg p-2 transition-all hover:bg-white/20 ${
                                                isToday ? 'bg-blue-500/30 border-2 border-blue-400' : 'bg-white/5 border border-white/10'
                                            }`}
                                        >
                                            <div className="h-full flex flex-col">
                                                <span className={`text-sm font-semibold ${isToday ? 'text-blue-300' : 'text-white'}`}>
                                                    {day}
                                                </span>
                                                {dayObservations.length > 0 && (
                                                    <div className="mt-1 flex flex-wrap gap-1">
                                                        {dayObservations.slice(0, 2).map((obs, idx) => (
                                                            <div
                                                                key={idx}
                                                                className="w-1.5 h-1.5 rounded-full"
                                                                style={{ backgroundColor: obs.backgroundColor }}
                                                            />
                                                        ))}
                                                        {dayObservations.length > 2 && (
                                                            <span className="text-xs text-white/60">
                                                                +{dayObservations.length - 2}
                                                            </span>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })}
                            </div>

                            {/* Legend */}
                            <div className="mt-6 pt-6 border-t border-white/10">
                                <h3 className="text-sm font-semibold text-white mb-3">Stage Colors</h3>
                                <div className="grid grid-cols-2 gap-3">
                                    <div className="flex items-center gap-2">
                                        <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#3b82f6' }} />
                                        <span className="text-sm text-white/80">Pre-Observation</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#8b5cf6' }} />
                                        <span className="text-sm text-white/80">Pre-Conference</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#a855f7' }} />
                                        <span className="text-sm text-white/80">Observation</span>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        <div className="w-3 h-3 rounded-full" style={{ backgroundColor: '#10b981' }} />
                                        <span className="text-sm text-white/80">Post-Conference</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Sidebar - Quick Stats */}
                    <div className="space-y-6">
                        {/* Stats Cards */}
                        <div className="bg-white rounded-xl shadow-sm glass-card p-6">
                            <h3 className="text-lg font-semibold text-white mb-4">Quick Stats</h3>
                            <div className="space-y-4">
                                <div className="bg-blue-500/10 rounded-lg p-4 border border-blue-500/20">
                                    <p className="text-sm text-white/60 mb-1">Total Observations</p>
                                    <p className="text-2xl font-bold text-blue-300">{events.length}</p>
                                </div>
                                <div className="bg-yellow-500/10 rounded-lg p-4 border border-yellow-500/20">
                                    <p className="text-sm text-white/60 mb-1">Pending</p>
                                    <p className="text-2xl font-bold text-yellow-300">
                                        {events.filter(e => e.extendedProps.status === 'pending').length}
                                    </p>
                                </div>
                                <div className="bg-green-500/10 rounded-lg p-4 border border-green-500/20">
                                    <p className="text-sm text-white/60 mb-1">Completed</p>
                                    <p className="text-2xl font-bold text-green-300">
                                        {events.filter(e => e.extendedProps.status === 'completed').length}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Quick Actions */}
                        <div className="bg-white rounded-xl shadow-sm glass-card p-6">
                            <h3 className="text-lg font-semibold text-white mb-4">Quick Actions</h3>
                            <div className="space-y-2">
                                <Link
                                    href={route('supervisor.observations.create')}
                                    className="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-center transition-colors"
                                >
                                    New Observation
                                </Link>
                                <Link
                                    href={route('supervisor.observations.index')}
                                    className="block w-full bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-center transition-colors"
                                >
                                    View All
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Modal for selected date observations */}
                {showModal && (
                    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
                        <div className="bg-white rounded-xl shadow-lg max-w-2xl w-full max-h-96 overflow-y-auto glass-card">
                            {/* Modal Header */}
                            <div className="sticky top-0 bg-white/10 backdrop-blur-sm border-b border-white/10 px-6 py-4 flex items-center justify-between">
                                <h3 className="text-lg font-semibold text-white">
                                    {selectedDate && selectedDate.toLocaleDateString('en-US', {
                                        weekday: 'long',
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    })}
                                </h3>
                                <button
                                    onClick={() => setShowModal(false)}
                                    className="p-1 hover:bg-white/10 rounded-lg transition-colors"
                                >
                                    <X className="w-5 h-5 text-white" />
                                </button>
                            </div>

                            {/* Modal Content */}
                            <div className="p-6">
                                {selectedObservations.length > 0 ? (
                                    <div className="space-y-4">
                                        {selectedObservations.map((observation) => (
                                            <Link
                                                key={observation.id}
                                                href={route('supervisor.observations.show', observation.id)}
                                                className="block p-4 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-colors group"
                                            >
                                                <div className="flex items-start justify-between mb-3">
                                                    <div className="flex-1">
                                                        <h4 className="font-semibold text-white group-hover:text-blue-300 transition-colors">
                                                            {observation.extendedProps.teacher_name}
                                                        </h4>
                                                        <p className="text-sm text-white/60 mt-1">
                                                            {observation.extendedProps.observation_date}
                                                        </p>
                                                    </div>
                                                    <span
                                                        className="px-3 py-1 rounded-full text-xs font-medium"
                                                        style={{
                                                            backgroundColor: observation.backgroundColor + '20',
                                                            color: observation.backgroundColor,
                                                            border: `1px solid ${observation.backgroundColor}`
                                                        }}
                                                    >
                                                        {getStageLabel(observation.extendedProps.stage)}
                                                    </span>
                                                </div>

                                                <div className="grid grid-cols-2 gap-3 text-sm">
                                                    <div className="flex items-center gap-2 text-white/70">
                                                        <BookOpen className="w-4 h-4" />
                                                        <span>{getStageLabel(observation.extendedProps.stage)}</span>
                                                    </div>
                                                    <div className="flex items-center gap-2 text-white/70">
                                                        <Clock className="w-4 h-4" />
                                                        <span className={`px-2 py-0.5 rounded text-xs font-medium ${
                                                            observation.extendedProps.status === 'completed'
                                                                ? 'bg-green-500/20 text-green-300'
                                                                : 'bg-yellow-500/20 text-yellow-300'
                                                        }`}>
                                                            {observation.extendedProps.status === 'completed' ? 'Completed' : 'Pending'}
                                                        </span>
                                                    </div>
                                                </div>

                                                {observation.extendedProps.overall_score && (
                                                    <div className="mt-3 pt-3 border-t border-white/10">
                                                        <p className="text-sm text-white/60">
                                                            Overall Score: <span className="text-white font-semibold">{observation.extendedProps.overall_score}</span>
                                                        </p>
                                                    </div>
                                                )}
                                            </Link>
                                        ))}
                                    </div>
                                ) : (
                                    <div className="text-center py-8">
                                        <p className="text-white/60 mb-4">No observations scheduled for this date</p>
                                        <Link
                                            href={route('supervisor.observations.create')}
                                            className="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                                        >
                                            Schedule Observation
                                        </Link>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
