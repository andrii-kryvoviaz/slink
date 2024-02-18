const RegexPatterns = {
  VARIABLE_DECLARATION: /.*(?=\s=\s\[)/,
  ICON_LINE: /'([^']+)'/g,
  ICON_TAG: /(Icon[.\n\s]*)?icon[=:\s]+(["'])([^"']+:[^"']+)\2/g
};

type RegexPattern = keyof typeof RegexPatterns;

type RegexMatchOptions = {
  group?: number,
  defaultValue?: any
}

export class RegexHelper {
  static extractRegexGroupsToArray(regexPattern: RegexPattern, content: string, { group = 1, defaultValue = [] }: RegexMatchOptions = {}): string[] {
    const regex = this.getRegexPattern(regexPattern);
    return content.match(regex)?.map((match) => match.replace(regex, `$${group}`)) || defaultValue;
  }

  static matchString(regexPattern: RegexPattern, content: string, { group = 1, defaultValue = [] }: RegexMatchOptions = {}): string {
    const regex = this.getRegexPattern(regexPattern);
    return content.match(regex)?.[group] || defaultValue;
  }

  private static getRegexPattern(regexPattern: RegexPattern): RegExp {
    return RegexPatterns[regexPattern];
  }
}