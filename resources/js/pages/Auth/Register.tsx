import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '../../layouts/GuestLayout';

interface RegisterProps {
    schools?: Array<{
        id: number;
        name: string;
        district?: string;
        division?: string;
    }>;
}

export default function Register({ schools }: RegisterProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        school_id: '',
        employee_id: '',
        grade_level_taught: '',
        subjects_taught: '',
        years_of_experience: '',
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout title="Register - ASPIRE">
            <Head title="Register - ASPIRE" />

            <form onSubmit={submit} className="space-y-5">
                {/* Personal Information */}
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-white/90">Personal Information</h3>
                    
                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Full Name
                        </label>
                        <input
                            type="text"
                            name="name"
                            value={data.name}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="Juan Dela Cruz"
                            autoComplete="name"
                            onChange={(e) => setData('name', e.target.value)}
                            required
                        />
                        {errors.name && (
                            <p className="mt-2 text-sm text-rose-400">{errors.name}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Email Address
                        </label>
                        <input
                            type="email"
                            name="email"
                            value={data.email}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="juan@school.edu.ph"
                            autoComplete="email"
                            onChange={(e) => setData('email', e.target.value)}
                            required
                        />
                        {errors.email && (
                            <p className="mt-2 text-sm text-rose-400">{errors.email}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Employee ID
                        </label>
                        <input
                            type="text"
                            name="employee_id"
                            value={data.employee_id}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="EMP-2024-001"
                            onChange={(e) => setData('employee_id', e.target.value)}
                            required
                        />
                        {errors.employee_id && (
                            <p className="mt-2 text-sm text-rose-400">{errors.employee_id}</p>
                        )}
                    </div>
                </div>

                {/* School Information */}
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-white/90">School Information</h3>
                    
                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            School
                        </label>
                        <select
                            name="school_id"
                            value={data.school_id}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            onChange={(e) => setData('school_id', e.target.value)}
                            required
                        >
                            <option value="">Select your school</option>
                            {schools?.map((school) => (
                                <option key={school.id} value={school.id}>
                                    {school.name}
                                </option>
                            ))}
                        </select>
                        {errors.school_id && (
                            <p className="mt-2 text-sm text-rose-400">{errors.school_id}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Grade Level Taught
                        </label>
                        <select
                            name="grade_level_taught"
                            value={data.grade_level_taught}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            onChange={(e) => setData('grade_level_taught', e.target.value)}
                            required
                        >
                            <option value="">Select grade level</option>
                            <option value="Kindergarten">Kindergarten</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="Grade 7">Grade 7</option>
                            <option value="Grade 8">Grade 8</option>
                            <option value="Grade 9">Grade 9</option>
                            <option value="Grade 10">Grade 10</option>
                            <option value="Grade 11">Grade 11</option>
                            <option value="Grade 12">Grade 12</option>
                        </select>
                        {errors.grade_level_taught && (
                            <p className="mt-2 text-sm text-rose-400">{errors.grade_level_taught}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Subjects Taught
                        </label>
                        <input
                            type="text"
                            name="subjects_taught"
                            value={data.subjects_taught}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="Mathematics, Science, English"
                            onChange={(e) => setData('subjects_taught', e.target.value)}
                            required
                        />
                        {errors.subjects_taught && (
                            <p className="mt-2 text-sm text-rose-400">{errors.subjects_taught}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Years of Experience
                        </label>
                        <input
                            type="number"
                            name="years_of_experience"
                            value={data.years_of_experience}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="5"
                            min="0"
                            max="50"
                            onChange={(e) => setData('years_of_experience', e.target.value)}
                            required
                        />
                        {errors.years_of_experience && (
                            <p className="mt-2 text-sm text-rose-400">{errors.years_of_experience}</p>
                        )}
                    </div>
                </div>

                {/* Password Fields */}
                <div className="space-y-4">
                    <h3 className="text-lg font-semibold text-white/90">Security</h3>
                    
                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Password
                        </label>
                        <input
                            type="password"
                            name="password"
                            value={data.password}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="••••••••"
                            autoComplete="new-password"
                            onChange={(e) => setData('password', e.target.value)}
                            required
                        />
                        {errors.password && (
                            <p className="mt-2 text-sm text-rose-400">{errors.password}</p>
                        )}
                    </div>

                    <div>
                        <label className="block text-sm font-semibold text-white/80 mb-2">
                            Confirm Password
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            value={data.password_confirmation}
                            className="dark-input w-full px-4 py-3 rounded-xl"
                            placeholder="••••••••"
                            autoComplete="new-password"
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            required
                        />
                        {errors.password_confirmation && (
                            <p className="mt-2 text-sm text-rose-400">{errors.password_confirmation}</p>
                        )}
                    </div>
                </div>

                {/* Submit Button */}
                <button
                    type="submit"
                    disabled={processing}
                    className="w-full bg-gradient-to-r from-indigo-500 to-rose-500 text-white py-3 rounded-xl font-semibold hover:from-indigo-600 hover:to-rose-600 transition-all duration-200 shadow-lg shadow-indigo-500/30 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {processing ? 'Creating account...' : 'Create Account'}
                </button>
            </form>

            {/* Login Link */}
            <div className="mt-6 text-center">
                <p className="text-white/60 text-sm">
                    Already have an account?{' '}
                    <Link
                        href={route('login')}
                        className="text-indigo-400 hover:text-indigo-300 font-semibold"
                    >
                        Sign in
                    </Link>
                </p>
            </div>
        </GuestLayout>
    );
}
