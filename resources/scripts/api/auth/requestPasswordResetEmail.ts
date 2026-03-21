import http from '@/api/http';
import getCaptchaPayload from '@/api/auth/getCaptchaPayload';
import { CaptchaProvider } from '@/types/captcha';

export default (email: string, captchaProvider: CaptchaProvider, captchaData?: string | null): Promise<string> => {
    return new Promise((resolve, reject) => {
        http.post('/auth/password', { email, ...getCaptchaPayload(captchaProvider, captchaData) })
            .then((response) => resolve(response.data.status || ''))
            .catch(reject);
    });
};
