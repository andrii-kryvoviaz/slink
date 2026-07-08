import { copyClipboardItems } from '$lib/utils/ui/clipboard';

export const copyImageContent = async (url: string): Promise<boolean> => {
  try {
    const response = await fetch(url);
    if (!response.ok) {
      throw new Error(`Unexpected response status: ${response.status}`);
    }
    let pngBlob = await response.blob();
    if (pngBlob.type !== 'image/png') {
      const bitmap = await createImageBitmap(pngBlob);
      const canvas = new OffscreenCanvas(bitmap.width, bitmap.height);
      canvas.getContext('2d')!.drawImage(bitmap, 0, 0);
      pngBlob = await canvas.convertToBlob({ type: 'image/png' });
    }
    return copyClipboardItems([new ClipboardItem({ 'image/png': pngBlob })]);
  } catch (error) {
    console.error('Failed to copy image:', error);
    return false;
  }
};
