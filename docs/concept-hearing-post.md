# ASPIRE System
## Automated Supervision Platform for Instructional Reform and Excellence

---

## 1. Background of the Study / Project Context

**Organization/Setting:**
- Department of Education (DepEd) – Division of Sagay City, Negros Occidental
- District IX – Public Schools District Supervisor (PSDS): Sir Rubin

**Existing Process:**
- Manual classroom observation scheduling, documentation, and feedback generation
- Paper-based COT (Classroom Observation Tool) forms aligned with PPST standards
- Time-consuming report preparation and manual data compilation across multiple schools

**Major Issue Encountered:**
- Supervisors spend excessive time on administrative tasks rather than instructional coaching
- Inconsistent documentation and feedback quality across observations
- Difficulty tracking teacher performance trends and career progression over time
- No centralized system for observation records across District IX schools

**Evidence the Problem Exists:**
- High administrative burden on PSDS handling observations for multiple schools and teachers
- Lack of standardized digital observation records
- Delayed feedback delivery to teachers, impacting professional growth

**Why a System-Based Solution is Needed:**
- Automate repetitive documentation tasks (COT generation, report compilation)
- Ensure consistent, data-driven evaluations aligned with PPST standards
- Enable AI-powered insights and real-time analytics for better instructional decisions

> **"What is happening now, and why is this project necessary?"**
> Supervisors are overwhelmed with manual paperwork, reducing time for meaningful coaching. ASPIRE automates the entire observation workflow—from scheduling to feedback—allowing focus on teacher development.

---

## 2. General and Specific Objectives

### General Objective
To develop an AI-powered classroom observation and evaluation system that streamlines instructional supervision for DepEd District IX, Division of Sagay City.

### Specific Objectives
1. **Automate observation documentation** – Develop a system that automates the complete COT workflow (pre-observation planning, pre-conference, observation, post-conference), generates PPST-aligned COT documents, and organizes observation records digitally.

2. **Implement AI-powered feedback generation** – Integrate multi-provider AI (Gemini, OpenAI, Claude, Ollama) with RAG to generate structured per-indicator feedback, observation insights, and comparison analyses grounded in PPST standards.

3. **Implement analytics dashboards and reporting tools** – Create visual dashboards showing indicator trends, progress comparisons, career progression assessments, and professional development recommendations for data-driven supervision.

4. **Evaluate system quality** – Assess the system using McCall's Software Quality Model (product operation, product revision, product transition).

5. **Determine usability** – Measure user satisfaction and effectiveness using the Computer System Usability Questionnaire (CSUQ).

---

## 3. Scope, Limitations, and Significance

### Scope

| Category | Details |
|----------|---------|
| **Intended Users** | Admin, Public Schools District Supervisor (PSDS), School Heads, Teachers |
| **Major Modules** | Observation Management (5-stage COT workflow), AI-Powered Insights (6 AI services), COT Document Generation (DOCX/PDF), PPST Standards Management, Coaching Agreements, Analytics Dashboard, Calendar Scheduling, Career Progression Assessments, Form Template Builder, Notifications, Audit Logging, Support Messages |
| **Processes Covered** | Pre-Observation Planning → Pre-Conference → Observation (with autosave) → Post-Conference → Report Generation → Coaching Agreement |
| **Platform** | Cloud-native Web Application (SPA) |
| **Major Technologies** | Laravel 12 (PHP 8.2+), React 19 with Inertia.js, TypeScript, Tailwind CSS, Python AI Bridge, Multi-provider AI (Gemini, OpenAI, Claude, Ollama), MySQL, DomPDF, PHPWord |

### Limitations
- System is limited to District IX, Division of Sagay City; not for nationwide deployment in this phase.

### Significance

| Beneficiary | Expected Improvement |
|-------------|---------------------|
| **Supervisors (PSDS)** | Reduced administrative burden; more time for instructional coaching and meaningful teacher support |

---

## 4. Research/Project Gap Analysis

### Existing Solutions Comparison

| Existing Solution | Existing Feature | Identified Gap |
|-------------------|------------------|----------------|
| **Manual Paper-Based System** | Paper COT forms, manual filing | No digital records; no trend tracking; no data analytics; inconsistent documentation |
| **Spreadsheet-based Tracking** | Excel-based observation logs | No AI assistance; no automated feedback; limited visualization; no PPST alignment |
| **Generic School Management Systems** | Student information, grades | No observation workflow; no COT support; no coaching features; no career progression tracking |

### Proposed System

| Proposed System | Addresses Identified Gaps |
|-----------------|---------------------------|
| **ASPIRE** | Integrated 5-stage observation workflow with multi-provider AI-powered insights (RAG-grounded in PPST), versioned COT indicator management, automated DOCX/PDF document generation, analytics dashboards with indicator trends and progress comparison, coaching agreements with digital signing, and career progression assessments |

---

## 5. Conceptual Framework (IPOO)

```
┌─────────────────────────────────────────────────────────────────────┐
│                         INPUT                                       │
├─────────────────────────────────────────────────────────────────────┤
│ • User credentials (Admin, Supervisor, School Head, Teacher)       │
│ • Observation data (schedule, lesson plans, COT ratings, comments)│
│ • PPST Standards and versioned COT indicators                      │
│ • Form template configurations                                     │
│ • Document uploads (lesson plans, evidence files)                  │
│ • AI provider credentials (API keys, model configs)                │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        PROCESS                                      │
├─────────────────────────────────────────────────────────────────────┤
│ • 5-stage observation workflow (planning → pre-conference →        │
│   observation → post-conference → completion)                      │
│ • AI-powered analysis via RAG (6 services: PreObservation,         │
│   ObservationGuidance, AIFeedback, PostConference, FinalReport,   │
│   DocumentExtractor) with multi-provider fallback                 │
│ • COT document generation (DOCX via PHPWord, PDF via DomPDF)      │
│ • Indicator trend computation and progress comparison              │
│ • Career progression assessment and prediction generation          │
│ • Notification delivery and audit logging                          │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        OUTPUT                                       │
├─────────────────────────────────────────────────────────────────────┤
│ • Digital COT documents with PPST-aligned ratings (DOCX/PDF)      │
│ • AI-generated per-indicator feedback with confidence scoring      │
│ • Observation insights, suggestions, and comparison analyses       │
│ • Analytics dashboards (indicator trends, progress, PD recs)       │
│ • Coaching agreements with digital signatures                      │
│ • Career progression assessments and performance predictions       │
│ • PDF exportable observation reports                               │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        OUTCOME                                      │
├─────────────────────────────────────────────────────────────────────┤
│ • Streamlined supervision process across District IX schools       │
│ • Data-driven instructional decisions grounded in PPST standards   │
│ • Improved teacher performance monitoring and career support       │
│ • Enhanced supervisor productivity and coaching effectiveness      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 6. Methodology

### Requirements/Data Gathering
- **Interview** – PSDS Sir Rubin, School Heads, and Teachers for requirements gathering
- **Document Review** – DepEd COT forms, PPST standards, observation guidelines
- **Survey** – User needs assessment and current pain points evaluation

### Development Methodology
- **Rapid Application Development (RAD)** – Chosen for rapid prototyping, intensive user involvement, and fast delivery suitable for a cloud-native, AI-powered platform requiring quick iterations

### System Development Process

| Component | Technology |
|-----------|------------|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Frontend** | React 19 with Inertia.js, TypeScript, Tailwind CSS, Framer Motion |
| **AI Integration** | Python AI Bridge + Multi-provider (Gemini, OpenAI Claude, Ollama) with RAG |
| **Database** | MySQL (InnoDB) with 76 migrations |
| **PDF Generation** | DomPDF (reports), PHPWord (COT documents) |
| **Authentication** | Laravel Breeze with role-based access (Spatie Permissions) |
| **Document Processing** | PHPWord, PHPSpreadsheet, PHPPresentation, PDFParser |
| **Audit Logging** | Spatie Activity Log |
| **Email** | PHPMailer (custom integration) |
| **Build Tools** | Vite 8, Node.js |
| **Version Control** | Git |
| **Deployment** | Cloud-hosted (XAMPP for development) |

---

## 7. Evaluation Methodology

| Method | Tool/Standard | Purpose |
|--------|---------------|---------|
| **Software Quality Evaluation** | McCall's Software Quality Model | Assess product operation, product revision, and product transition criteria |
| **Usability Assessment** | Computer System Usability Questionnaire (CSUQ) | Measure user satisfaction and system effectiveness across all user roles |

---

## 8. Proposed System Architecture

```
┌──────────────────────────────────────────────────────────────────────┐
│                      PRESENTATION LAYER                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  Admin   │  │Supervisor│  │School    │  │ Teacher  │           │
│  │Dashboard │  │Dashboard │  │Head Dash │  │Dashboard │           │
│  │(React +  │  │(React +  │  │(React +  │  │(React +  │           │
│  │Inertia)  │  │Inertia)  │  │Inertia)  │  │Inertia)  │           │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘           │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                       APPLICATION LAYER                              │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐       │
│  │Observation │ │   COT      │ │  Reports   │ │  Coaching  │       │
│  │ Workflow   │ │  Ratings   │ │  & Export  │ │ Agreements │       │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘       │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐       │
│  │COT Document│ │   PPST     │ │ Calendar   │ │Notifications│      │
│  │ Generation │ │ Standards  │ │ Scheduling │ │  & Audit   │       │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘       │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐                      │
│  │ Form       │ │  Career    │ │  Predictions│                     │
│  │ Templates  │ │Progression │ │  & Analytics│                     │
│  └────────────┘ └────────────┘ └────────────┘                      │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                          AI LAYER                                    │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  Python AI Bridge ←→ Multi-Provider AI                      │   │
│  │  ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐          │   │
│  │  │ Gemini  │ │ OpenAI  │ │ Claude  │ │ Ollama  │          │   │
│  │  └─────────┘ └─────────┘ └─────────┘ └─────────┘          │   │
│  │  RAG System: PPST Rubric + COT Indicator Repositories      │   │
│  │  Services: PreObs │ ObsGuidance │ Feedback │ PostConf │    │   │
│  │            FinalReport │ DocumentExtractor                  │   │
│  └──────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                         DATA LAYER                                   │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  MySQL   │  │  Users   │  │Observations│ │ AI Logs  │           │
│  │Database  │  │  & Roles │  │& COT Docs │  │& Insights│           │
│  │(76 tables│  │          │  │           │  │          │           │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘           │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 9. Expected Output

### Dashboard Previews

**Admin Dashboard:**
- User management, school management, COT indicator versions, PPST standards, form templates, AI settings, audit logs, announcements, support messages

**Supervisor Dashboard:**
- Observation calendar, teacher/school head list, recent observations, quick actions (create observation, generate reports), AI insights overview

**School Head Dashboard:**
- My observations (as observee), teacher observations (as observer), lesson plans, reports, coaching agreements

**Teacher Dashboard:**
- My observations, uploaded lesson plans, feedback received, coaching agreements, confirm/reject observation schedules

### Sample Expected Outputs
- Digital COT documents (DOCX/PDF) with PPST-aligned ratings and career-stage-specific instruments
- AI-generated per-indicator feedback with confidence scoring (high ≥ 0.85, medium ≥ 0.60, low < 0.60)
- Observation insights, suggestions, and comparison analyses grounded in PPST via RAG
- Analytics dashboards with indicator trends, progress comparison, and PD recommendations
- Coaching agreements with digital signatures
- Career progression assessments and performance predictions
- PDF exportable observation reports with executive summaries

---

## 10. Closing Statement

The **ASPIRE System** addresses the critical need for modernizing instructional supervision in DepEd District IX, Division of Sagay City. By leveraging multi-provider AI technology with RAG-grounded PPST standards and automating the complete 5-stage COT observation workflow, ASPIRE empowers supervisors to shift from administrative tasks to meaningful instructional coaching—ultimately improving teacher performance and student outcomes.

---

**Project Proponents:** [Team/Group Name]
**Prepared for:** Concept Hearing
**Date:** [Date]

*ASPIRE — Empowering Supervisors, Enhancing Instruction, Excelling in Education.*
