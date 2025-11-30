export interface HashtagMatch {
  readonly text: string;
  readonly hashtag: string;
  readonly index: number;
  readonly length: number;
}

export interface TextSegment {
  readonly text: string;
  readonly isHashtag: boolean;
  readonly hashtag?: string;
}

const HASHTAG_REGEX = /#([a-zA-Z0-9_]+)/g;

export function parseHashtags(text: string): readonly HashtagMatch[] {
  if (!text) return [];

  const matches: HashtagMatch[] = [];
  let match: RegExpExecArray | null;

  HASHTAG_REGEX.lastIndex = 0;

  while ((match = HASHTAG_REGEX.exec(text)) !== null) {
    const [fullMatch, hashtag] = match;
    matches.push({
      text: fullMatch,
      hashtag,
      index: match.index,
      length: fullMatch.length,
    });
  }

  return matches;
}

export function hasHashtags(text: string): boolean {
  if (!text) return false;
  return /#[a-zA-Z0-9_]+/.test(text);
}

export function extractHashtags(text: string): readonly string[] {
  return parseHashtags(text).map((match) => match.hashtag);
}

export function splitTextIntoSegments(text: string): readonly TextSegment[] {
  if (!text) return [];

  const hashtags = parseHashtags(text);

  if (hashtags.length === 0) {
    return [{ text, isHashtag: false }];
  }

  const segments: TextSegment[] = [];
  let lastIndex = 0;

  for (const match of hashtags) {
    if (match.index > lastIndex) {
      const beforeText = text.substring(lastIndex, match.index);
      segments.push({ text: beforeText, isHashtag: false });
    }

    segments.push({
      text: match.text,
      isHashtag: true,
      hashtag: match.hashtag,
    });

    lastIndex = match.index + match.length;
  }

  if (lastIndex < text.length) {
    const remainingText = text.substring(lastIndex);
    segments.push({ text: remainingText, isHashtag: false });
  }

  return segments;
}

export function createHashtagSearchQuery(hashtag: string): string {
  if (!hashtag) return '';
  return `#${hashtag}`;
}

export function createHashtagSearchUrl(hashtag: string): string {
  const searchQuery = createHashtagSearchQuery(hashtag);
  return `/explore?search=${encodeURIComponent(searchQuery)}&searchBy=hashtag`;
}
