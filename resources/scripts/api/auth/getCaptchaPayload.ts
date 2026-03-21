import { CaptchaProvider } from '@/types/captcha';

export default (provider: CaptchaProvider, token?: string | null): Record<string, string> => {
    if (!token) {
        return {};
    }

    if (provider === 'turnstile') {
        return { 'cf-turnstile-response': token };
    }

    if (provider === 'recaptcha') {
        return { 'g-recaptcha-response': token };
    }

    return {};
};
