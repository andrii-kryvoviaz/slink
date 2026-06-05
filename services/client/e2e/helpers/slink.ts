import { execFileSync } from 'child_process';

const DOCKER_ARGS = [
  'compose',
  '-p',
  'slink-e2e',
  'exec',
  '-T',
  'slink',
  'slink',
];

export function slink(...args: string[]): string {
  return execFileSync('docker', [...DOCKER_ARGS, ...args], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });
}
