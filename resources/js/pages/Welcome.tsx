import React, { useEffect, useState } from 'react';
import { HeroGeometric } from '@/components/ui/shape-landing-hero';
import { motion, useReducedMotion } from 'framer-motion';
import {
    ArrowRight,
    ArrowUp,
    BarChart3,
    BookOpen,
    CalendarCheck,
    Check,
    ChevronDown,
    ClipboardCheck,
    FileCheck,
    Lock,
    Mail,
    MapPin,
    Menu,
    MessagesSquare,
    Moon,
    School,
    Settings,
    ShieldCheck,
    Sparkles,
    Sun,
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
    { id: 'faq', label: 'FAQ' },
    { id: 'contact', label: 'Contact' },
];

const SECTION_IDS = ['about', 'how-it-works', 'features', 'roles', 'faq', 'contact'];

const FRAMEWORKS = ['COT-Aligned', 'PPST-Based', 'PPSSH-Ready', 'IPCRF-Friendly'];

const STATS = [
    { value: '4-step', label: 'Guided observation cycle' },
    { value: '6', label: 'Core supervision tools' },
    { value: '4', label: 'Dedicated role portals' },
    { value: '100%', label: 'Audit-logged records' },
];

const STEPS = [
    {
        icon: <CalendarCheck className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        step: 'Step 1',
        title: 'Schedule the observation',
        desc: 'Supervisors set classroom observations, pre-conferences, and post-conferences on a shared calendar. Teachers confirm their schedules in one click.',
    },
    {
        icon: <ClipboardCheck className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        step: 'Step 2',
        title: 'Observe with digital COT',
        desc: 'Rate classroom practice on standardized COT rubrics with real-time scoring, evidence capture, and guided indicators — no more paper forms.',
    },
    {
        icon: <Sparkles className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        step: 'Step 3',
        title: 'Get AI-assisted feedback',
        desc: 'ASPIRE analyzes ratings and evidence to draft strengths, growth areas, and coaching recommendations the supervisor reviews and refines.',
    },
    {
        icon: <TrendingUp className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        step: 'Step 4',
        title: 'Coach and track growth',
        desc: 'Post-conference agreements, ratings history, and analytics dashboards turn every observation cycle into measurable professional growth.',
    },
];

const FEATURES = [
    {
        icon: <ClipboardCheck className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        title: 'Digital COT Evaluation',
        desc: 'Standardized Classroom Observation Tool rubrics with real-time scoring, indicator-level guidance, and printable COT documents.',
    },
    {
        icon: <MessagesSquare className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
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
        icon: <BookOpen className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        title: 'Teachers',
        loginAs: 'Teacher',
        desc: 'Confirm schedules, upload lesson plans, receive clear feedback, and track your growth across every observation cycle.',
        points: ['One-click schedule confirmation', 'Structured, transparent feedback', 'Personal growth analytics'],
    },
    {
        icon: <Users className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
        title: 'Supervisors',
        loginAs: 'Supervisor',
        desc: 'Plan observations, rate with guided COT rubrics, and produce consistent, well-documented coaching support.',
        points: ['Shared observation calendar', 'Guided COT rating workflow', 'AI-drafted recommendations'],
    },
    {
        icon: <School className="h-6 w-6 text-emerald-600" />,
        title: 'School Heads',
        loginAs: 'School Head',
        desc: 'Oversee instructional supervision in your school — monitor cycles, review reports, and support your teachers.',
        points: ['School-wide supervision view', 'PPSSH-aligned observation flow', 'Performance summaries'],
    },
    {
        icon: <Settings className="h-6 w-6 text-amber-600" />,
        title: 'Administrators',
        loginAs: 'Admin',
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

const FAQS = [
    {
        q: 'How do I get an ASPIRE account?',
        a: 'Accounts are provisioned by your school or division administrator. Sign in with your work email once your account is created — if you are locked out or signing in for the first time, coordinate with your school ICT coordinator, or send us an inquiry through the contact form below.',
    },
    {
        q: 'Who has the final say on ratings and feedback?',
        a: 'The supervisor, always. ASPIRE drafts strengths, growth areas, and recommendations from ratings and evidence, but every rating is confirmed and every feedback note is reviewed by a human before it reaches the teacher.',
    },
    {
        q: 'Which DepEd frameworks does ASPIRE follow?',
        a: 'Observation workflows use COT indicators and stay aligned with PPST career stages, PPSSH guidance for school heads, and IPCRF-related reporting — so evidence collected in ASPIRE maps to the documents schools already use.',
    },
    {
        q: 'What happens if our connection is slow during an observation?',
        a: 'ASPIRE autosaves your progress as you work. For observations in low-connectivity areas, coordinate with your administrator about the best setup for your school before the visit.',
    },
    {
        q: 'Is observation data kept private?',
        a: 'Yes. Records are restricted by role — teachers see their own cycles, supervisors see their assignees — and every view and change is recorded in an audit trail. Observation data is treated as confidential personnel information.',
    },
];

function Eyebrow({ children }: { children: React.ReactNode }): React.JSX.Element {
    return (
        <p className="mb-4 text-xs font-bold uppercase tracking-[0.2em] text-[#0B3D91] dark:text-blue-300">
            {children}
        </p>
    );
}

function SectionHeading({
    id,
    eyebrow,
    title,
    desc,
}: {
    id: string;
    eyebrow: string;
    title: string;
    desc: string;
}): React.JSX.Element {
    return (
        <motion.div
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.8 }}
            className="mx-auto mb-12 max-w-3xl text-center md:mb-16"
        >
            <Eyebrow>{eyebrow}</Eyebrow>
            <h2 id={id} className="scroll-mt-32 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl dark:text-white">
                {title}
            </h2>
            <p className="mt-4 text-base leading-relaxed text-slate-600 md:text-lg dark:text-slate-300">
                {desc}
            </p>
        </motion.div>
    );
}

function CyclePreview(): React.JSX.Element {
    return (
        <div
            className="mx-auto mt-10 w-full max-w-2xl overflow-hidden rounded-2xl border border-blue-100 bg-white/90 text-left shadow-xl shadow-blue-900/10 backdrop-blur dark:border-white/10 dark:bg-slate-900/90 dark:shadow-none"
            aria-label="Preview of an observation cycle in ASPIRE"
        >
            <div className="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3.5 dark:border-white/10">
                <div className="flex items-center gap-2.5">
                    <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[#0B3D91] text-xs font-bold text-white">
                        COT
                    </span>
                    <div>
                        <p className="text-sm font-semibold text-slate-900 dark:text-white">Grade 8 Science — Observation #3</p>
                        <p className="text-xs text-slate-500 dark:text-slate-400">Pre-conference done • Scheduled Tue 9:00 AM</p>
                    </div>
                </div>
                <span className="hidden shrink-0 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-emerald-200 sm:inline-block dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">
                    On track
                </span>
            </div>
            <div className="grid gap-3 px-5 py-4 sm:grid-cols-3">
                {[
                    { label: 'Schedule confirmed', state: 'Done', tone: 'emerald' },
                    { label: 'COT rating • 6 of 8', state: 'In progress', tone: 'blue' },
                    { label: 'Post-conference', state: 'Queued', tone: 'slate' },
                ].map((s) => (
                    <div key={s.label} className="rounded-xl border border-slate-100 bg-slate-50/70 px-3.5 py-3 dark:border-white/10 dark:bg-white/5">
                        <p className="text-xs font-semibold text-slate-900 dark:text-white">{s.label}</p>
                        <p className="mt-1 text-[11px] font-medium text-slate-500 dark:text-slate-400">{s.state}</p>
                        <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-200 dark:bg-white/10">
                            <div
                                className={`h-full rounded-full ${s.tone === 'emerald' ? 'w-full bg-emerald-500' : s.tone === 'blue' ? 'w-3/4 bg-[#0B3D91] dark:bg-blue-400' : 'w-1/4 bg-slate-300 dark:bg-slate-500'}`}
                            />
                        </div>
                    </div>
                ))}
            </div>
            <div className="flex flex-wrap items-center gap-2 border-t border-slate-100 px-5 py-3.5 dark:border-white/10">
                {['Domain 2: 5.8', 'Domain 3: 6.1', 'Evidence attached'].map((chip) => (
                    <span key={chip} className="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                        <Check className="h-3 w-3 text-emerald-600" />
                        {chip}
                    </span>
                ))}
            </div>
        </div>
    );
}

function FaqItem({ q, a, index }: { q: string; a: string; index: number }): React.JSX.Element {
    const [open, setOpen] = useState(index === 0);
    return (
        <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white transition-colors dark:border-white/10 dark:bg-white/5">
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition-colors hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#0B3D91] dark:hover:bg-white/5"
            >
                <span className="text-[15px] font-semibold text-slate-900 dark:text-white">{q}</span>
                <span className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 text-slate-500 transition-transform duration-300 dark:border-white/10 dark:text-slate-300 ${open ? 'rotate-180 bg-[#0B3D91] text-white dark:bg-white dark:text-slate-900' : ''}`}>
                    <ChevronDown className="h-4 w-4" />
                </span>
            </button>
            {open && (
                <div className="border-t border-slate-100 px-5 py-4 dark:border-white/10">
                    <p className="text-sm leading-relaxed text-slate-600 dark:text-slate-300">{a}</p>
                </div>
            )}
        </div>
    );
}

export default function LandingPage(): React.JSX.Element {
    const [menuOpen, setMenuOpen] = useState(false);
    const [isDark, setIsDark] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const [activeId, setActiveId] = useState<string>('');
    const [showTop, setShowTop] = useState(false);
    const reduceMotion = useReducedMotion();
    const year = new Date().getFullYear();

    useEffect(() => {
        setIsDark(document.documentElement.classList.contains('dark'));
    }, []);

    useEffect(() => {
        const onScroll = (): void => {
            setScrolled(window.scrollY > 8);
            setShowTop(window.scrollY > 900);
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) setActiveId(entry.target.id);
                }
            },
            { rootMargin: '-40% 0px -55% 0px', threshold: 0 },
        );
        SECTION_IDS.forEach((id) => {
            const el = document.getElementById(id);
            if (el) observer.observe(el);
        });
        return () => observer.disconnect();
    }, []);

    useEffect(() => {
        if (!menuOpen) return;
        const onKey = (e: KeyboardEvent): void => {
            if (e.key === 'Escape') setMenuOpen(false);
        };
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [menuOpen]);

    const toggleTheme = (): void => {
        const root = document.documentElement;
        const next = !root.classList.contains('dark');
        root.classList.toggle('dark', next);
        localStorage.setItem('theme', next ? 'dark' : 'light');
        setIsDark(next);
    };

    const goToLogin = (role?: string): void => {
        window.location.href = role ? `/login?role=${encodeURIComponent(role)}` : '/login';
    };

    const scrollToTop = (): void => {
        window.scrollTo({ behavior: reduceMotion ? 'auto' : 'smooth', top: 0 });
    };

    return (
        <div className="relative w-full bg-white text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
            {/* DepEd gov top strip */}
            <div className="bg-[#0A2A6B] px-6 py-1.5 text-center sm:text-left">
                <p className="mx-auto max-w-7xl text-[11px] font-medium tracking-wide text-blue-100">
                    Republic of the Philippines &nbsp;•&nbsp; Department of Education &nbsp;•&nbsp; ASPIRE Supervision Platform
                </p>
            </div>
            {/* Header Navigation */}
            <motion.header
                initial={reduceMotion ? false : { opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                className={`sticky top-0 z-50 border-b bg-white/90 backdrop-blur-md transition-shadow dark:bg-slate-950/85 ${scrolled ? 'border-blue-100 shadow-[0_8px_30px_-12px_rgba(11,61,145,0.25)] dark:border-white/10' : 'border-blue-100/70 dark:border-white/10'}`}
            >
                <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-3.5">
                    {/* Brand */}
                    <a href="#main-content" className="flex items-center gap-3 rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91]" aria-label="ASPIRE home">
                        <img
                            src="/images/whitelogotheme.jpg"
                            alt="ASPIRE — Learn • Grow • Serve"
                            className="h-10 w-auto rounded-md border border-blue-100 object-contain dark:border-white/10"
                            loading="eager"
                        />
                    </a>

                    {/* Desktop nav */}
                    <nav className="hidden items-center gap-1 lg:flex" aria-label="Primary">
                        {NAV_LINKS.map((link) => (
                            <a
                                key={link.id}
                                href={`#${link.id}`}
                                aria-current={activeId === link.id ? 'true' : undefined}
                                className={`rounded-full px-4 py-2 text-sm font-medium transition-colors duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] ${activeId === link.id ? 'bg-blue-50 text-[#0B3D91] dark:bg-white/10 dark:text-white' : 'text-slate-600 hover:bg-slate-100 hover:text-[#0B3D91] dark:text-slate-300 dark:hover:bg-white/5 dark:hover:text-white'}`}
                            >
                                {link.label}
                            </a>
                        ))}
                    </nav>

                    <div className="hidden items-center gap-3 lg:flex">
                        <button
                            type="button"
                            onClick={toggleTheme}
                            title={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
                            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
                            className="inline-flex h-10 w-10 items-center justify-center rounded-full border border-blue-100 text-slate-600 transition-colors hover:bg-blue-50 hover:text-[#0B3D91] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/10 dark:hover:text-white"
                        >
                            {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
                        </button>
                        <button
                            onClick={() => goToLogin()}
                            className="group inline-flex items-center gap-2 rounded-full bg-[#0B3D91] px-6 py-2.5 text-sm font-semibold text-white shadow-md shadow-blue-900/20 transition-all duration-300 hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] focus-visible:ring-offset-2 dark:bg-white dark:text-slate-900 dark:shadow-none dark:hover:bg-blue-100"
                        >
                            Log In
                            <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-0.5" />
                        </button>
                    </div>

                    {/* Mobile controls */}
                    <div className="flex items-center gap-1 lg:hidden">
                        <button
                            type="button"
                            onClick={toggleTheme}
                            title={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
                            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
                            className="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-white/10"
                        >
                            {isDark ? <Sun className="h-5 w-5" /> : <Moon className="h-5 w-5" />}
                        </button>
                        <button
                            type="button"
                            onClick={() => setMenuOpen((v) => !v)}
                            className="inline-flex h-10 w-10 items-center justify-center rounded-lg text-slate-700 hover:bg-blue-50 dark:text-slate-200 dark:hover:bg-white/10"
                            aria-label={menuOpen ? 'Close menu' : 'Open menu'}
                            aria-expanded={menuOpen}
                        >
                            {menuOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
                        </button>
                    </div>
                </div>

                {/* Mobile menu */}
                {menuOpen && (
                    <motion.nav
                        initial={reduceMotion ? false : { opacity: 0, height: 0 }}
                        animate={{ opacity: 1, height: 'auto' }}
                        transition={{ duration: 0.25 }}
                        className="overflow-hidden border-t border-blue-100 bg-white px-6 py-4 dark:border-white/10 dark:bg-slate-950 lg:hidden"
                        aria-label="Mobile"
                    >
                        <div className="flex flex-col gap-1">
                            {NAV_LINKS.map((link) => (
                                <a
                                    key={link.id}
                                    href={`#${link.id}`}
                                    onClick={() => setMenuOpen(false)}
                                    aria-current={activeId === link.id ? 'true' : undefined}
                                    className={`rounded-lg px-3 py-2.5 text-sm font-medium ${activeId === link.id ? 'bg-blue-50 text-[#0B3D91] dark:bg-white/10 dark:text-white' : 'text-slate-700 hover:bg-blue-50 hover:text-[#0B3D91] dark:text-slate-200 dark:hover:bg-white/10 dark:hover:text-white'}`}
                                >
                                    {link.label}
                                </a>
                            ))}
                            <button
                                onClick={() => goToLogin()}
                                className="mt-2 inline-flex items-center justify-center gap-2 rounded-full bg-[#0B3D91] px-6 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-slate-900"
                            >
                                Log In <ArrowRight className="h-4 w-4" />
                            </button>
                        </div>
                    </motion.nav>
                )}
            </motion.header>

            <main id="main-content">
                {/* Hero Section */}
                <section aria-label="Introduction">
                    <HeroGeometric
                        badge="AI-Powered Instructional Supervision"
                        title1="ASPIRE"
                        title2="Automated Supervision Platform for Instructional Reform & Excellence"
                        description="Move classroom observation from paper forms to a guided digital cycle — schedule, observe with COT rubrics, receive AI-assisted feedback, and grow with every cycle."
                        actions={
                            <motion.div
                                initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ delay: 1.1, duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                                className="flex flex-col items-center gap-5"
                            >
                                <div className="flex flex-wrap items-center justify-center gap-3 sm:gap-4">
                                    <button
                                        onClick={() => goToLogin()}
                                        className="group inline-flex items-center gap-2 rounded-full bg-[#0B3D91] px-7 py-3.5 text-sm font-semibold text-white shadow-xl shadow-blue-900/20 transition-all duration-300 hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] focus-visible:ring-offset-2 dark:bg-white dark:text-slate-900 dark:shadow-none dark:hover:bg-blue-100"
                                    >
                                        Log in to your portal
                                        <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                                    </button>
                                    <a
                                        href="#how-it-works"
                                        className="inline-flex items-center gap-2 rounded-full border border-blue-200 bg-white px-7 py-3.5 text-sm font-semibold text-[#0B3D91] shadow-sm transition-all duration-300 hover:border-blue-400 hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] dark:border-white/20 dark:bg-transparent dark:text-white dark:shadow-none dark:hover:border-white/40 dark:hover:bg-white/10"
                                    >
                                        See how it works
                                    </a>
                                </div>
                                <div
                                    className="flex flex-wrap items-center justify-center gap-2"
                                    aria-label="Supported frameworks"
                                >
                                    {FRAMEWORKS.map((f) => (
                                        <span
                                            key={f}
                                            className="inline-flex items-center gap-1.5 rounded-full border border-blue-100 bg-blue-50 px-3 py-1 text-[11px] font-medium tracking-wide text-blue-900 dark:border-white/10 dark:bg-white/5 dark:text-slate-300"
                                        >
                                            <Check className="h-3 w-3 text-emerald-600" />
                                            {f}
                                        </span>
                                    ))}
                                </div>
                                <CyclePreview />
                            </motion.div>
                        }
                    />
                </section>

                {/* Stats strip */}
                <section className="border-y border-blue-100 bg-white px-6 py-10 dark:border-white/10 dark:bg-slate-950" aria-label="Platform at a glance">
                    <div className="mx-auto grid max-w-6xl grid-cols-2 gap-6 text-center lg:grid-cols-4">
                        {STATS.map((s) => (
                            <div key={s.label}>
                                <p className="text-3xl font-extrabold tracking-tight text-[#0B3D91] md:text-4xl dark:text-white">{s.value}</p>
                                <p className="mt-1 text-sm text-slate-600 dark:text-slate-300">{s.label}</p>
                            </div>
                        ))}
                    </div>
                </section>

                {/* Purpose Section */}
                <section id="about" className="relative scroll-mt-24 bg-white px-6 py-20 md:py-24 dark:bg-slate-950" aria-labelledby="about-heading">
                    <div className="mx-auto max-w-7xl">
                        <SectionHeading
                            id="about-heading"
                            eyebrow="Why ASPIRE exists"
                            title="Supervision that helps teachers grow"
                            desc="Teacher observation in the Philippines still runs on scattered paperwork — ratings disconnected from feedback, feedback disconnected from coaching. ASPIRE unifies the entire supervision cycle in one platform, so every classroom visit produces fair ratings, clear guidance, and measurable growth."
                        />

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
                            {[
                                {
                                    icon: <BookOpen className="h-7 w-7 text-[#0B3D91] dark:text-blue-300" />,
                                    tile: 'bg-blue-50',
                                    title: 'Our Mission',
                                    desc: 'To give every supervisor and educator data-driven insight and AI-assisted feedback that turn routine observations into continuous instructional improvement.',
                                },
                                {
                                    icon: <TrendingUp className="h-7 w-7 text-amber-600" />,
                                    tile: 'bg-amber-50',
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
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.1, duration: 0.6 }}
                                    className="rounded-2xl border border-slate-200 bg-slate-50/60 p-8 transition-shadow duration-300 hover:shadow-lg hover:shadow-slate-200 dark:border-white/10 dark:bg-white/5 dark:hover:shadow-none"
                                >
                                    <div
                                        className={`mb-6 flex h-14 w-14 items-center justify-center rounded-xl ${card.tile} dark:bg-white/10`}
                                    >
                                        {card.icon}
                                    </div>
                                    <h3 className="mb-3 text-xl font-semibold text-slate-900 dark:text-white">
                                        {card.title}
                                    </h3>
                                    <p className="leading-relaxed text-slate-600 dark:text-slate-300">{card.desc}</p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* How It Works Section */}
                <section
                    id="how-it-works"
                    className="relative scroll-mt-24 border-y border-blue-100 bg-[#EFF6FF] px-6 py-20 md:py-24 dark:border-white/10 dark:bg-slate-900"
                    aria-labelledby="how-it-works-heading"
                >
                    <div className="mx-auto max-w-7xl">
                        <SectionHeading
                            id="how-it-works-heading"
                            eyebrow="How it works"
                            title="One cycle, from schedule to growth"
                            desc="Every observation follows the same guided path — so nothing falls through the cracks and every teacher gets a complete, documented experience."
                        />

                        <div className="relative grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div className="absolute left-0 right-0 top-12 hidden h-px bg-gradient-to-r from-transparent via-blue-300 to-transparent lg:block dark:via-white/20" aria-hidden="true" />
                            {STEPS.map((step, index) => (
                                <motion.div
                                    key={step.title}
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.1, duration: 0.6 }}
                                    className="relative rounded-2xl border border-blue-100 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-blue-300 hover:shadow-lg hover:shadow-blue-100 dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:hover:border-blue-400/40 dark:hover:shadow-none"
                                >
                                    <span className="absolute right-5 top-5 text-4xl font-bold text-blue-100 dark:text-white/10">
                                        {index + 1}
                                    </span>
                                    <div className="relative mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 ring-2 ring-white dark:bg-white/10 dark:ring-transparent">
                                        {step.icon}
                                    </div>
                                    <p className="mb-1 text-[11px] font-bold uppercase tracking-[0.18em] text-blue-700 dark:text-blue-300">
                                        {step.step}
                                    </p>
                                    <h3 className="mb-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        {step.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                        {step.desc}
                                    </p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section id="features" className="relative scroll-mt-24 bg-slate-50 px-6 py-20 md:py-24 dark:bg-slate-900/50" aria-labelledby="features-heading">
                    <div className="mx-auto max-w-7xl">
                        <SectionHeading
                            id="features-heading"
                            eyebrow="Platform features"
                            title="Everything supervision needs, in one place"
                            desc="Purpose-built tools for each stage of the observation cycle — designed with DepEd workflows in mind."
                        />

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                            {FEATURES.map((feature, index) => (
                                <motion.div
                                    key={feature.title}
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: (index % 3) * 0.1, duration: 0.6 }}
                                    className="rounded-2xl border border-blue-100 bg-white p-7 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl hover:shadow-blue-100 dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:hover:border-blue-400/40 dark:hover:shadow-none"
                                >
                                    <div className="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-white/10">
                                        {feature.icon}
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        {feature.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                        {feature.desc}
                                    </p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Roles Section */}
                <section id="roles" className="relative scroll-mt-24 bg-white px-6 py-20 md:py-24 dark:bg-slate-950" aria-labelledby="roles-heading">
                    <div className="mx-auto max-w-7xl">
                        <SectionHeading
                            id="roles-heading"
                            eyebrow="Who it's for"
                            title="A workspace for every role"
                            desc="Each role gets a dedicated portal with the tools and views that match its responsibilities."
                        />

                        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                            {ROLES.map((role, index) => (
                                <motion.div
                                    key={role.title}
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.08, duration: 0.6 }}
                                    className="flex flex-col rounded-2xl border border-blue-100 bg-white p-7 shadow-sm transition-all duration-300 hover:border-blue-200 hover:shadow-lg hover:shadow-blue-100 dark:border-white/10 dark:bg-white/5 dark:shadow-none dark:hover:border-blue-400/40"
                                >
                                    <div className="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 dark:bg-white/10">
                                        {role.icon}
                                    </div>
                                    <h3 className="mb-2 text-lg font-semibold text-slate-900 dark:text-white">
                                        {role.title}
                                    </h3>
                                    <p className="mb-4 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                        {role.desc}
                                    </p>
                                    <ul className="mb-6 space-y-2">
                                        {role.points.map((point) => (
                                            <li
                                                key={point}
                                                className="flex items-start gap-2 text-sm text-slate-600 dark:text-slate-300"
                                            >
                                                <span className="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-emerald-100">
                                                    <Check className="h-2.5 w-2.5 text-emerald-700" />
                                                </span>
                                                {point}
                                            </li>
                                        ))}
                                    </ul>
                                    <button
                                        onClick={() => goToLogin(role.loginAs)}
                                        className="mt-auto inline-flex items-center gap-1.5 rounded-lg text-sm font-semibold text-[#0B3D91] hover:text-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] dark:text-blue-300 dark:hover:text-white"
                                    >
                                        Log in as {role.loginAs}
                                        <ArrowRight className="h-3.5 w-3.5" />
                                    </button>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Benefits strip — DepEd brand band */}
                <section
                    className="relative overflow-hidden bg-gradient-to-r from-[#0A2A6B] via-[#0B3D91] to-[#0A2A6B] px-6 py-16 md:py-20"
                    aria-labelledby="benefits-heading"
                >
                    {/* tricolor accent line */}
                    <div className="absolute inset-x-0 top-0 flex h-1" aria-hidden="true">
                        <div className="flex-1 bg-[#0B3D91]" />
                        <div className="flex-1 bg-[#CE1126]" />
                        <div className="flex-1 bg-[#FCD116]" />
                    </div>
                    <div className="mx-auto max-w-7xl">
                        <motion.div
                            initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ duration: 0.7 }}
                            className="mb-10 text-center"
                        >
                            <h2 id="benefits-heading" className="text-3xl font-bold tracking-tight text-white md:text-4xl">
                                Why schools choose ASPIRE
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-sm text-blue-100">
                                Built for DepEd workflows — COT, PPST, PPSSH, and IPCRF aligned.
                            </p>
                        </motion.div>
                        <div className="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            {BENEFITS.map((benefit, index) => (
                                <motion.div
                                    key={benefit.title}
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.08, duration: 0.6 }}
                                    className="rounded-2xl border border-white/25 bg-white/10 p-6 backdrop-blur-sm"
                                >
                                    <h3 className="mb-2 text-lg font-semibold text-white">
                                        {benefit.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-blue-100">
                                        {benefit.desc}
                                    </p>
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* FAQ Section */}
                <section id="faq" className="relative scroll-mt-24 bg-slate-50 px-6 py-20 md:py-24 dark:bg-slate-900/50" aria-labelledby="faq-heading">
                    <div className="mx-auto max-w-3xl">
                        <SectionHeading
                            id="faq-heading"
                            eyebrow="Questions, answered"
                            title="Frequently asked questions"
                            desc="The essentials for teachers, supervisors, and school heads getting started with ASPIRE."
                        />
                        <div className="space-y-3">
                            {FAQS.map((f, i) => (
                                <FaqItem key={f.q} q={f.q} a={f.a} index={i} />
                            ))}
                        </div>
                    </div>
                </section>

                {/* Contact Section */}
                <section id="contact" className="relative scroll-mt-24 bg-white px-6 py-20 md:py-24 dark:bg-slate-950" aria-labelledby="contact-heading">
                    <div className="mx-auto max-w-7xl">
                        <SectionHeading
                            id="contact-heading"
                            eyebrow="Get in touch"
                            title="Contact us"
                            desc="Questions about the platform, support requests, or partnership inquiries — our team is ready to help. For account access, please coordinate with your school administrator."
                        />

                        <div className="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-3">
                            {[
                                {
                                    icon: <Mail className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
                                    title: 'Email support',
                                    body: 'support@aspire.edu.ph',
                                    href: 'mailto:support@aspire.edu.ph',
                                    cta: 'Send an email',
                                },
                                {
                                    icon: <MessagesSquare className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
                                    title: 'Contact form',
                                    body: 'No account yet? Send an inquiry as a teacher, school head, or supervisor.',
                                    href: '/contact',
                                    cta: 'Open contact form',
                                },
                                {
                                    icon: <MapPin className="h-6 w-6 text-[#0B3D91] dark:text-blue-300" />,
                                    title: 'School',
                                    body: 'Sagay National High School, Sagay City, Philippines',
                                    href: undefined,
                                    cta: undefined,
                                },
                            ].map((item, index) => (
                                <motion.div
                                    key={item.title}
                                    initial={reduceMotion ? false : { opacity: 0, y: 20 }}
                                    whileInView={{ opacity: 1, y: 0 }}
                                    viewport={{ once: true }}
                                    transition={{ delay: index * 0.1, duration: 0.6 }}
                                    className="flex flex-col items-center rounded-2xl border border-blue-100 bg-white p-8 text-center shadow-sm dark:border-white/10 dark:bg-white/5 dark:shadow-none"
                                >
                                    <div className="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 dark:bg-white/10">
                                        {item.icon}
                                    </div>
                                    <h3 className="mb-2 font-semibold text-slate-900 dark:text-white">
                                        {item.title}
                                    </h3>
                                    <p className="text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                                        {item.body}
                                    </p>
                                    {item.href && (
                                        <a
                                            href={item.href}
                                            className="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-[#0B3D91] hover:text-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] dark:text-blue-300 dark:hover:text-white"
                                        >
                                            {item.cta}
                                            <ArrowRight className="h-3.5 w-3.5" />
                                        </a>
                                    )}
                                </motion.div>
                            ))}
                        </div>
                    </div>
                </section>

                {/* Privacy note */}
                <section
                    id="privacy-policy"
                    className="relative scroll-mt-24 border-t border-blue-100 bg-[#F8FAFF] px-6 py-16 dark:border-white/10 dark:bg-slate-900"
                    aria-labelledby="privacy-heading"
                >
                    <div className="mx-auto flex max-w-4xl flex-col items-center gap-5 text-center">
                        <span className="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-blue-100 dark:bg-white/10 dark:ring-white/10 dark:shadow-none">
                            <Lock className="h-6 w-6 text-slate-700 dark:text-slate-200" />
                        </span>
                        <h2 id="privacy-heading" className="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            Privacy & data protection
                        </h2>
                        <p className="max-w-2xl leading-relaxed text-slate-600 dark:text-slate-300">
                            Observation records, ratings, and feedback are treated as
                            confidential personnel data. ASPIRE stores them securely,
                            restricts access by role, and keeps a full audit trail of
                            every view and change — so professional information stays
                            professional.
                        </p>
                    </div>
                </section>

                {/* Final CTA */}
                <section className="relative overflow-hidden bg-[#0A2A6B] px-6 py-16 md:py-20" aria-labelledby="cta-heading">
                    <div className="absolute inset-0 bg-gradient-to-r from-[#0A2A6B] via-[#0B3D91] to-[#0A2A6B]" aria-hidden="true" />
                    <div className="absolute -left-20 top-0 h-64 w-64 rounded-full bg-blue-500/30 blur-3xl" aria-hidden="true" />
                    <div className="absolute -right-20 bottom-0 h-64 w-64 rounded-full bg-sky-400/20 blur-3xl" aria-hidden="true" />
                    <div className="relative mx-auto flex max-w-4xl flex-col items-center text-center">
                        <h2 id="cta-heading" className="text-3xl font-bold tracking-tight text-white md:text-4xl">
                            Ready to move beyond paper forms?
                        </h2>
                        <p className="mt-3 max-w-2xl text-[15px] leading-relaxed text-blue-100">
                            Sign in to schedule your next observation, or revisit how the guided cycle works.
                        </p>
                        <div className="mt-7 flex flex-wrap items-center justify-center gap-3">
                            <button
                                onClick={() => goToLogin()}
                                className="group inline-flex items-center gap-2 rounded-full bg-white px-7 py-3.5 text-sm font-semibold text-[#0A2A6B] shadow-lg transition hover:bg-blue-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white focus-visible:ring-offset-2 focus-visible:ring-offset-[#0A2A6B]"
                            >
                                Log in to your portal
                                <ArrowRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
                            </button>
                            <a
                                href="#how-it-works"
                                className="inline-flex items-center gap-2 rounded-full border border-white/30 px-7 py-3.5 text-sm font-semibold text-white transition hover:border-white/60 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                            >
                                Revisit the cycle
                            </a>
                        </div>
                    </div>
                </section>
            </main>

            {/* Footer — DepEd navy */}
            <footer className="relative bg-[#0A2A6B] px-6 pb-8 pt-14">
                <div className="absolute inset-x-0 top-0 flex h-1" aria-hidden="true">
                    <div className="flex-1 bg-[#0B3D91]" />
                    <div className="flex-1 bg-[#CE1126]" />
                    <div className="flex-1 bg-[#FCD116]" />
                </div>
                <div className="mx-auto max-w-7xl">
                    <div className="grid grid-cols-1 gap-10 md:grid-cols-3">
                        <div>
                            <div className="mb-4 flex items-center gap-3">
                                <img
                                    src="/images/whitelogotheme.jpg"
                                    alt="ASPIRE — Learn • Grow • Serve"
                                    className="h-12 w-auto rounded-md border border-white/20 object-contain"
                                    loading="lazy"
                                />
                            </div>
                            <p className="max-w-xs text-sm leading-relaxed text-blue-100">
                                Automated Supervision Platform for Instructional Reform
                                & Excellence — digitizing classroom observation for
                                Philippine schools.
                            </p>
                            <div className="mt-4 flex flex-wrap gap-2" aria-label="Supported frameworks">
                                {FRAMEWORKS.map((f) => (
                                    <span key={f} className="rounded-full border border-white/20 px-2.5 py-1 text-[11px] font-medium text-blue-100">
                                        {f}
                                    </span>
                                ))}
                            </div>
                        </div>
                        <nav aria-label="Footer">
                            <h3 className="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-blue-200">
                                Explore
                            </h3>
                            <ul className="space-y-2.5">
                                {[
                                    ...NAV_LINKS,
                                    { id: 'privacy-policy', label: 'Privacy Policy' },
                                ].map((link) => (
                                    <li key={link.id}>
                                        <a
                                            href={`#${link.id}`}
                                            className="rounded text-sm text-blue-100 transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                                        >
                                            {link.label}
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        </nav>
                        <div>
                            <h3 className="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-blue-200">
                                Portals
                            </h3>
                            <ul className="space-y-2.5">
                                {['Teacher', 'Supervisor', 'School Head', 'Administrator'].map(
                                    (portal) => (
                                        <li key={portal}>
                                            <button
                                                onClick={() => goToLogin(portal)}
                                                className="group inline-flex items-center gap-1.5 rounded text-sm text-blue-100 transition-colors hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
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
                    <div className="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/20 pt-6 text-center sm:flex-row sm:text-left">
                        <p className="text-xs text-blue-200">
                            © {year} ASPIRE • Automated Supervision Platform for
                            Instructional Reform & Excellence
                        </p>
                        <button
                            onClick={scrollToTop}
                            className="inline-flex items-center gap-1.5 rounded-full border border-white/25 px-4 py-1.5 text-xs font-semibold text-blue-100 transition hover:border-white/50 hover:text-white focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white"
                        >
                            <ArrowUp className="h-3.5 w-3.5" />
                            Back to top
                        </button>
                    </div>
                </div>
            </footer>

            {/* Back to top floating */}
            {showTop && (
                <button
                    onClick={scrollToTop}
                    aria-label="Back to top"
                    className="fixed bottom-6 right-6 z-50 inline-flex h-11 w-11 items-center justify-center rounded-full bg-[#0B3D91] text-white shadow-xl shadow-blue-900/30 transition hover:bg-blue-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0B3D91] focus-visible:ring-offset-2 dark:bg-white dark:text-slate-900 dark:hover:bg-blue-100"
                >
                    <ArrowUp className="h-5 w-5" />
                </button>
            )}
        </div>
    );
}
