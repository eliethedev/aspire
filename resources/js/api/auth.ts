import { User, ApiResponse } from '../types';

export interface LoginCredentials {
    email: string;
    password: string;
}

export interface RegisterData {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    role: string;
}

export const authApi = {
    login: async (credentials: LoginCredentials): Promise<ApiResponse<{ user: User; token: string }>> => {
        const response = await window.axios.post('/api/auth/login', credentials);
        return response.data;
    },

    register: async (data: RegisterData): Promise<ApiResponse<{ user: User }>> => {
        const response = await window.axios.post('/api/auth/register', data);
        return response.data;
    },

    logout: async (): Promise<ApiResponse<null>> => {
        const response = await window.axios.post('/api/auth/logout');
        return response.data;
    },

    me: async (): Promise<ApiResponse<User>> => {
        const response = await window.axios.get('/api/auth/me');
        return response.data;
    },

    refresh: async (): Promise<ApiResponse<{ token: string }>> => {
        const response = await window.axios.post('/api/auth/refresh');
        return response.data;
    },
};
