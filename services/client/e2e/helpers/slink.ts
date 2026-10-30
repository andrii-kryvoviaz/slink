import { execFileSync } from 'child_process';

export const COMPOSE_PROJECT = process.env.COMPOSE_PROJECT_NAME ?? 'slink-e2e';

const DOCKER_ARGS = [
  'compose',
  '-p',
  COMPOSE_PROJECT,
  'exec',
  '-T',
  '-u',
  'www-data',
  'slink',
  'slink',
];

export function slink(...args: string[]): string {
  return execFileSync('docker', [...DOCKER_ARGS, ...args], {
    encoding: 'utf8',
    stdio: ['ignore', 'pipe', 'pipe'],
  });
}
