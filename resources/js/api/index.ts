// ASPIRE API Layer - Centralized export of all API modules

export { authApi } from './auth';
export { teacherApi } from './teacher';
export { cotApi } from './cot';
export { dashboardApi } from './dashboard';

// Re-export types from auth and teacher
export type { LoginCredentials, RegisterData } from './auth';
export type { CreateTeacherData } from './teacher';
export type { 
    CreateObservationData, 
    CreateCotRatingData, 
    CreateFeedbackData
} from './cot';

// Re-export dashboard types from dashboard.ts
export type { 
    TeacherDashboardStats,
    SupervisorDashboardStats,
    SchoolHeadDashboardStats 
} from './dashboard';
