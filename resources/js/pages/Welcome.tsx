import React, { useState } from 'react';
import { HeroGeometric } from '@/components/ui/shape-landing-hero';
import { motion } from 'framer-motion';
import {
    ArrowRight,
    BarChart3,
    BookOpen,
    CalendarCheck,
    Check,
    ClipboardCheck,
    FileCheck,
    GraduationCap,
    Lock,
    Mail,
    MapPin,
    Menu,
    MessagesSquare,
    Phone,
    School,
    Settings,
    ShieldCheck,
    Sparkles,
    TrendingUp,
    Users,
    X,
} from 'lucide-react';

/**
 * ASPIRE Landing Page
 * Communicates the purpose, features, and benefits of the
 * Automated Supervision Platform for Instructional Reform & Excellence.
 */

const NAV_LINKS = [
    { id: 'about', label: 'Purpose' },
    { id: 'how-it-works', label: 'How It Works' },
    { id: 'features', label: 'Features' },
    { id: 'roles', label: "Who It's For" },
    { id: 'contact', label: 'Contact' },
];

const FRAMEWORKS = ['COT-Aligned', 'PPST-Based', 'PPSSH-Ready', 'IPCRF-Friendly'];

const STEPS = [
    {
        icon: <CalendarCheck className="h-6 w-6 text-indigo-600" />,
        step: 'Step 1',
        title: 'Schedule the observation',
        desc: 'Supervisors set classroom observations, pre-conferences, and post-conferences on a shared calendar. Teachers confirm their schedules in one click.',
    },
    {
        icon: <ClipboardCheck className="h-6 w-6 text-indigo-600" />,
        step: 'Step 2',
        title: 'Observe with digital COT',
        desc: 'Rate classroom practice on standardized COT rubrics with real-time scoring, evidence capture, and guided indicators — no more paper forms.',
    },
    {
        icon: <Sparkles className="h-6 w-6 text-indigo-600" />,
        step: 'Step 3',
        title: 'Get AI-assisted feedback',
        desc: 'ASPIRE analyzes ratings and evidence to draft strengths, growth areas, and coaching recommendations the supervisor reviews and refines.',
    },
    {
        icon: <TrendingUp className="h-6 w-6 text-indigo-600" />,
        step: 'Step 4',
        title: 'Coach and track growth',
        desc: 'Post-conference agreements, ratings history, and analytics dashboards turn every observation cycle into measurable professional growth.',
    },
];

const FEATURES = [
    {
        icon: <ClipboardCheck className="h-6 w-6 text-indigo-600" />,
        title: 'Digital COT Evaluation',
        desc: 'Standardized Classroom Observation Tool rubrics with real-time scoring, indicator-level guidance, and printable COT documents.',
    },
    {
        icon: <MessagesSquare className="h-6 w-6 text-violet-600" />,
        title: 'Pre & Post Conferences',
        desc: 'Structured conference workflows keep observers and teachers aligned before the lesson and accountable after it.',
    },
    {
        icon: <Sparkles className="h-6 w-6 text-amber-600" />,
        title: 'AI-Assisted Feedback',
        desc: 'Intelligent analysis drafts personalized strengths, needs, and recommendations — always reviewed by the supervisor, never fully automated.',
    },
    {
        icon: <BarChart3 className="h-6 w-6 text-emerald-600" />,
        title: 'Analytics & Reports',
        desc: 'Longitudinal performance trends, school-wide summaries, and exportable reports that support data-driven decisions.',
    },
    {
        icon: <Users className="h-6 w-6 text-sky-600" />,
        title: 'Role-Based Portals',
        desc: 'Dedicated workspaces for teachers, supervisors, school heads, and administrators — each person sees exactly what they need.',
    },
    {
        icon: <FileCheck className="h-6 w-6 text-rose-600" />,
        title: 'Evidence & Audit Trail',
        desc: 'Lesson plans, evidence files, observation logs, and confirmations are stored securely with a complete activity history.',
    },
];

const ROLES = [
    {
        icon: <BookOpen className="h-6 w-6 text-indigo-600" />,
        title: 'Teachers',
        desc: 'Confirm schedules, upload lesson plans, receive clear feedback, and track your growth across every observation cycle.',
        points: ['One-click schedule confirmation', 'Structured, transparent feedback', 'Personal growth analytics'],
    },
    {
        icon: <Users className="h-6 w-6 text-violet-600" />,
        title: 'Supervisors',
        desc: 'Plan observations, rate with guided COT rubrics, and produce consistent, well-documented coaching support.',
        points: ['Shared observation calendar', 'Guided COT rating workflow', 'AI-drafted recommendations'],
    },
    {
        icon: <School className="h-6 w-6 text-emerald-600" />,
        title: 'School Heads',
        desc: 'Oversee instructional supervision in your school — monitor cycles, review reports, and support your teachers.',
        points: ['School-wide supervision view', 'PPSSH-aligned observation flow', 'Performance summaries'],
    },
    {
        icon: <Settings className="h-6 w-6 text-amber-600" />,
        title: 'Administrators',
        desc: 'Manage users, schools, rubrics, and system settings with full audit logs and role-based access control.',
        points: ['User and school management', 'COT / PPST rubric control', 'System audit visibility'],
    },
];

const BENEFITS = [
    { title: 'Less paperwork', desc: 'The full cycle — scheduling to final report — lives in one system.' },
    { title: 'Fairer evaluations', desc: 'Standardized rubrics and evidence keep every rating grounded.' },
    { title: 'Faster feedback', desc: 'Draft insights in minutes, so coaching happens while it matters.' },
    { title: 'Visible growth', desc: 'Trends and history prove progress across quarters and years.' },
];

function Eyebrow({ children }: { children: React.ReactNode }): React.JSX.Element {
    return (
        <p className="text-xs font-bold uppercase tracking-[0.2em] text-indigo-600 mb-4">
            {children}
        </p>
    );
}

export default function LandingPage(): React.JSX.Element {
    const [menuOpen, setMenuOpen] = useState(false);

    const goToLogin = (): void => {
        window.location.href = '/login';
    };

    const scrollToSection = (id: string): void => {
        setMenuOpen(false);
        const element = document.getElementById(id);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <div className="relative w-full bg-[#030303] text-slate-900 antialiased">
            {/* Header Navigation */}
            <motion.header
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                className="fixed top-0 left-0 right-0 z-50 border-b border-white/10 bg-[#030303]/70 backdrop-blur-md"
            >
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
                    {/* Brand */}
                    <a href="/" className="flex items-center gap-3" aria-label="ASPIRE home">
                        <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 shadow-lg shadow-indigo-500/30">
                            <GraduationCap className="h-5 w-5 text-white" />
                        </span>
                        <span className="leading-tight">
                            <span className="block text-lg font-bold tracking-tight text-white">
                                ASPIRE
                            </span>
                            <span className="block text-[10px] font-medium uppercase tracking-[0.18em] text-white/50">
                                Instructional Excellence
                            </span>
                        </span>
                    </a>

                    {/* Desktop nav */}
                    <nav className="hidden items-center gap-7 lg:flex" aria-label="Primary">
                        {NAV_LINKS.map((link) => (
                            <button
                                key={link.id}
                                onClick={() => scrollToSection(link.id)}
                                className="text-sm font-medium text-white/70 transition-colors duration-300 hover:text-white"
                            >
                                {link.label}
                            </button>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-3 lg:flex">
                        <button
                            onClick={goToLogin}
                            className="group inline-flex items-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-slate-900 transition-all duration-300 hover:bg-indigo-100"
                        >
                            Log In
                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                        </button>
                    </div>

                    {/* Mobile toggle */}
                    <button
                        type="button"
                        onClick={() => setMenuOpen((v) => !v)}
                        className="inline-flex h-10 w-10 items-center justify-center rounded-lg text-white/80 hover:bg-white/10 lg:hidden"
                        aria-label={menuOpen ? 'Close menu' : 'Open menu'}
                        aria-expanded={menuOpen}
                    >
                        {menuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                    </button>
                </div>

                {/* Mobile menu */}
                {menuOpen && (
                    <nav
                        className="border-t border-white/10 bg-[#030303]/95 px-6 py-4 backdrop-blur-md lg:hidden"
                        aria-label="Mobile"
                    >
                        <div className="flex flex-col gap-1">
                            {NAV_LINKS.map((link) => (
                                <button
                                    key={link.id}
                                    onClick={() => scrollToSection(link.id)}
                                    className="rounded-lg px-3 py-2.5 text-left text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white"
                                >
                                    {link.label}
                                </button>
                            ))}
                            <button
                                onClick={goToLogin}
                                className="mt-2 inline-flex items-center justify-center gap-2 rounded-full bg-white px-6 py-2.5 text-sm font-semibold text-slate-900"
                            >
                                Log In <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </nav>
                )}
            </motion.header>

            {/* Hero Section */}
            <section className="relative" aria-label="Introduction">
                <HeroGeometric
                    badge="AI-Powered Instructional Supervision"
                    title1="ASPIRE"
                    title2="Automated Supervision Platform for Instructional Reform & Excellence"
                    description="Move classroom observation from paper forms to a guided digital cycle — schedule, observe with COT rubrics, receive AI-assisted feedback, and grow with every cycle."
                    actions={
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ delay: 1.1, duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                            className="flex flex-col items-center gap-5"
                        >
                            <div className="flex flex-wrap items-center justify-center gap-4">
                                <button
                                    onClick={goToLogin}
                                    className="group inline-flex items-center gap-2 rounded-full bg-white px-8 py-4 text-sm font-semibold text-slate-900 shadow-xl shadow-white/10 transition-all duration-300 hover:bg-indigo-100"
                                >
                                    Log in to your portal
                                    <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                </button>
                                <button
                                    onClick={() => scrollToSection('features')}
                                    className="inline-flex items-center gap-2 rounded-full border border-white/20 px-8 py-4 text-sm font-semibold text-white/80 transition-all duration-300 hover:border-white/40 hover:text-white"
                                >
                                    Explore the platform
                                </button>
                            </div>
                            <div
                                className="flex flex-wrap items-center justify-center gap-2"
                                aria-label="Supported frameworks"
                            >
                                {FRAMEWORKS.map((f) => (
                                    <span
                                        key={f}
                                        className="inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.04] px-3 py-1 text-[11px] font-medium tracking-wide text-white/55"
                                    >
                                        <Check className="h-3 w-3 text-emerald-400" />
                                        {f}
                                    </span>
                                ))}
                            </div>
                        </motion.div>
                    }
                />
            </section>

            {/* Purpose Section */}
            <section id="about" className="relative bg-white px-6 py-24" aria-label="Purpose">
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="mx-auto mb-16 max-w-3xl text-center"
                    >
                        <Eyebrow>Why ASPIRE exists</Eyebrow>
                        <h2 className="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
                            Supervision that helps teachers grow
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-slate-600">
                            Teacher observation in the Philippines still runs on scattered
                            paperwork — ratings disconnected from feedback, feedback
                            disconnected from coaching. ASPIRE unifies the entire
                            supervision cycle in one platform, so every classroom visit
                            produces fair ratings, clear guidance, and measurable growth.
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                        {[
                            {
                                icon: <BookOpen className="h-7 w-7 text-indigo-600" />,
                                tile: 'bg-indigo-50',
                                title: 'Our Mission',
                                desc: 'To give every supervisor and educator data-driven insight and AI-assisted feedback that turn routine observations into continuous instructional improvement.',
                            },
                            {
                                icon: <TrendingUp className="h-7 w-7 text-violet-600" />,
                                tile: 'bg-violet-50',
                                title: 'Our Vision',
                                desc: 'A school system where every teacher receives timely, personalized support — and every school advances toward excellence through intelligent supervision.',
                            },
                            {
                                icon: <ShieldCheck className="h-7 w-7 text-emerald-600" />,
                                tile: 'bg-emerald-50',
                                title: 'Our Values',
                                desc: 'Fairness first: standardized rubrics, evidence-backed ratings, and transparent feedback — with human judgment always in charge of the final call.',
                            },
                        ].map((card, index) => (
                            <motion.div
                                key={card.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.1, duration: 0.6 }}
                                className="rounded-2xl border border-slate-200 bg-slate-50/60 p-8 transition-shadow duration-300 hover:shadow-lg hover:shadow-slate-200"
                            >
                                <div
                                    className={`mb-6 flex h-14 w-14 items-center justify-center rounded-xl ${card.tile}`}
                                >
                                    {card.icon}
                                </div>
                                <h3 className="mb-3 text-xl font-semibold text-slate-900">
                                    {card.title}
                                </h3>
                                <p className="leading-relaxed text-slate-600">{card.desc}</p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* How It Works Section */}
            <section
                id="how-it-works"
                className="relative bg-slate-950 px-6 py-24"
                aria-label="How it works"
            >
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="mx-auto mb-16 max-w-3xl text-center"
                    >
                        <p className="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-indigo-400">
                            How it works
                        </p>
                        <h2 className="text-4xl font-bold tracking-tight text-white md:text-5xl">
                            One cycle, from schedule to growth
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-white/60">
                            Every observation follows the same guided path — so nothing
                            falls through the cracks and every teacher gets a complete,
                            documented experience.
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                        {STEPS.map((step, index) => (
                            <motion.div
                                key={step.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.1, duration: 0.6 }}
                                className="relative rounded-2xl border border-white/10 bg-white/[0.04] p-6 backdrop-blur-sm transition-colors duration-300 hover:border-indigo-400/40"
                            >
                                <span className="absolute right-5 top-5 text-4xl font-bold text-white/10">
                                    {index + 1}
                                </span>
                                <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-white">
                                    {step.icon}
                                </div>
                                <p className="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-indigo-300">
                                    {step.step}
                                </p>
                                <h3 className="mb-2 text-lg font-semibold text-white">
                                    {step.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-white/60">
                                    {step.desc}
                                </p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="relative bg-slate-50 px-6 py-24" aria-label="Features">
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="mx-auto mb-16 max-w-3xl text-center"
                    >
                        <Eyebrow>Platform features</Eyebrow>
                        <h2 className="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
                            Everything supervision needs, in one place
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-slate-600">
                            Purpose-built tools for each stage of the observation cycle —
                            designed with DepEd workflows in mind.
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {FEATURES.map((feature, index) => (
                            <motion.div
                                key={feature.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: (index % 3) * 0.1, duration: 0.6 }}
                                className="rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200"
                            >
                                <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                    {feature.icon}
                                </div>
                                <h3 className="mb-2 text-lg font-semibold text-slate-900">
                                    {feature.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-slate-600">
                                    {feature.desc}
                                </p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Roles Section */}
            <section id="roles" className="relative bg-white px-6 py-24" aria-label="Who it's for">
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="mx-auto mb-16 max-w-3xl text-center"
                    >
                        <Eyebrow>Who it's for</Eyebrow>
                        <h2 className="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
                            A workspace for every role
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-slate-600">
                            Each role gets a dedicated portal with the tools and views
                            that match its responsibilities.
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                        {ROLES.map((role, index) => (
                            <motion.div
                                key={role.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.08, duration: 0.6 }}
                                className="flex flex-col rounded-2xl border border-slate-200 bg-white p-7 shadow-sm transition-shadow duration-300 hover:shadow-lg hover:shadow-slate-200"
                            >
                                <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                                    {role.icon}
                                </div>
                                <h3 className="mb-2 text-lg font-semibold text-slate-900">
                                    {role.title}
                                </h3>
                                <p className="mb-4 text-sm leading-relaxed text-slate-600">
                                    {role.desc}
                                </p>
                                <ul className="mb-6 space-y-2">
                                    {role.points.map((point) => (
                                        <li
                                            key={point}
                                            className="flex items-start gap-2 text-sm text-slate-600"
                                        >
                                            <span className="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                                <Check className="h-2.5 w-2.5 text-emerald-700" />
                                            </span>
                                            {point}
                                        </li>
                                    ))}
                                </ul>
                                <button
                                    onClick={goToLogin}
                                    className="mt-auto inline-flex items-center gap-1.5 text-sm font-semibold text-indigo-700 hover:text-indigo-900"
                                >
                                    Log in as {role.title === 'Teachers' ? 'Teacher' : role.title === 'Supervisors' ? 'Supervisor' : role.title === 'School Heads' ? 'School Head' : 'Admin'}
                                    <ArrowRight className="h-3.5 w-3.5" />
                                </button>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Benefits strip */}
            <section
                className="relative overflow-hidden bg-gradient-to-r from-indigo-950 via-slate-950 to-violet-950 px-6 py-20"
                aria-label="Benefits"
            >
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.7 }}
                        className="mb-12 text-center"
                    >
                        <h2 className="text-3xl font-bold tracking-tight text-white md:text-4xl">
                            Why schools choose ASPIRE
                        </h2>
                    </motion.div>
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        {BENEFITS.map((benefit, index) => (
                            <motion.div
                                key={benefit.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.08, duration: 0.6 }}
                                className="rounded-2xl border border-white/10 bg-white/[0.05] p-6 backdrop-blur-sm"
                            >
                                <h3 className="mb-2 text-lg font-semibold text-white">
                                    {benefit.title}
                                </h3>
                                <p className="text-sm leading-relaxed text-white/60">
                                    {benefit.desc}
                                </p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section id="contact" className="relative bg-slate-50 px-6 py-24" aria-label="Contact">
                <div className="mx-auto max-w-7xl">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="mx-auto mb-14 max-w-3xl text-center"
                    >
                        <Eyebrow>Get in touch</Eyebrow>
                        <h2 className="text-4xl font-bold tracking-tight text-slate-900 md:text-5xl">
                            Contact us
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-slate-600">
                            Questions about the platform, support requests, or
                            partnership inquiries — our team is ready to help. For
                            account access, please coordinate with your school
                            administrator.
                        </p>
                    </motion.div>

                    <div className="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-3">
                        {[
                            {
                                icon: <Mail className="h-6 w-6 text-indigo-600" />,
                                title: 'Email',
                                lines: ['support@aspire.edu.ph'],
                            },
                            {
                                icon: <Phone className="h-6 w-6 text-indigo-600" />,
                                title: 'Phone',
                                lines: ['+63 (XXX) XXX-XXXX'],
                            },
                            {
                                icon: <MapPin className="h-6 w-6 text-indigo-600" />,
                                title: 'School',
                                lines: ['Sagay National High School', 'Sagay City, Philippines'],
                            },
                        ].map((item, index) => (
                            <motion.div
                                key={item.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.1, duration: 0.6 }}
                                className="flex flex-col items-center rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm"
                            >
                                <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-50">
                                    {item.icon}
                                </div>
                                <h3 className="mb-2 font-semibold text-slate-900">
                                    {item.title}
                                </h3>
                                {item.lines.map((line) => (
                                    <p key={line} className="text-sm text-slate-600">
                                        {line}
                                    </p>
                                ))}
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Privacy note */}
            <section
                id="privacy-policy"
                className="relative bg-white px-6 py-16"
                aria-label="Privacy policy"
            >
                <div className="mx-auto flex max-w-4xl flex-col items-center gap-5 text-center">
                    <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100">
                        <Lock className="h-6 w-6 text-slate-700" />
                    </span>
                    <h2 className="text-2xl font-bold tracking-tight text-slate-900">
                        Privacy & data protection
                    </h2>
                    <p className="max-w-2xl leading-relaxed text-slate-600">
                        Observation records, ratings, and feedback are treated as
                        confidential personnel data. ASPIRE stores them securely,
                        restricts access by role, and keeps a full audit trail of
                        every view and change — so professional information stays
                        professional.
                    </p>
                </div>
            </section>

            {/* Footer */}
            <footer className="relative border-t border-white/10 bg-[#030303] px-6 pb-8 pt-14">
                <div className="mx-auto max-w-7xl">
                    <div className="grid grid-cols-1 gap-10 md:grid-cols-3">
                        <div>
                            <div className="mb-4 flex items-center gap-3">
                                <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600">
                                    <GraduationCap className="h-5 w-5 text-white" />
                                </span>
                                <span className="text-lg font-bold tracking-tight text-white">
                                    ASPIRE
                                </span>
                            </div>
                            <p className="max-w-xs text-sm leading-relaxed text-white/55">
                                Automated Supervision Platform for Instructional Reform
                                & Excellence — digitizing classroom observation for
                                Philippine schools.
                            </p>
                        </div>
                        <nav aria-label="Footer">
                            <h3 className="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-white/40">
                                Explore
                            </h3>
                            <ul className="space-y-2.5">
                                {[
                                    ...NAV_LINKS,
                                    { id: 'privacy-policy', label: 'Privacy Policy' },
                                ].map((link) => (
                                    <li key={link.id}>
                                        <button
                                            onClick={() => scrollToSection(link.id)}
                                            className="text-sm text-white/65 transition-colors hover:text-white"
                                        >
                                            {link.label}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                        <div>
                            <h3 className="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-white/40">
                                Portals
                            </h3>
                            <ul className="space-y-2.5">
                                {['Teacher', 'Supervisor', 'School Head', 'Administrator'].map(
                                    (portal) => (
                                        <li key={portal}>
                                            <button
                                                onClick={goToLogin}
                                                className="group inline-flex items-center gap-1.5 text-sm text-white/65 transition-colors hover:text-white"
                                            >
                                                {portal} Login
                                                <ArrowRight className="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" />
                                            </button>
                                        </li>
                                    ),
                                )}
                            </ul>
                        </div>
                    </div>
                    <div className="mt-12 border-t border-white/10 pt-6 text-center">
                        <p className="text-xs text-white/45">
                            © 2026 ASPIRE • Automated Supervision Platform for
                            Instructional Reform & Excellence
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    );
}
