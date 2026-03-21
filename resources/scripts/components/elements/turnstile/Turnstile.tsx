import React, { forwardRef, useEffect, useImperativeHandle, useMemo, useRef } from 'react';

type TurnstileRenderOptions = {
    sitekey: string;
    theme?: 'auto' | 'light' | 'dark';
    size?: 'normal' | 'flexible' | 'compact';
    appearance?: 'always' | 'execute' | 'interaction-only';
    execution?: 'render' | 'execute';
    callback?: (token: string) => void;
    'error-callback'?: (errorCode?: string) => void;
    'expired-callback'?: () => void;
    'timeout-callback'?: () => void;
};

type TurnstileApi = {
    ready: (callback: () => void) => void;
    render: (container: string | HTMLElement, options: TurnstileRenderOptions) => string;
    execute: (container: string) => void;
    reset: (widgetId: string) => void;
    remove: (widgetId: string) => void;
};

declare global {
    interface Window {
        turnstile?: TurnstileApi;
    }
}

export type TurnstileHandle = {
    execute: () => void;
    reset: () => void;
};

type Props = {
    siteKey: string;
    onVerify: (token: string) => void;
    onError?: (error: Error) => void;
    onExpire?: () => void;
    css?: any;
};

const SCRIPT_SOURCE = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
let scriptLoader: Promise<TurnstileApi> | null = null;

const loadTurnstile = (): Promise<TurnstileApi> => {
    if (typeof window === 'undefined') {
        return Promise.reject(new Error('Cloudflare Turnstile can only be used in the browser.'));
    }

    if (window.turnstile) {
        return Promise.resolve(window.turnstile);
    }

    if (!scriptLoader) {
        scriptLoader = new Promise<TurnstileApi>((resolve, reject) => {
            const resolveWhenReady = () => {
                if (!window.turnstile) {
                    reject(new Error('Cloudflare Turnstile failed to initialize.'));
                    return;
                }

                window.turnstile.ready(() => resolve(window.turnstile!));
            };

            const handleError = () => reject(new Error('Failed to load the Cloudflare Turnstile script.'));
            const existing = document.querySelector<HTMLScriptElement>(`script[src="${SCRIPT_SOURCE}"]`);

            if (existing) {
                existing.addEventListener('load', resolveWhenReady, { once: true });
                existing.addEventListener('error', handleError, { once: true });

                if (window.turnstile) {
                    resolveWhenReady();
                }

                return;
            }

            const script = document.createElement('script');
            script.src = SCRIPT_SOURCE;
            script.async = true;
            script.defer = true;
            script.addEventListener('load', resolveWhenReady, { once: true });
            script.addEventListener('error', handleError, { once: true });
            document.head.appendChild(script);
        }).catch((error) => {
            scriptLoader = null;
            throw error;
        });
    }

    return scriptLoader ?? Promise.reject(new Error('Cloudflare Turnstile loader was not initialized.'));
};

export default forwardRef<TurnstileHandle, Props>(({ siteKey, onVerify, onError, onExpire, css }, ref) => {
    const widgetId = useRef<string | null>(null);
    const containerId = useMemo(() => `turnstile-${Math.random().toString(36).slice(2, 10)}`, []);

    useEffect(() => {
        let cancelled = false;

        loadTurnstile()
            .then((turnstile) => {
                if (cancelled || widgetId.current) {
                    return;
                }

                widgetId.current = turnstile.render(`#${containerId}`, {
                    sitekey: siteKey,
                    theme: 'light',
                    size: 'flexible',
                    appearance: 'interaction-only',
                    execution: 'execute',
                    callback: (token) => onVerify(token),
                    'error-callback': (errorCode) =>
                        onError?.(new Error(`Cloudflare Turnstile validation failed${errorCode ? `: ${errorCode}` : '.'}`)),
                    'expired-callback': () => onExpire?.(),
                    'timeout-callback': () => onExpire?.(),
                });
            })
            .catch((error) => onError?.(error instanceof Error ? error : new Error(String(error))));

        return () => {
            cancelled = true;

            if (window.turnstile && widgetId.current) {
                window.turnstile.remove(widgetId.current);
                widgetId.current = null;
            }
        };
    }, [containerId, onError, onExpire, onVerify, siteKey]);

    useImperativeHandle(ref, () => ({
        execute: () => {
            if (!window.turnstile || !widgetId.current) {
                throw new Error('Cloudflare Turnstile is not ready yet.');
            }

            window.turnstile.execute(`#${containerId}`);
        },
        reset: () => {
            if (window.turnstile && widgetId.current) {
                window.turnstile.reset(widgetId.current);
            }
        },
    }), [containerId]);

    return <div id={containerId} css={css} />;
});
