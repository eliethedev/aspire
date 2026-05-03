import { ApiResponse, Observation, Prediction, CotRating } from '../types';

export interface TeacherDashboardStats {
    total_observations: number;
    completed_observations: number;
    average_cot_score: number;
    pending_feedback_count: number;
    recent_predictions: Prediction[];
}

export interface SupervisorDashboardStats {
    total_teachers: number;
    pending_observations: number;
    completed_this_month: number;
    recent_observations: Observation[];
}

export interface SchoolHeadDashboardStats {
    total_teachers: number;
    total_supervisors: number;
    observations_this_term: number;
    school_average_score: number;
}

export const dashboardApi = {
    // Teacher Dashboard
    getTeacherStats: async (): Promise<ApiResponse<TeacherDashboardStats>> => {
        const response = await window.axios.get('/api/dashboard/teacher/stats');
        return response.data;
    },

    getTeacherRecentObservations: async (): Promise<ApiResponse<Observation[]>> => {
        const response = await window.axios.get('/api/dashboard/teacher/recent-observations');
        return response.data;
    },

    getTeacherPredictions: async (): Promise<ApiResponse<Prediction[]>> => {
        const response = await window.axios.get('/api/dashboard/teacher/predictions');
        return response.data;
    },

    // Supervisor Dashboard
    getSupervisorStats: async (): Promise<ApiResponse<SupervisorDashboardStats>> => {
        const response = await window.axios.get('/api/dashboard/supervisor/stats');
        return response.data;
    },

    getSupervisorRecentObservations: async (): Promise<ApiResponse<Observation[]>> => {
        const response = await window.axios.get('/api/dashboard/supervisor/recent-observations');
        return response.data;
    },

    // School Head Dashboard
    getSchoolHeadStats: async (): Promise<ApiResponse<SchoolHeadDashboardStats>> => {
        const response = await window.axios.get('/api/dashboard/school-head/stats');
        return response.data;
    },

    getSchoolPerformance: async (): Promise<ApiResponse<{ department_scores: Record<string, number>; trend: CotRating[] }>> => {
        const response = await window.axios.get('/api/dashboard/school-head/performance');
        return response.data;
    },
};
