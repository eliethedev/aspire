// ASPIRE TypeScript Type Definitions

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'teacher' | 'supervisor' | 'school_head' | 'admin';
    created_at: string;
    updated_at: string;
}

export interface Teacher {
    id: number;
    user_id: number;
    employee_id: string;
    subject_specialization: string;
    grade_level: string;
    school_id: number;
    user?: User;
}

export interface Observation {
    id: number;
    teacher_id: number;
    supervisor_id: number;
    observation_date: string;
    subject: string;
    grade_section: string;
    duration_minutes: number;
    notes: string;
    status: 'pending' | 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
    teacher?: Teacher;
}

export interface CotRating {
    id: number;
    observation_id: number;
    rating_category: string;
    score: number;
    max_score: number;
    comments: string;
    created_at: string;
    observation?: Observation;
}

export interface Feedback {
    id: number;
    cot_rating_id: number;
    supervisor_id: number;
    content: string;
    type: 'strength' | 'improvement' | 'general';
    created_at: string;
    updated_at: string;
    cotRating?: CotRating;
}

export interface AiFeedback {
    id: number;
    cot_rating_id: number;
    analysis: string;
    recommendations: string[];
    strengths: string[];
    areas_for_improvement: string[];
    confidence_score: number;
    model_version: string;
    created_at: string;
    updated_at: string;
    cotRating?: CotRating;
}

export interface Prediction {
    id: number;
    teacher_id: number;
    prediction_type: 'performance' | 'promotion' | 'training';
    predicted_outcome: string;
    confidence_level: number;
    factors_considered: string[];
    action_recommended: string;
    status: 'active' | 'fulfilled' | 'expired';
    valid_until: string;
    created_at: string;
    updated_at: string;
    teacher?: Teacher;
}

export interface ApiResponse<T> {
    data: T;
    message: string;
    status: string;
}

export interface PaginatedResponse<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}
