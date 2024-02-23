import UAParser from 'ua-parser-js';

export const parseUserAgent = (userAgent: string) => {
  const ua = new UAParser(userAgent);
  return ua.getResult();
};

export const parseUserAgentFromRequest = (request: Request) => {
  const userAgents = request.headers.get('user-agent');
  return parseUserAgent(userAgents || '');
};
