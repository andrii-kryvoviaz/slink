import { env } from '$env/dynamic/private';

import type { RequestHandler } from './$types';

function base64UrlEncode(data: string): string {
  return btoa(data).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

async function getSubscriberJwt(secret: string): Promise<string> {
  const header = JSON.stringify({ typ: 'JWT', alg: 'HS256' });
  const payload = JSON.stringify({
    mercure: { subscribe: ['*'] },
    iat: Math.floor(Date.now() / 1000),
    exp: Math.floor(Date.now() / 1000) + 3600,
  });

  const base64Header = base64UrlEncode(header);
  const base64Payload = base64UrlEncode(payload);
  const data = `${base64Header}.${base64Payload}`;

  const encoder = new TextEncoder();
  const keyData = encoder.encode(secret);
  const messageData = encoder.encode(data);

  const key = await crypto.subtle.importKey(
    'raw',
    keyData,
    { name: 'HMAC', hash: 'SHA-256' },
    false,
    ['sign'],
  );
  const signature = await crypto.subtle.sign('HMAC', key, messageData);
  const signatureBase64 = base64UrlEncode(
    String.fromCharCode(...new Uint8Array(signature)),
  );

  return `${data}.${signatureBase64}`;
}

export const GET: RequestHandler = async ({ url }) => {
  const mercureUrl =
    env.MERCURE_URL || 'http://localhost:3333/.well-known/mercure';
  const mercureSecret = env.MERCURE_JWT_SECRET;

  if (!mercureSecret) {
    return new Response('Mercure JWT secret not configured', { status: 500 });
  }

  const topic = url.searchParams.get('topic');

  if (!topic) {
    return new Response('Missing topic parameter', { status: 400 });
  }

  const jwt = await getSubscriberJwt(mercureSecret);

  const targetUrl = new URL(mercureUrl);
  targetUrl.searchParams.append('topic', topic);

  const response = await fetch(targetUrl.toString(), {
    headers: {
      Accept: 'text/event-stream',
      Authorization: `Bearer ${jwt}`,
    },
  });

  if (!response.ok) {
    return new Response('Failed to connect to Mercure hub', {
      status: response.status,
    });
  }

  return new Response(response.body, {
    headers: {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      Connection: 'keep-alive',
    },
  });
};
