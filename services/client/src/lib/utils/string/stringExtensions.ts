String.prototype.capitalizeFirstLetter = function () {
  return this.charAt(0).toUpperCase() + this.slice(1);
};

String.prototype.toFormattedHtml = function () {
  return this.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(
    /\*(.+?)\*/g,
    '<em>$1</em>',
  );
};
