import { Observation, CotRating, AiFeedback, ApiResponse, PaginatedResponse } from '../types';

export interface CreateObservationData {
    teacher_id: number;
    observation_date: string;
    subject: string;
    grade_section: string;
    duration_minutes: number;
    notes: string;
}

export interface CreateCotRatingData {
    observation_id: number;
    rating_category: string;
    score: number;
    max_score: number;
    comments: string;
}

export interface CreateFeedbackData {
    cot_rating_id: number;
    content: string;
    type: 'strength' | 'improvement' | 'general';
}

export const cotApi = {
    // Observations
    getObservations: async (page: number = 1): Promise<PaginatedResponse<Observation>> => {
        const response = await window.axios.get(`/api/observations?page=${page}`);
        return response.data;
    },

    getObservationById: async (id: number): Promise<ApiResponse<Observation>> => {
        const response = await window.axios.get(`/api/observations/${id}`);
        return response.data;
    },

    createObservation: async (data: CreateObservationData): Promise<ApiResponse<Observation>> => {
        const response = await window.axios.post('/api/observations', data);
        return response.data;
    },

    updateObservation: async (id: number, data: Partial<CreateObservationData>): Promise<ApiResponse<Observation>> => {
        const response = await window.axios.put(`/api/observations/${id}`, data);
        return response.data;
    },

    completeObservation: async (id: number): Promise<ApiResponse<Observation>> => {
        const response = await window.axios.patch(`/api/observations/${id}/complete`);
        return response.data;
    },

    // COT Ratings
    getRatingsByObservation: async (observationId: number): Promise<ApiResponse<CotRating[]>> => {
        const response = await window.axios.get(`/api/observations/${observationId}/ratings`);
        return response.data;
    },

    createRating: async (data: CreateCotRatingData): Promise<ApiResponse<CotRating>> => {
        const response = await window.axios.post('/api/cot-ratings', data);
        return response.data;
    },

    updateRating: async (id: number, data: Partial<CreateCotRatingData>): Promise<ApiResponse<CotRating>> => {
        const response = await window.axios.put(`/api/cot-ratings/${id}`, data);
        return response.data;
    },

    // Feedback
    getFeedbackByRating: async (ratingId: number): Promise<ApiResponse<AiFeedback>> => {
        const response = await window.axios.get(`/api/cot-ratings/${ratingId}/feedback`);
        return response.data;
    },

    addFeedback: async (data: CreateFeedbackData): Promise<ApiResponse<AiFeedback>> => {
        const response = await window.axios.post('/api/feedback', data);
        return response.data;
    },

    // AI Feedback
    generateAiFeedback: async (ratingId: number): Promise<ApiResponse<AiFeedback>> => {
        const response = await window.axios.post(`/api/cot-ratings/${ratingId}/generate-ai-feedback`);
        return response.data;
    },

    getAiFeedback: async (ratingId: number): Promise<ApiResponse<AiFeedback>> => {
        const response = await window.axios.get(`/api/cot-ratings/${ratingId}/ai-feedback`);
        return response.data;
    },
};
