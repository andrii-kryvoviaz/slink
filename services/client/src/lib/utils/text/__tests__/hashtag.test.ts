import { describe, expect, it } from 'vitest';

import {
  createHashtagSearchQuery,
  extractHashtags,
  hasHashtags,
  parseHashtags,
  splitTextIntoSegments,
} from '../hashtag';

describe('hashtag utility functions', () => {
  describe('parseHashtags', () => {
    it('should parse hashtags from text', () => {
      const text = 'Hello #world and #testing';
      const result = parseHashtags(text);

      expect(result).toHaveLength(2);
      expect(result[0]).toEqual({
        text: '#world',
        hashtag: 'world',
        index: 6,
        length: 6,
      });
      expect(result[1]).toEqual({
        text: '#testing',
        hashtag: 'testing',
        index: 17,
        length: 8,
      });
    });

    it('should return empty array for text without hashtags', () => {
      const text = 'Hello world';
      const result = parseHashtags(text);

      expect(result).toHaveLength(0);
    });

    it('should handle empty string', () => {
      const result = parseHashtags('');
      expect(result).toHaveLength(0);
    });
  });

  describe('hasHashtags', () => {
    it('should return true for text with hashtags', () => {
      expect(hasHashtags('Hello #world')).toBe(true);
    });

    it('should return false for text without hashtags', () => {
      expect(hasHashtags('Hello world')).toBe(false);
    });

    it('should handle empty string', () => {
      expect(hasHashtags('')).toBe(false);
    });
  });

  describe('extractHashtags', () => {
    it('should extract hashtag strings', () => {
      const text = 'Hello #world and #testing';
      const result = extractHashtags(text);

      expect(result).toEqual(['world', 'testing']);
    });
  });

  describe('splitTextIntoSegments', () => {
    it('should split text into segments', () => {
      const text = 'Hello #world and #testing';
      const result = splitTextIntoSegments(text);

      expect(result).toHaveLength(4);
      expect(result[0]).toEqual({ text: 'Hello ', isHashtag: false });
      expect(result[1]).toEqual({
        text: '#world',
        isHashtag: true,
        hashtag: 'world',
      });
      expect(result[2]).toEqual({ text: ' and ', isHashtag: false });
      expect(result[3]).toEqual({
        text: '#testing',
        isHashtag: true,
        hashtag: 'testing',
      });
    });

    it('should handle text without hashtags', () => {
      const text = 'Hello world';
      const result = splitTextIntoSegments(text);

      expect(result).toHaveLength(1);
      expect(result[0]).toEqual({ text: 'Hello world', isHashtag: false });
    });

    it('should handle empty string', () => {
      const result = splitTextIntoSegments('');
      expect(result).toHaveLength(0);
    });
  });

  describe('createHashtagSearchQuery', () => {
    it('should create search query', () => {
      expect(createHashtagSearchQuery('world')).toBe('#world');
    });

    it('should handle empty string', () => {
      expect(createHashtagSearchQuery('')).toBe('');
    });
  });
});
