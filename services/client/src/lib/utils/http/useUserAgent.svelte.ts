import { crawlerDetect } from './CrawlerDetect';

type OS = 'macOS' | 'Windows' | 'Linux' | 'Android' | 'iOS' | 'Unknown';
type Browser = 'Chrome' | 'Firefox' | 'Safari' | 'Edge' | 'Opera' | 'Unknown';

function matchFirst<T>(ua: string, patterns: [RegExp, T][], fallback: T): T {
  for (const [regex, value] of patterns) {
    if (regex.test(ua)) return value;
  }
  return fallback;
}

const OS_PATTERNS: [RegExp, OS][] = [
  [/Windows/i, 'Windows'],
  [/Macintosh/i, 'macOS'],
  [/Linux/i, 'Linux'],
  [/Android/i, 'Android'],
  [/iPhone|iPad/i, 'iOS'],
];

const BROWSER_PATTERNS: [RegExp, Browser][] = [
  [/Edg/i, 'Edge'],
  [/OPR|Opera/i, 'Opera'],
  [/Chrome/i, 'Chrome'],
  [/Firefox/i, 'Firefox'],
  [/Safari/i, 'Safari'],
];

class UserAgent {
  _ua: string = $state('');

  constructor(ua: string) {
    this._ua = ua;
  }

  readonly os: OS = $derived(
    matchFirst(this._ua, OS_PATTERNS, 'Unknown' as OS),
  );
  readonly browser: Browser = $derived(
    matchFirst(this._ua, BROWSER_PATTERNS, 'Unknown' as Browser),
  );
  readonly isMobile: boolean = $derived(/Mobi|Android/i.test(this._ua));
  readonly isTablet: boolean = $derived(/Tablet|iPad/i.test(this._ua));
  readonly isDesktop: boolean = $derived(!this.isMobile && !this.isTablet);
  readonly isApple: boolean = $derived(
    this.os === 'macOS' || this.os === 'iOS',
  );
  readonly isBot: boolean = $derived(crawlerDetect.isCrawler(this._ua));
}

export function useUserAgent(ua: string | null | undefined): UserAgent {
  return new UserAgent(ua ?? '');
}
