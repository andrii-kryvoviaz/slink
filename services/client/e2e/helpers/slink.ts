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

const ALREADY_EXISTS_SIGNALS = ['already exist', 'already registered'];

function capturedOutput(error: unknown): string {
  const { stdout, stderr } = error as { stdout?: string; stderr?: string };
  return [stdout, stderr].filter(Boolean).join('\n');
}

function isAlreadyExistsError(error: unknown): boolean {
  const output = capturedOutput(error).toLowerCase();
  return ALREADY_EXISTS_SIGNALS.some((signal) => output.includes(signal));
}

export function ensureUser(user: {
  email: string;
  username: string;
  password: string;
  active?: boolean;
}): void {
  const args = [
    'user:create',
    `--email=${user.email}`,
    `--username=${user.username}`,
    '-p',
    user.password,
  ];

  if (user.active) {
    args.push('-a');
  }

  try {
    slink(...args);
  } catch (error) {
    if (!isAlreadyExistsError(error)) {
      throw new Error(`user:create failed:\n${capturedOutput(error)}`);
    }
  }
}

export function grantRole(email: string, role: string): void {
  slink('user:grant:role', `--email=${email}`, role);
}
