import http from '@/api/http';

export default (locale: string): Promise<void> => {
    return new Promise((resolve, reject) => {
        http.post('/locales/locale', { locale })
            .then(() => resolve())
            .catch(reject);
    });
};
