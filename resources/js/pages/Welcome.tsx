import React from 'react';
import { HeroGeometric } from '@/components/ui/shape-landing-hero';
import { motion } from 'framer-motion';
import { BookOpen, Users, BarChart3, Shield, Mail, MapPin, Phone } from 'lucide-react';

/**
 * ASPIRE Landing Page with HeroGeometric Component
 * Dark theme with animated geometric shapes
 */

export default function LandingPage(): React.JSX.Element {
    const handleLogin = (role: string): void => {
        window.location.href = `/login?role=${role}`;
    };

    const scrollToSection = (id: string): void => {
        const element = document.getElementById(id);
        if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <div className="relative w-full bg-[#030303]">
            {/* Header Navigation */}
            <motion.header
                initial={{ opacity: 0, y: -20 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                className="fixed top-0 left-0 right-0 z-50 px-6 py-4 bg-[#030303]/50 backdrop-blur-md border-b border-white/5"
            >
                <div className="max-w-7xl mx-auto flex items-center justify-between">
                    {/* Logo */}
                    <a href="/" className="text-2xl font-bold text-white tracking-tight">
                        ASPIRE
                    </a>

                    {/* Navigation Links */}
                    <nav className="hidden md:flex items-center gap-6">
                        <button 
                            onClick={() => scrollToSection('about')}
                            className="text-white/70 hover:text-white transition-colors duration-300 text-sm font-medium"
                        >
                            About Us
                        </button>
                        <button 
                            onClick={() => scrollToSection('features')}
                            className="text-white/70 hover:text-white transition-colors duration-300 text-sm font-medium"
                        >
                            Features
                        </button>
                        <button 
                            onClick={() => scrollToSection('contact')}
                            className="text-white/70 hover:text-white transition-colors duration-300 text-sm font-medium"
                        >
                            Contact
                        </button>
                          <button 
                            onClick={() => scrollToSection('privacy-policy')}
                            className="text-white/70 hover:text-white transition-colors duration-300 text-sm font-medium"
                        >
                            Privacy Policy
                        </button>
                    </nav>

                    {/* Auth Buttons */}
                    <div className="flex items-center gap-3">
                        <button
                            onClick={() => handleLogin('teacher')}
                            className="px-5 py-2 text-white/80 hover:text-white text-sm font-medium transition-colors duration-300"
                        >
                            Log In
                        </button>
                        <button
                            onClick={() => window.location.href = '/register'}
                            className="px-5 py-2 bg-white text-[#030303] text-sm font-semibold rounded-full hover:bg-white/90 transition-all duration-300"
                        >
                            Sign Up
                        </button>
                    </div>
                </div>
            </motion.header>

            {/* Hero Section */}
            <section className="relative min-h-screen">
                <HeroGeometric 
                    badge="AI-Powered Evaluation"
                    title1="ASPIRE"
                    title2="Automated Supervision Platform for Instructional Reform & Excellence"
                />

                {/* Hero Login Buttons */}
                <div className="absolute bottom-12 left-0 right-0 z-20 flex flex-col items-center gap-6">
                    <motion.div
                        initial={{ opacity: 0, y: 20 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ delay: 1.2, duration: 0.8, ease: [0.25, 0.4, 0.25, 1] }}
                        className="flex flex-wrap items-center justify-center gap-4 px-4"
                    >
                        <button
                            onClick={() => handleLogin('teacher')}
                            className="group px-8 py-4 bg-white/10 backdrop-blur-md border border-white/20 text-white font-semibold rounded-full hover:bg-white/20 hover:border-white/40 transition-all duration-300"
                        >
                            <span className="flex items-center gap-2">
                                Teacher Login
                                <svg className="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 8l4 4m0 0l-4 4m4-4H3" />
                                </svg>
                            </span>
                        </button>

                        <button
                            onClick={() => handleLogin('supervisor')}
                            className="px-8 py-4 bg-white/5 backdrop-blur-md border border-white/10 text-white/80 font-semibold rounded-full hover:bg-white/10 hover:border-white/20 transition-all duration-300"
                        >
                            Supervisor
                        </button>

                        <button
                            onClick={() => handleLogin('school-head')}
                            className="px-8 py-4 bg-white/5 backdrop-blur-md border border-white/10 text-white/80 font-semibold rounded-full hover:bg-white/10 hover:border-white/20 transition-all duration-300"
                        >
                            School Head
                        </button>
                    </motion.div>
                </div>
            </section>

            {/* About Us Section */}
            <section id="about" className="relative py-24 px-6 bg-[#030303]">
                <div className="max-w-7xl mx-auto">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="text-center mb-16"
                    >
                        <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">About Us</h2>
                        <p className="text-lg text-white/60 max-w-3xl mx-auto">
                            ASPIRE is a comprehensive digital platform revolutionizing teacher supervision 
                            and evaluation in the Philippines. We leverage AI technology to provide 
                            actionable insights and foster professional growth.
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.1, duration: 0.6 }}
                            className="p-8 rounded-2xl bg-white/[0.03] border border-white/10"
                        >
                            <div className="w-14 h-14 bg-indigo-500/20 rounded-xl flex items-center justify-center mb-6">
                                <BookOpen className="w-7 h-7 text-indigo-400" />
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-3">Our Mission</h3>
                            <p className="text-white/60">
                                To provide comprehensive support to supervisors and educators through data-driven insights and AI-assisted feedback, 
                                enabling continuous improvement in instructional excellence.
                            </p>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.2, duration: 0.6 }}
                            className="p-8 rounded-2xl bg-white/[0.03] border border-white/10"
                        >
                            <div className="w-14 h-14 bg-rose-500/20 rounded-xl flex items-center justify-center mb-6">
                                <Users className="w-7 h-7 text-rose-400" />
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-3">Our Vision</h3>
                            <p className="text-white/60">
                                A transformed educational landscape where every teacher receives 
                                personalized support and every school achieves excellence through 
                                intelligent supervision.
                            </p>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.3, duration: 0.6 }}
                            className="p-8 rounded-2xl bg-white/[0.03] border border-white/10"
                        >
                            <div className="w-14 h-14 bg-amber-500/20 rounded-xl flex items-center justify-center mb-6">
                                <Shield className="w-7 h-7 text-amber-400" />
                            </div>
                            <h3 className="text-xl font-semibold text-white mb-3">Our Values</h3>
                            <p className="text-white/60">
                                Integrity, innovation, and inclusivity guide our platform development 
                                to ensure fair and comprehensive teacher evaluation across all institutions.
                            </p>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section id="features" className="relative py-24 px-6 bg-[#0a0a0a]">
                <div className="max-w-7xl mx-auto">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="text-center mb-16"
                    >
                        <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">Key Features</h2>
                        <p className="text-lg text-white/60 max-w-3xl mx-auto">
                            Comprehensive tools designed for modern educational supervision
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        {[
                            {
                                icon: <BarChart3 className="w-6 h-6 text-cyan-400" />,
                                title: "COT Evaluation",
                                desc: "Digital Classroom Observation Tool with standardized rubrics and real-time scoring."
                            },
                            {
                                icon: <Users className="w-6 h-6 text-violet-400" />,
                                title: "AI Feedback",
                                desc: "Intelligent analysis generating personalized improvement recommendations for supervisors."
                            },
                            {
                                icon: <BookOpen className="w-6 h-6 text-rose-400" />,
                                title: "Performance Tracking",
                                desc: "Longitudinal analytics showing growth trends and predictive performance insights."
                            },
                            {
                                icon: <Shield className="w-6 h-6 text-amber-400" />,
                                title: "Role-Based Access",
                                desc: "Secure portals customized for Teachers, Supervisors, and School Administrators."
                            }
                        ].map((feature, index) => (
                            <motion.div
                                key={feature.title}
                                initial={{ opacity: 0, y: 20 }}
                                whileInView={{ opacity: 1, y: 0 }}
                                viewport={{ once: true }}
                                transition={{ delay: index * 0.1, duration: 0.6 }}
                                className="p-6 rounded-2xl bg-white/[0.02] border border-white/10 hover:bg-white/[0.05] transition-colors duration-300"
                            >
                                <div className="w-12 h-12 bg-white/[0.05] rounded-xl flex items-center justify-center mb-4">
                                    {feature.icon}
                                </div>
                                <h3 className="text-lg font-semibold text-white mb-2">{feature.title}</h3>
                                <p className="text-white/50 text-sm leading-relaxed">{feature.desc}</p>
                            </motion.div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Contact Section */}
            <section id="contact" className="relative py-24 px-6 bg-[#030303]">
                <div className="max-w-7xl mx-auto">
                    <motion.div
                        initial={{ opacity: 0, y: 30 }}
                        whileInView={{ opacity: 1, y: 0 }}
                        viewport={{ once: true }}
                        transition={{ duration: 0.8 }}
                        className="text-center mb-16"
                    >
                        <h2 className="text-4xl md:text-5xl font-bold text-white mb-6">Contact Us</h2>
                        <p className="text-lg text-white/60 max-w-3xl mx-auto">
                            Get in touch with our team for support, inquiries, or partnership opportunities
                        </p>
                    </motion.div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.1, duration: 0.6 }}
                            className="flex flex-col items-center text-center p-6"
                        >
                            <div className="w-14 h-14 bg-indigo-500/20 rounded-full flex items-center justify-center mb-4">
                                <Mail className="w-6 h-6 text-indigo-400" />
                            </div>
                            <h3 className="text-white font-semibold mb-2">Email</h3>
                            <p className="text-white/60 text-sm">support@aspire.edu.ph</p>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.2, duration: 0.6 }}
                            className="flex flex-col items-center text-center p-6"
                        >
                            <div className="w-14 h-14 bg-rose-500/20 rounded-full flex items-center justify-center mb-4">
                                <Phone className="w-6 h-6 text-rose-400" />
                            </div>
                            <h3 className="text-white font-semibold mb-2">Phone</h3>
                            <p className="text-white/60 text-sm">+63 (1) 123456789 </p>
                        </motion.div>

                        <motion.div
                            initial={{ opacity: 0, y: 20 }}
                            whileInView={{ opacity: 1, y: 0 }}
                            viewport={{ once: true }}
                            transition={{ delay: 0.3, duration: 0.6 }}
                            className="flex flex-col items-center text-center p-6"
                        >
                            <div className="w-14 h-14 bg-amber-500/20 rounded-full flex items-center justify-center mb-4">
                                <MapPin className="w-6 h-6 text-amber-400" />
                            </div>
                            <h3 className="text-white font-semibold mb-2">Address</h3>
                            <p className="text-white/60 text-sm">DepEd Sagay National High School Sagay City</p>
                        </motion.div>
                    </div>
                </div>
            </section>

            {/* Footer */}
            <footer className="relative py-8 px-6 bg-[#030303] border-t border-white/5">
                <div className="max-w-7xl mx-auto text-center">
                    <p className="text-white/40 text-sm">
                        © 2026 ASPIRE • Automated Supervision Platform for Instructional Reform & Excellence
                    </p>
                </div>
            </footer>
        </div>
    );
}
