export const styleTag = (id: string, css: string): string => {
  if (css.trim() === '') {
    return '';
  }

  return `<style id="${id}">${css.replace(/<\/(style)/gi, '<\\/$1')}</style>`;
};
