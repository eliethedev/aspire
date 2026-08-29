# ASPIRE System
## Automated Supervision Platform for Instructional Reform and Excellence

---

## 1. Background of the Study / Project Context

**Organization/Setting:**
- Department of Education (DepEd) – Division of Sagay City, Negros Occidental
- District IX – Public Schools District Supervisor (PSDS): Sir Rubin

**Existing Process:**
- Manual classroom observation scheduling, documentation, and feedback generation
- Paper-based COT (Classroom Observation Tool) forms
- Time-consuming report preparation and data compilation

**Major Issue Encountered:**
- Supervisors spend excessive time on administrative tasks rather than instructional coaching
- Inconsistent documentation and feedback across observations
- Difficulty tracking teacher performance trends over time

**Evidence the Problem Exists:**
- High administrative burden on PSDS handling multiple schools
- Lack of centralized observation records
- Delayed feedback to teachers

**Why a System-Based Solution is Needed:**
- Automate repetitive documentation tasks
- Ensure consistent, data-driven evaluations aligned with PPST standards
- Enable real-time analytics for better decision-making

> **"What is happening now, and why is this project necessary?"**
> Supervisors are overwhelmed with manual paperwork, reducing time for meaningful coaching. ASPIRE automates the observation workflow, allowing focus on teacher development.

---

## 2. General and Specific Objectives

### General Objective
To develop an AI-powered classroom observation and evaluation system that streamlines instructional supervision for DepEd District IX.

### Specific Objectives
1. **Automate observation documentation** – Develop a system that automates COT documentation, organizes observation records, and generates structured feedback aligned with PPST standards.

2. **Implement analytics dashboards** – Create visual reporting tools showing observation results, indicator trends, and progress comparisons for data-driven supervision.

3. **Evaluate system quality** – Assess the system using McCall's Software Quality Model (product operation, product revision, product transition).

4. **Determine usability** – Measure user satisfaction and effectiveness using the Computer System Usability Questionnaire (CSUQ).

---

## 3. Scope, Limitations, and Significance

### Scope

| Category | Details |
|----------|---------|
| **Intended Users** | Admin, Public Schools District Supervisor (PSDS), School Heads, Teachers |
| **Major Modules** | Observation Management, AI Insights, COT Document Generation, PPST Standards, Coaching Agreements, Analytics Dashboard, Calendar Scheduling, Career Progression, Notifications, Audit Logging |
| **Processes Covered** | Pre-Observation Planning → Pre-Conference → Observation → Post-Conference → Feedback → Coaching |
| **Platform** | Web-based (Cloud-native) |
| **Major Technologies** | Laravel (PHP), Python (AI Bridge), Gemini AI, PDF Generation, MySQL Database |

### Limitations
- System is limited to District IX, Division of Sagay City; not for nationwide deployment in this phase.

### Significance

| Beneficiary | Expected Improvement |
|-------------|---------------------|
| **Supervisors (PSDS)** | Reduced administrative burden; more time for instructional coaching |

---

## 4. Research/Project Gap Analysis

### Existing Solutions Comparison

| Existing Solution | Existing Feature | Identified Gap |
|-------------------|------------------|----------------|
| **Manual Paper-Based System** | Paper COT forms, manual filing | No digital records; difficult to track trends; no data analytics |
| **Spreadsheet-based Tracking** | Excel-based observation logs | No AI assistance; no automated feedback; limited visualization |
| **Generic School Management Systems** | Student information, grades | No observation workflow; no PPST alignment; no coaching features |

### Proposed System

| Proposed System | Addresses Identified Gaps |
|-----------------|---------------------------|
| **ASPIRE** | Integrated observation workflow with AI-powered insights, PPST-aligned COT generation, analytics dashboards, and coaching agreements – addressing all identified gaps |

---

## 5. Conceptual Framework (IPOO)

```
┌─────────────────────────────────────────────────────────────────────┐
│                         INPUT                                       │
├─────────────────────────────────────────────────────────────────────┤
│ • User credentials (Admin, Supervisor, School Head, Teacher)       │
│ • Observation data (schedule, lesson plans, ratings, comments)     │
│ • PPST Standards and COT indicators                                │
│ • Form templates and evaluation criteria                           │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        PROCESS                                      │
├─────────────────────────────────────────────────────────────────────┤
│ • Observation workflow management (4-stage process)                 │
│ • AI-powered analysis (insights, suggestions, comparisons)          │
│ • COT document generation and PDF export                           │
│ • Analytics computation (trends, progress, PD recommendations)     │
│ • Notification and audit logging                                    │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        OUTPUT                                       │
├─────────────────────────────────────────────────────────────────────┤
│ • Digital observation records and COT documents                    │
│ • AI-generated feedback and suggestions                            │
│ • Analytics dashboards and reports (PDF export)                    │
│ • Coaching agreements and career progression assessments           │
│ • Notification alerts and audit trails                             │
└─────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────────┐
│                        OUTCOME                                      │
├─────────────────────────────────────────────────────────────────────┤
│ • Streamlined supervision process                                  │
│ • Data-driven instructional decisions                              │
│ • Improved teacher performance monitoring                          │
│ • Enhanced supervisor productivity                                 │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 6. Methodology

### Requirements/Data Gathering
- **Interview** – PSDS Sir Rubin, School Heads, and Teachers for requirements
- **Document Review** – DepEd observation forms, PPST standards, COT guidelines
- **Survey** – User needs and current pain points assessment

### Development Methodology
- **Rapid Application Development (RAD)** – Chosen for rapid prototyping, intensive user involvement, and fast delivery suitable for cloud-native AI platforms

### System Development Process

| Component | Technology |
|-----------|------------|
| **Backend Framework** | Laravel 11 (PHP) |
| **Frontend** | Blade Templates, Tailwind CSS, Alpine.js |
| **AI Integration** | Python Bridge + Gemini AI API |
| **Database** | MySQL |
| **PDF Generation** | DOMPDF / TCPDF |
| **Authentication** | Laravel Breeze with role-based access |
| **Version Control** | Git |
| **Deployment** | Cloud-hosted (XAMPP for development) |

---

## 7. Evaluation Methodology

| Method | Tool/Standard | Purpose |
|--------|---------------|---------|
| **Software Quality Evaluation** | McCall's Software Quality Model | Assess product operation, product revision, and product transition |
| **Usability Assessment** | Computer System Usability Questionnaire (CSUQ) | Measure user satisfaction and system effectiveness |
| **User Acceptance** | System Usability Scale (SUS) | Validate user acceptance across roles |

---

## 8. Proposed System Architecture

```
┌──────────────────────────────────────────────────────────────────────┐
│                           PRESENTATION LAYER                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  Admin   │  │Supervisor│  │School    │  │ Teacher  │           │
│  │Dashboard │  │Dashboard │  │HeadDash  │  │Dashboard │           │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘           │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                         APPLICATION LAYER                           │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐       │
│  │Observation │ │   AI       │ │  Reports   │ │  Coaching  │       │
│  │ Management │ │  Services  │ │  & Export  │ │ Agreements │       │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘       │
│  ┌────────────┐ ┌────────────┐ ┌────────────┐ ┌────────────┐       │
│  │COT Document│ │   PPST     │ │ Calendar   │ │Notifications│      │
│  │ Generation │ │ Standards  │ │ Scheduling │ │  & Audit   │       │
│  └────────────┘ └────────────┘ └────────────┘ └────────────┘       │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                           AI LAYER                                  │
│  ┌──────────────────────────────────────────────────────────────┐   │
│  │  Python AI Bridge ←→ Gemini AI API                          │   │
│  │  • Feedback Generation  • Insights  • Suggestions          │   │
│  │  • Comparison Analysis  • PD Recommendations               │   │
│  └──────────────────────────────────────────────────────────────┘   │
└──────────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌──────────────────────────────────────────────────────────────────────┐
│                           DATA LAYER                                │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐           │
│  │  MySQL   │  │  Users   │  │Observations│ │ AI Logs  │           │
│  │Database  │  │  & Roles │  │& COT Docs │  │& Insights│           │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘           │
└──────────────────────────────────────────────────────────────────────┘
```

---

## 9. Expected Output

### Proposed Dashboard Features
- **Admin Dashboard**: User management, school management, PPST standards, audit logs
- **Supervisor Dashboard**: Observation calendar, teacher list, recent observations, AI insights
- **School Head Dashboard**: Teacher observations, reports, lesson plans
- **Teacher Dashboard**: My observations, feedback, coaching agreements

### Sample Expected Outputs
- Digital COT documents with PPST-aligned ratings
- AI-generated observation insights and suggestions
- Analytics dashboards with indicator trends
- PDF exportable reports and coaching agreements

---

## 10. Closing Statement

The **ASPIRE System** addresses the critical need for modernizing instructional supervision in DepEd District IX. By leveraging AI technology and automating the observation workflow, ASPIRE empowers supervisors to shift from administrative tasks to meaningful instructional coaching, ultimately improving teacher performance and student outcomes.

---

**Project Proponents:** [Team/Group Name]
**Prepared for:** Concept Hearing
**Date:** [Date]

*ASPIRE — Empowering Supervisors, Enhancing Instruction, Excelling in Education.*
