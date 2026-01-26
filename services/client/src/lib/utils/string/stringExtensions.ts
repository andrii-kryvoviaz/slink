String.prototype.capitalizeFirstLetter = function () {
  return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.toFormattedHtml = function () {
  return this.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(
    /\*(.+?)\*/g,
    '<em>$1</em>',
  );
};

String.prototype.decodeHtmlEntities = function () {
  return this.replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&quot;/g, '"')
    .replace(/&apos;/g, "'")
    .replace(/&#039;/g, "'")
    .replace(/&amp;/g, '&');
};
