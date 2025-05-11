type OS = 'macOS' | 'Windows' | 'Linux' | 'Android' | 'iOS' | 'Unknown';
type Browser = 'Chrome' | 'Firefox' | 'Safari' | 'Edge' | 'Opera' | 'Unknown';
type Platform = 'desktop' | 'mobile' | 'tablet' | 'Unknown';
type Vendor = 'Google' | 'Mozilla' | 'Apple' | 'Microsoft' | 'Unknown';
type Architecture = 'x86' | 'x64' | 'arm' | 'arm64' | 'Unknown';

type UserAgentInfo = {
  os: OS;
  browser: Browser;
  platform: Platform;
  vendor: Vendor;
  architecture: Architecture;
  isMobile: boolean;
  isTablet: boolean;
  isDesktop: boolean;
  isBot: boolean;
  isMacOS: boolean;
  isIOS: boolean;
};

export const parseUserAgent = (
  userAgent: string | null | undefined,
): UserAgentInfo => {
  if (!userAgent) {
    return {
      os: 'Unknown',
      browser: 'Unknown',
      platform: 'Unknown',
      vendor: 'Unknown',
      architecture: 'Unknown',
      isMobile: false,
      isTablet: false,
      isDesktop: false,
      isBot: false,
      isMacOS: false,
      isIOS: false,
    };
  }

  const isMobile = /Mobi|Android/i.test(userAgent);
  const isTablet = /Tablet|iPad/i.test(userAgent);
  const isDesktop = !isMobile && !isTablet;
  const isBot = /bot|crawl|spider/i.test(userAgent);

  const os = /Windows/i.test(userAgent)
    ? 'Windows'
    : /Macintosh/i.test(userAgent)
      ? 'macOS'
      : /Linux/i.test(userAgent)
        ? 'Linux'
        : /Android/i.test(userAgent)
          ? 'Android'
          : /iPhone|iPad/i.test(userAgent)
            ? 'iOS'
            : 'Unknown';

  const browser = /Chrome/i.test(userAgent)
    ? 'Chrome'
    : /Firefox/i.test(userAgent)
      ? 'Firefox'
      : /Safari/i.test(userAgent)
        ? 'Safari'
        : /Edge/i.test(userAgent)
          ? 'Edge'
          : /Opera/i.test(userAgent)
            ? 'Opera'
            : 'Unknown';

  const platform = isMobile
    ? 'mobile'
    : isTablet
      ? 'tablet'
      : isDesktop
        ? 'desktop'
        : 'Unknown';

  const vendor = /Apple/i.test(userAgent)
    ? 'Apple'
    : /Google/i.test(userAgent)
      ? 'Google'
      : /Mozilla/i.test(userAgent)
        ? 'Mozilla'
        : /Microsoft/i.test(userAgent)
          ? 'Microsoft'
          : 'Unknown';

  const architecture = /x86_64|x64/i.test(userAgent)
    ? 'x64'
    : /x86/i.test(userAgent)
      ? 'x86'
      : /arm64/i.test(userAgent)
        ? 'arm64'
        : /arm/i.test(userAgent)
          ? 'arm'
          : 'Unknown';

  const isMacOS = os === 'macOS';
  const isIOS = os === 'iOS';

  return {
    os,
    browser,
    platform,
    vendor,
    architecture,
    isMobile,
    isTablet,
    isDesktop,
    isBot,
    isMacOS,
    isIOS,
  };
};
