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

export function ensureUser(user: {
  email: string;
  username: string;
  password: string;
  active?: boolean;
}): void {
  const args = [
    'user:create',
    '--if-not-exists',
    `--email=${user.email}`,
    `--username=${user.username}`,
    '-p',
    user.password,
  ];

  if (user.active) {
    args.push('-a');
  }

  slink(...args);
}

export function grantRole(email: string, role: string): void {
  slink('user:grant:role', `--email=${email}`, role);
}
