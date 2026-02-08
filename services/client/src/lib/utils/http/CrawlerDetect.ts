import crawlerUserAgents from 'crawler-user-agents';

class CrawlerDetect {
  private readonly regex: RegExp;

  constructor() {
    const pattern = crawlerUserAgents.map((entry) => entry.pattern).join('|');
    this.regex = new RegExp(pattern, 'i');
  }

  isCrawler(userAgent: string | null | undefined): boolean {
    if (!userAgent) return false;
    return this.regex.test(userAgent);
  }

  getMatch(userAgent: string | null | undefined): string | null {
    if (!userAgent) return null;
    const match = this.regex.exec(userAgent);
    return match ? match[0] : null;
  }
}

export const crawlerDetect = new CrawlerDetect();
