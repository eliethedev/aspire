import { Teacher, Observation, ApiResponse, PaginatedResponse } from '../types';

export interface CreateTeacherData {
    employee_id: string;
    subject_specialization: string;
    grade_level: string;
    school_id: number;
    name: string;
    email: string;
}

export const teacherApi = {
    getAll: async (page: number = 1): Promise<PaginatedResponse<Teacher>> => {
        const response = await window.axios.get(`/api/teachers?page=${page}`);
        return response.data;
    },

    getById: async (id: number): Promise<ApiResponse<Teacher>> => {
        const response = await window.axios.get(`/api/teachers/${id}`);
        return response.data;
    },

    create: async (data: CreateTeacherData): Promise<ApiResponse<Teacher>> => {
        const response = await window.axios.post('/api/teachers', data);
        return response.data;
    },

    update: async (id: number, data: Partial<CreateTeacherData>): Promise<ApiResponse<Teacher>> => {
        const response = await window.axios.put(`/api/teachers/${id}`, data);
        return response.data;
    },

    delete: async (id: number): Promise<ApiResponse<null>> => {
        const response = await window.axios.delete(`/api/teachers/${id}`);
        return response.data;
    },

    getObservations: async (id: number): Promise<ApiResponse<Observation[]>> => {
        const response = await window.axios.get(`/api/teachers/${id}/observations`);
        return response.data;
    },

    getProfile: async (): Promise<ApiResponse<Teacher>> => {
        const response = await window.axios.get('/api/teacher/profile');
        return response.data;
    },
};
