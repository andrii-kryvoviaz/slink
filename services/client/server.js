import process from 'node:process';

const protocolHeader = 'x-slink-origin-protocol';
const hostHeader = 'x-slink-origin-host';

const serveFromOrigin = async (origin) => {
  process.env.PROTOCOL_HEADER = protocolHeader;
  process.env.HOST_HEADER = hostHeader;

  const { server } = await import('./index.js');

  server.prependListener('request', (request) => {
    request.headers[protocolHeader] = origin.protocol.slice(0, -1);
    request.headers[hostHeader] = origin.host;
  });
};

if (process.env.ORIGIN) {
  await serveFromOrigin(new URL(process.env.ORIGIN));
} else {
  await import('./index.js');
}
