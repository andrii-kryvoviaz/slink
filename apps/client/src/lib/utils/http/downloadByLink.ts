export function downloadByLink(link: string, filename: string) {
  const a = document.createElement('a');
  a.href = link;
  a.download = filename;
  a.click();
}
