// Type declarations for global window object
import { AxiosInstance } from 'axios';

declare global {
    interface Window {
        axios: AxiosInstance;
    }
}

export {};
